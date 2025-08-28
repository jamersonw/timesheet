
$(document).ready(function() {
    
    var currentApprovalId = null;
    
    // Debug detalhado: verificar se os dados estão carregados
    console.log('[Weekly JS Debug] Manage Weekly carregado');
    console.log('[Weekly JS Debug] Dados recebidos:', manage_weekly_data);
    
    if (typeof manage_weekly_data === 'undefined') {
        console.error('[Weekly JS Debug] ERRO: manage_weekly_data não está definido!');
        alert('ERRO: Dados da página não foram carregados corretamente. Verifique os logs do servidor.');
        return;
    }
    
    if (!manage_weekly_data.weekly_approvals) {
        console.error('[Weekly JS Debug] ERRO: weekly_approvals não existe nos dados!');
        console.log('[Weekly JS Debug] Propriedades disponíveis:', Object.keys(manage_weekly_data));
        return;
    }
    
    console.log('[Weekly JS Debug] Aprovações semanais encontradas:', manage_weekly_data.weekly_approvals.length);
    
    // Debug de cada aprovação
    if (manage_weekly_data.weekly_approvals.length > 0) {
        manage_weekly_data.weekly_approvals.forEach(function(approval, index) {
            console.log('[Weekly JS Debug] Aprovação ' + (index + 1) + ':', approval);
            console.log('[Weekly JS Debug] - Staff ID:', approval.staff_id);
            console.log('[Weekly JS Debug] - Nome:', approval.firstname + ' ' + approval.lastname);
            console.log('[Weekly JS Debug] - Status:', approval.status);
            console.log('[Weekly JS Debug] - Total tarefas:', approval.total_tasks);
            
            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
            loadTimesheetPreview(approval.id, approval.staff_id, approval.week_start_date);
        });
    } else {
        console.log('[Weekly JS Debug] Nenhuma aprovação encontrada para esta semana');
    }
    
    // Function to load total hours for a specific approval
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        console.log('[Weekly JS Debug] Carregando total de horas para:', {
            approvalId: approvalId,
            staffId: staffId,
            weekStartDate: weekStartDate
        });
        
        var $totalDisplay = $('.total-hours-display[data-approval-id="' + approvalId + '"]');
        
        if ($totalDisplay.length === 0) {
            console.warn('[Weekly JS Debug] Elemento total-hours-display não encontrado para approval:', approvalId);
            return;
        }
        
        $.get(manage_weekly_data.admin_url + 'timesheet/get_week_total', {
            staff_id: staffId,
            week_start_date: weekStartDate
        })
        .done(function(response) {
            console.log('[Weekly JS Debug] Resposta total de horas:', response);
            if (response.success) {
                $totalDisplay.html('<strong>' + parseFloat(response.total_hours).toFixed(1) + 'h</strong>');
            } else {
                $totalDisplay.html('<span class="text-danger">Erro</span>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('[Weekly JS Debug] Erro ao carregar total de horas:', error);
            $totalDisplay.html('<span class="text-danger">Erro</span>');
        });
    }
    
    // Function to load timesheet preview
    function loadTimesheetPreview(approvalId, staffId, weekStartDate) {
        console.log('[Weekly JS Debug] Carregando preview para:', {
            approvalId: approvalId,
            staffId: staffId,
            weekStartDate: weekStartDate
        });
        
        var $previewContainer = $('#preview-' + approvalId);
        
        if ($previewContainer.length === 0) {
            console.warn('[Weekly JS Debug] Container de preview não encontrado para approval:', approvalId);
            return;
        }
        
        $.get(manage_weekly_data.admin_url + 'timesheet/get_timesheet_preview', {
            staff_id: staffId,
            week_start_date: weekStartDate
        })
        .done(function(response) {
            console.log('[Weekly JS Debug] Resposta preview:', response);
            if (response.success) {
                $previewContainer.html(response.html);
            } else {
                $previewContainer.html('<div class="text-center text-danger">Erro ao carregar preview</div>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('[Weekly JS Debug] Erro ao carregar preview:', error);
            $previewContainer.html('<div class="text-center text-danger">Erro ao carregar dados</div>');
        });
    }

    // Approve button click
    $(document).on('click', 'button.approve-btn, a.approve-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('[Weekly JS Debug] Clicou em aprovar semanal, ID:', approvalId);
        
        TimesheetModals.confirm({
            title: 'Aprovar Timesheet',
            message: 'Tem certeza que deseja aprovar este timesheet? Esta ação não pode ser desfeita.',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            console.log('[Weekly JS Debug] Resultado da confirmação semanal:', confirmed);
            if (confirmed) {irmed) {
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
    
    // Cancel approval button click
    $(document).on('click', '.cancel-approval-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em cancelar aprovação, ID:', approvalId);
        
        if (!approvalId) {
            console.error('Approval ID não encontrado no botão');
            return;
        }
        
        // Usar confirm simples se TimesheetModals não estiver disponível
        if (typeof TimesheetModals !== 'undefined') {
            TimesheetModals.confirm({
                title: 'Cancelar Aprovação',
                message: 'Tem certeza que deseja cancelar esta aprovação? O timesheet voltará ao status de rascunho e os timers do quadro de horas serão removidos.',
                icon: 'fa-exclamation-triangle',
                confirmText: 'Sim, Cancelar',
                cancelText: 'Não',
                confirmClass: 'timesheet-modal-btn-warning'
            }).then(function(confirmed) {
                console.log('Resultado da confirmação de cancelamento:', confirmed);
                if (confirmed) {
                    cancelApproval(approvalId);
                }
            });
        } else {
            // Fallback para confirm nativo
            if (confirm('Tem certeza que deseja cancelar esta aprovação? O timesheet voltará ao status de rascunho e os timers do quadro de horas serão removidos.')) {
                cancelApproval(approvalId);
            }
        }
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
    
    function cancelApproval(approvalId) {
        var $button = $('button[data-approval-id="' + approvalId + '"]');
        var originalText = $button.html();
        
        // Mostrar loading
        $button.html('<i class="fa fa-spinner fa-spin"></i> Cancelando...').prop('disabled', true);
        
        var postData = {
            approval_id: approvalId
        };
        
        console.log('Cancelando aprovação via AJAX:', postData);
        
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/cancel_approval',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('Resposta do cancelamento:', response);
                
                if (response.success) {
                    TimesheetModals.alert({
                        title: 'Sucesso',
                        message: response.message,
                        icon: 'fa-check-circle',
                        type: 'success'
                    }).then(function() {
                        // Recarregar a página para atualizar o status
                        location.reload();
                    });
                } else {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: response.message || 'Erro ao cancelar aprovação',
                        icon: 'fa-exclamation-triangle',
                        type: 'error'
                    });
                    
                    // Restaurar botão
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX no cancelamento:', xhr.responseText);
                
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
