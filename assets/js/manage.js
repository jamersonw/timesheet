$(document).ready(function() {
    
    var currentApprovalId = null;
    
    // Load total hours for each approval
    if (typeof manage_data !== 'undefined' && manage_data.pending_approvals) {
        manage_data.pending_approvals.forEach(function(approval) {
            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
        });
    }
    
    // Approve button click
    $(document).on('click', '.approve-btn', function() {
        var approvalId = $(this).data('approval-id');
        
        if (confirm('Are you sure you want to approve this timesheet? This action cannot be undone.')) {
            approveRejectTimesheet(approvalId, 'approved');
        }
    });
    
    // Reject button click
    $(document).on('click', '.reject-btn', function() {
        currentApprovalId = $(this).data('approval-id');
        $('#rejection-reason').val('');
        $('#rejection-modal').modal('show');
    });
    
    // Confirm rejection
    $('#confirm-rejection').click(function() {
        var reason = $('#rejection-reason').val().trim();
        
        if (!reason) {
            alert('Please provide a reason for rejection.');
            return;
        }
        
        approveRejectTimesheet(currentApprovalId, 'rejected', reason);
        $('#rejection-modal').modal('hide');
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
                    showAlert('success', response.message);
                    setTimeout(function() {
                        $row.fadeOut(function() {
                            $(this).remove();
                            checkEmptyTable();
                        });
                    }, 1500);
                    
                } else {
                    showAlert('danger', response.message);
                    $buttons.prop('disabled', false);
                    $row.find('.approve-btn').html('<i class="fa fa-check"></i> Approve');
                    $row.find('.reject-btn').html('<i class="fa fa-times"></i> Reject');
                }
            },
            // ================== INÍCIO DA CORREÇÃO ==================
            // Corrigido a função de erro para receber os parâmetros corretos
            error: function(jqXHR, textStatus, errorThrown) {
                showAlert('danger', 'Error processing request. Please try again.');
                
                console.group('[APROVAÇÃO] ERRO DE AJAX');
                console.error('Status Code:', jqXHR.status);
                console.error('Status Text:', textStatus);
                console.error('Error Thrown:', errorThrown);
                console.error('Resposta Completa do Servidor:', jqXHR.responseText);
                console.groupEnd();
                
                $buttons.prop('disabled', false);
                $row.find('.approve-btn').html('<i class="fa fa-check"></i> Approve');
                $row.find('.reject-btn').html('<i class="fa fa-times"></i> Reject');
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
    
    function showAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible">';
        alertHtml += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        alertHtml += message;
        alertHtml += '</div>';
        
        $('.panel-body').prepend(alertHtml);
        
        if (type === 'success') {
            setTimeout(function() {
                $('.alert-success').fadeOut();
            }, 3000);
        }
        
        $('html, body').animate({
            scrollTop: $('.panel-body').offset().top - 100
        }, 500);
    }
    
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