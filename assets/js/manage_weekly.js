
$(document).ready(function() {
    
    var currentApprovalId = null;
    
    // Debug: verificar se os dados estão carregados
    console.log('Manage Weekly carregado:', manage_weekly_data);
    console.log('Aprovações semanais encontradas:', manage_weekly_data.weekly_approvals.length);
    
    // Load total hours and preview for each approval
    if (manage_weekly_data.weekly_approvals && manage_weekly_data.weekly_approvals.length > 0) {
        manage_weekly_data.weekly_approvals.forEach(function(approval) {
            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
            loadTimesheetPreview(approval.id, approval.staff_id, approval.week_start_date);
        });
    }
    
    // Approve button click
    $(document).on('click', 'button.approve-btn, a.approve-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em aprovar semanal, ID:', approvalId);
        
        TimesheetModals.confirm({
            title: 'Aprovar Timesheet',
            message: 'Tem certeza que deseja aprovar este timesheet? Esta ação não pode ser desfeita.',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            console.log('Resultado da confirmação semanal:', confirmed);
            if (confirmed) {
                approveRejectTimesheet(approvalId, 'approved');
            }
        });
    });
    
    // Reject button click
    $(document).on('click', 'button.reject-btn, a.reject-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em rejeitar semanal, ID:', approvalId);
        
        TimesheetModals.prompt({
            title: 'Rejeitar Timesheet',
            message: 'Por favor, informe o motivo da rejeição:',
            placeholder: 'Digite o motivo da rejeição...',
            icon: 'fa-times-circle',
            confirmText: 'Rejeitar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-danger',
            required: true
        }).then(function(reason) {
            console.log('Motivo da rejeição semanal:', reason);
            if (reason) {
                approveRejectTimesheet(approvalId, 'rejected', reason);
            }
        });
    });
    
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/get_week_total',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var hours = parseFloat(response.total_hours) || 0;
                    var formatted = hours > 0 ? hours.toFixed(2) + 'h' : '0h';
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html(formatted);
                } else {
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html('Erro');
                }
            },
            error: function() {
                $('.total-hours-display[data-approval-id="' + approvalId + '"]').html('Erro');
            }
        });
    }
    
    function loadTimesheetPreview(approvalId, staffId, weekStartDate) {
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/get_timesheet_preview',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.html) {
                    $('#preview-' + approvalId).html(response.html);
                } else {
                    $('#preview-' + approvalId).html('<div class="text-center text-muted">Nenhuma entrada encontrada</div>');
                }
            },
            error: function() {
                $('#preview-' + approvalId).html('<div class="text-center text-muted text-danger">Erro ao carregar preview</div>');
            }
        });
    }
    
    function approveRejectTimesheet(approvalId, action, reason) {
        var $button = $('button[data-approval-id="' + approvalId + '"]');
        var originalText = $button.html();
        
        // Mostrar loading
        $button.html('<i class="fa fa-spinner fa-spin"></i> Processando...').prop('disabled', true);
        
        var postData = {
            approval_id: approvalId,
            action: action
        };
        
        if (reason) {
            postData.reason = reason;
        }
        
        console.log('Dados enviados via AJAX (semanal):', postData);
        
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/approve_reject',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('Resposta recebida (semanal):', response);
                
                if (response.success) {
                    TimesheetModals.alert({
                        title: 'Sucesso',
                        message: response.message,
                        icon: 'fa-check-circle',
                        type: 'success'
                    }).then(function() {
                        // Remover o painel da aprovação
                        $('.approval-panel[data-approval-id="' + approvalId + '"]').fadeOut(500, function() {
                            $(this).remove();
                            
                            // Verificar se ainda há aprovações pendentes
                            if ($('.approval-panel').length === 0) {
                                location.reload();
                            }
                        });
                    });
                } else {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: response.message || 'Erro ao processar aprovação',
                        icon: 'fa-exclamation-triangle',
                        type: 'error'
                    });
                    
                    // Restaurar botão
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX (semanal):', xhr.responseText);
                
                TimesheetModals.alert({
                    title: 'Erro de Comunicação',
                    message: 'Erro ao comunicar com o servidor. Tente novamente.',
                    icon: 'fa-exclamation-triangle',
                    type: 'error'
                });
                
                // Restaurar botão
                $button.html(originalText).prop('disabled', false);
            }
        });
    }
    
});
