
$(document).ready(function() {
    'use strict';
    
    // Verificar se os dados estão disponíveis
    if (typeof weekly_manage_data === 'undefined') {
        console.error('weekly_manage_data não está definido!');
        return;
    }
    
    var currentApprovalId = null;
    
    // Carregar total de horas para cada aprovação
    if (weekly_manage_data.weekly_approvals) {
        weekly_manage_data.weekly_approvals.forEach(function(approval) {
            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
        });
    }

    // Inicializar tooltips
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Botão de aprovar
    $(document).on('click', 'button.approve-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em aprovar, ID:', approvalId);
        
        if (typeof TimesheetModals !== 'undefined' && TimesheetModals.confirm) {
            TimesheetModals.confirm({
                title: 'Aprovar Timesheet',
                message: 'Tem certeza que deseja aprovar este timesheet? As horas serão adicionadas ao quadro de horas do Perfex.',
                icon: 'fa-check-circle',
                confirmText: 'Aprovar',
                cancelText: 'Cancelar',
                confirmClass: 'timesheet-modal-btn-success'
            }).then(function(confirmed) {
                if (confirmed) {
                    approveRejectTimesheet(approvalId, 'approved');
                }
            });
        } else {
            // Fallback para confirm nativo
            if (confirm('Tem certeza que deseja aprovar este timesheet?')) {
                approveRejectTimesheet(approvalId, 'approved');
            }
        }
    });
    
    // Botão de rejeitar
    $(document).on('click', 'button.reject-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em rejeitar, ID:', approvalId);
        
        if (typeof TimesheetModals !== 'undefined' && TimesheetModals.prompt) {
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
                if (reason) {
                    approveRejectTimesheet(approvalId, 'rejected', reason);
                }
            });
        } else {
            // Fallback para prompt nativo
            var reason = prompt('Por favor, informe o motivo da rejeição:');
            if (reason && reason.trim()) {
                approveRejectTimesheet(approvalId, 'rejected', reason.trim());
            }
        }
    });

    // Botão de cancelar aprovação
    $(document).on('click', 'button.cancel-approval-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var approvalId = $(this).data('approval-id');
        console.log('Clicou em cancelar aprovação, ID:', approvalId);
        
        if (typeof TimesheetModals !== 'undefined' && TimesheetModals.confirm) {
            TimesheetModals.confirm({
                title: 'Cancelar Aprovação',
                message: 'Tem certeza que deseja cancelar esta aprovação? As horas serão removidas do quadro de horas e o status voltará para rascunho.',
                icon: 'fa-exclamation-triangle',
                confirmText: 'Cancelar Aprovação',
                cancelText: 'Manter Aprovação',
                confirmClass: 'timesheet-modal-btn-warning'
            }).then(function(confirmed) {
                if (confirmed) {
                    cancelApproval(approvalId);
                }
            });
        } else {
            // Fallback para confirm nativo
            if (confirm('Tem certeza que deseja cancelar esta aprovação?')) {
                cancelApproval(approvalId);
            }
        }
    });
    
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        $.ajax({
            url: weekly_manage_data.admin_url + 'timesheet/get_week_total',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var hours = parseFloat(response.total_hours) || 0;
                    var formatted = hours.toFixed(1) + 'h';
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html(
                        '<strong>' + formatted + '</strong>'
                    );
                } else {
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html(
                        '<span class="text-muted">0.0h</span>'
                    );
                }
            },
            error: function() {
                $('.total-hours-display[data-approval-id="' + approvalId + '"]').html(
                    '<span class="text-danger">Error</span>'
                );
            }
        });
    }
    
    function approveRejectTimesheet(approvalId, action, reason) {
        var data = {
            approval_id: approvalId,
            action: action
        };
        
        if (reason) {
            data.reason = reason;
        }
        
        console.log('Dados enviados via AJAX:', data);
        
        $.ajax({
            url: weekly_manage_data.admin_url + 'timesheet/approve_reject',
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                var row = $('tr[data-approval-id="' + approvalId + '"]');
                row.find('.btn').prop('disabled', true);
                row.find('.btn i').removeClass().addClass('fa fa-spinner fa-spin');
            },
            success: function(response) {
                console.log('Resposta do servidor:', response);
                if (response.success) {
                    if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                        TimesheetModals.alert({
                            title: 'Sucesso',
                            message: response.message,
                            icon: 'fa-check-circle',
                            type: 'success'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert_float('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                        TimesheetModals.alert({
                            title: 'Erro',
                            message: response.message || 'Erro desconhecido',
                            icon: 'fa-exclamation-circle',
                            type: 'error'
                        });
                    } else {
                        alert_float('danger', response.message || 'Erro desconhecido');
                    }
                    var row = $('tr[data-approval-id="' + approvalId + '"]');
                    row.find('.btn').prop('disabled', false);
                    restoreButtonIcons(row, action);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', error);
                if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: 'Erro de comunicação com o servidor',
                        icon: 'fa-exclamation-circle',
                        type: 'error'
                    });
                } else {
                    alert_float('danger', 'Erro de comunicação com o servidor');
                }
                var row = $('tr[data-approval-id="' + approvalId + '"]');
                row.find('.btn').prop('disabled', false);
                restoreButtonIcons(row, action);
            }
        });
    }

    function cancelApproval(approvalId) {
        $.ajax({
            url: weekly_manage_data.admin_url + 'timesheet/cancel_approval',
            type: 'POST',
            data: {
                approval_id: approvalId
            },
            dataType: 'json',
            beforeSend: function() {
                var row = $('tr[data-approval-id="' + approvalId + '"]');
                row.find('.btn').prop('disabled', true);
                row.find('.cancel-approval-btn i').removeClass().addClass('fa fa-spinner fa-spin');
            },
            success: function(response) {
                console.log('Resposta do servidor:', response);
                if (response.success) {
                    if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                        TimesheetModals.alert({
                            title: 'Sucesso',
                            message: response.message,
                            icon: 'fa-check-circle',
                            type: 'success'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert_float('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                        TimesheetModals.alert({
                            title: 'Erro',
                            message: response.message || 'Erro desconhecido',
                            icon: 'fa-exclamation-circle',
                            type: 'error'
                        });
                    } else {
                        alert_float('danger', response.message || 'Erro desconhecido');
                    }
                    var row = $('tr[data-approval-id="' + approvalId + '"]');
                    row.find('.btn').prop('disabled', false);
                    row.find('.cancel-approval-btn i').removeClass().addClass('fa fa-undo');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', error);
                if (typeof TimesheetModals !== 'undefined' && TimesheetModals.alert) {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: 'Erro de comunicação com o servidor',
                        icon: 'fa-exclamation-circle',
                        type: 'error'
                    });
                } else {
                    alert_float('danger', 'Erro de comunicação com o servidor');
                }
                var row = $('tr[data-approval-id="' + approvalId + '"]');
                row.find('.btn').prop('disabled', false);
                row.find('.cancel-approval-btn i').removeClass().addClass('fa fa-undo');
            }
        });
    }
    
    function restoreButtonIcons(row, action) {
        row.find('.approve-btn i').removeClass().addClass('fa fa-check');
        row.find('.reject-btn i').removeClass().addClass('fa fa-times');
        row.find('.cancel-approval-btn i').removeClass().addClass('fa fa-undo');
    }
});
