$(document).ready(function() {
    
    var currentApprovalId = null;
    
    // Debug: verificar se os botões existem na página
    console.log('Botões de aprovação encontrados:', $('.approve-btn').length);
    console.log('Botões de rejeição encontrados:', $('.reject-btn').length);
    
    // Load total hours for each approval
    if (typeof manage_data !== 'undefined' && manage_data.pending_approvals) {
        manage_data.pending_approvals.forEach(function(approval) {
            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
        });
    }
    
    // Approve button click - usar seletor mais específico
    $(document).on('click', 'button.approve-btn, a.approve-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em aprovar, ID:', approvalId);
        
        TimesheetModals.confirm({
            title: 'Aprovar Timesheet',
            message: 'Tem certeza que deseja aprovar este timesheet? Esta ação não pode ser desfeita.',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            console.log('Resultado da confirmação:', confirmed);
            if (confirmed) {
                approveRejectTimesheet(approvalId, 'approved');
            }
        });
    });
    
    // Reject button click - usar seletor mais específico
    $(document).on('click', 'button.reject-btn, a.reject-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em rejeitar, ID:', approvalId);
        
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
            console.log('Motivo da rejeição:', reason);
            if (reason) {
                approveRejectTimesheet(approvalId, 'rejected', reason);
            }
        });
    });
    
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        // Usar a variável correta dependendo da view
        var data_source = (typeof manage_data !== 'undefined') ? manage_data : approval_data;
        
        $.ajax({
            url: data_source.admin_url + 'timesheet/get_week_total',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]')
                        .html('<strong>' + formatHours(response.total_hours) + '</strong>');
                } else {
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]')
                        .html('<span class="text-muted">0.00</span>');
                }
            },
            error: function() {
                $('.total-hours-display[data-approval-id="' + approvalId + '"]')
                    .html('<span class="text-danger">Error</span>');
            }
        });
    }
    
    function approveRejectTimesheet(approvalId, action, reason) {
        var $row = $('tr[data-approval-id="' + approvalId + '"]');
        var $buttons = $row.find('.approve-btn, .reject-btn');
        
        $buttons.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        var data = {
            approval_id: approvalId,
            action: action
        };
        
        if (reason) {
            data.reason = reason;
        }
        
        // Usar a variável correta dependendo da view (manage.php ou view_approval.php)
        var data_source = (typeof manage_data !== 'undefined') ? manage_data : approval_data;
        
        console.group('[APROVAÇÃO] Envio para o Servidor');
        console.log('Dados enviados via AJAX:', data);
        console.log('URL:', data_source.admin_url + 'timesheet/approve_reject');
        console.groupEnd();
        
        $.ajax({
            url: data_source.admin_url + 'timesheet/approve_reject',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                console.group('[APROVAÇÃO] Resposta do Servidor');
                console.log('Resposta recebida:', response);
                console.groupEnd();
                
                if (response.success) {
                    // Usar notificação automática em vez de modal adicional
                    TimesheetModals.notify('success', response.message);
                    
                    setTimeout(function() {
                        $row.fadeOut(function() {
                            $(this).remove();
                            checkEmptyTable();
                        });
                    }, 1500);
                    
                } else {
                    TimesheetModals.notify('danger', response.message);
                    $buttons.prop('disabled', false);
                    $row.find('.approve-btn').html('<i class="fa fa-check"></i> Aprovar');
                    $row.find('.reject-btn').html('<i class="fa fa-times"></i> Rejeitar');
                }
            },
            // ================== INÍCIO DA CORREÇÃO ==================
            // Corrigido a função de erro para receber os parâmetros corretos
            error: function(jqXHR, textStatus, errorThrown) {
                TimesheetModals.notify('danger', 'Erro ao processar solicitação. Tente novamente.');
                
                console.group('[APROVAÇÃO] ERRO DE AJAX');
                console.error('Status Code:', jqXHR.status);
                console.error('Status Text:', textStatus);
                console.error('Error Thrown:', errorThrown);
                console.error('Resposta Completa do Servidor:', jqXHR.responseText);
                console.groupEnd();
                
                $buttons.prop('disabled', false);
                $row.find('.approve-btn').html('<i class="fa fa-check"></i> Aprovar');
                $row.find('.reject-btn').html('<i class="fa fa-times"></i> Rejeitar');
            }
            // =================== FIM DA CORREÇÃO ====================
        });
    }
    
    function checkEmptyTable() {
        var $tbody = $('table tbody');
        if ($tbody.find('tr').length === 0) {
            var emptyHtml = '<div class="alert alert-info">';
            emptyHtml += '<i class="fa fa-info-circle"></i> ';
            emptyHtml += 'No pending timesheet approvals at this time.';
            emptyHtml += '</div>';
            
            $('.table-responsive').replaceWith(emptyHtml);
        }
    }
    
    function formatHours(hours) {
        return parseFloat(hours || 0).toFixed(2);
    }
    
    // Função showAlert removida - usando TimesheetModals.notify() agora
    
    $('#rejection-reason').on('input', function() {
        var reason = $(this).val().trim();
        $('#confirm-rejection').prop('disabled', !reason);
    });
    
    $('#confirm-rejection').prop('disabled', true);
    
    $('#rejection-modal').on('hidden.bs.modal', function() {
        $('#rejection-reason').val('');
        $('#confirm-rejection').prop('disabled', true);
        currentApprovalId = null;
    });
});