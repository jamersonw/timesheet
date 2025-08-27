
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet_modals.css'); ?>">
<script>
// Garantir que as funções CSRF estejam disponíveis
function csrf_jquery_ajax_setup() {
    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type == 'POST' || settings.type == 'PUT' || settings.type == 'DELETE') {
                    if (typeof csrf_token_name !== 'undefined' && typeof csrf_hash_name !== 'undefined') {
                        xhr.setRequestHeader(csrf_token_name, $('input[name="' + csrf_token_name + '"]').val());
                    }
                }
            }
        });
    }
}

// Executar quando o documento estiver pronto
$(document).ready(function() {
    csrf_jquery_ajax_setup();
});
</script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-calendar"></i> <?php echo _l('timesheet_weekly_approvals'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <!-- Week Navigation -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo admin_url('timesheet/manage_weekly?week=' . timesheet_get_previous_week($week_start)); ?>" 
                                           class="btn btn-default">
                                            <i class="fa fa-chevron-left"></i> <?php echo _l('timesheet_previous_week'); ?>
                                        </a>
                                        <button type="button" class="btn btn-primary" disabled>
                                            <?php echo _l('timesheet_week_of') . ' ' . _d($week_start) . ' - ' . _d($week_end); ?>
                                        </button>
                                        <a href="<?php echo admin_url('timesheet/manage_weekly?week=' . timesheet_get_next_week($week_start)); ?>" 
                                           class="btn btn-default">
                                            <?php echo _l('timesheet_next_week'); ?> <i class="fa fa-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo admin_url('timesheet/manage'); ?>" class="btn btn-info">
                                        <i class="fa fa-flash"></i> <?php echo _l('timesheet_quick_approvals'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('timesheet/manage_weekly?week=' . timesheet_get_week_start()); ?>" 
                                       class="btn btn-default">
                                        <i class="fa fa-calendar-o"></i> <?php echo _l('timesheet_this_week'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Approvals Table -->
                        <?php if (empty($weekly_approvals)): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <?php echo _l('timesheet_no_approvals_this_week'); ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff'); ?></th>
                                            <th class="text-center"><?php echo _l('status'); ?></th>
                                            <th class="text-center"><?php echo _l('timesheet_total'); ?> <?php echo _l('timesheet_hours'); ?></th>
                                            <th><?php echo _l('submitted_at'); ?></th>
                                            <th class="text-center"><?php echo _l('options'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($weekly_approvals as $approval): ?>
                                        <tr data-approval-id="<?php echo $approval->id; ?>">
                                            <td>
                                                <strong><?php echo $approval->firstname . ' ' . $approval->lastname; ?></strong>
                                                <br><small class="text-muted"><?php echo $approval->email; ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $status_class = '';
                                                $status_icon = '';
                                                switch($approval->status) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        $status_icon = 'clock-o';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        $status_icon = 'check';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        $status_icon = 'times';
                                                        break;
                                                }
                                                ?>
                                                <span class="label label-<?php echo $status_class; ?>">
                                                    <i class="fa fa-<?php echo $status_icon; ?>"></i> 
                                                    <?php echo ucfirst($approval->status); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="total-hours-display" data-approval-id="<?php echo $approval->id; ?>">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($approval->submitted_at): ?>
                                                    <?php echo _dt($approval->submitted_at); ?>
                                                <?php endif; ?>
                                                
                                                <?php if ($approval->approved_at): ?>
                                                    <br><small class="text-muted">
                                                        <?php echo $approval->status == 'approved' ? 'Approved' : 'Rejected'; ?>: 
                                                        <?php echo _dt($approval->approved_at); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo admin_url('timesheet/view_approval/' . $approval->id); ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="<?php echo _l('view'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($approval->status == 'pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                                data-approval-id="<?php echo $approval->id; ?>" 
                                                                title="<?php echo _l('timesheet_approve'); ?>">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                                data-approval-id="<?php echo $approval->id; ?>" 
                                                                title="<?php echo _l('timesheet_reject'); ?>">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    <?php elseif ($approval->status == 'approved'): ?>
                                                        <button type="button" class="btn btn-sm btn-warning cancel-approval-btn" 
                                                                data-approval-id="<?php echo $approval->id; ?>" 
                                                                title="<?php echo _l('timesheet_cancel_approval'); ?>">
                                                            <i class="fa fa-undo"></i> <?php echo _l('cancel'); ?>
                                                        </button>
                                                    <?php elseif ($approval->status == 'rejected'): ?>
                                                        <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                                data-approval-id="<?php echo $approval->id; ?>" 
                                                                title="<?php echo _l('timesheet_approve'); ?>">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        
                                                        <?php if ($approval->rejection_reason): ?>
                                                        <button type="button" class="btn btn-sm btn-default" 
                                                                title="<?php echo htmlspecialchars($approval->rejection_reason); ?>"
                                                                data-toggle="tooltip">
                                                            <i class="fa fa-comment"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejection-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('timesheet_reject'); ?> Timesheet</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejection-reason" rows="4" 
                              placeholder="Please provide a reason for rejecting this timesheet..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-danger" id="confirm-rejection">
                    <i class="fa fa-times"></i> <?php echo _l('timesheet_reject'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var weekly_manage_data = {
    admin_url: '<?php echo admin_url(); ?>',
    weekly_approvals: <?php echo json_encode($weekly_approvals); ?>
};
</script>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/manage_weekly.js'); ?>"></script>
</body>
</html>
