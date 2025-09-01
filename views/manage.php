
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet_modals.css'); ?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-check-circle"></i> <?php echo _l('timesheet_approvals'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <?php if (empty($pending_approvals)): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <?php echo _l('timesheet_no_pending_approvals'); ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff'); ?></th>
                                            <th>Projeto / Tarefa</th>
                                            <th><?php echo _l('timesheet_total'); ?> <?php echo _l('timesheet_hours'); ?></th>
                                            <th><?php echo _l('submitted_at'); ?></th>
                                            <th class="text-center"><?php echo _l('options'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_approvals as $approval): ?>
                                        <tr data-approval-id="<?php echo $approval->id; ?>">
                                            <td>
                                                <strong><?php echo $approval->firstname . ' ' . $approval->lastname; ?></strong>
                                                <br><small class="text-muted"><?php echo $approval->email; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $approval->project_name; ?></strong>
                                                <br><small class="text-muted"><?php echo $approval->task_name; ?></small>
                                                <br><span class="label label-default"><?php echo _d($approval->week_start_date); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="total-hours-display" data-approval-id="<?php echo $approval->id; ?>" 
                                                      data-task-id="<?php echo $approval->task_id; ?>" 
                                                      data-staff-id="<?php echo $approval->staff_id; ?>" 
                                                      data-week-start="<?php echo $approval->week_start_date; ?>">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </span>
                                            </td>
                                            <td><?php echo _dt($approval->submitted_at); ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo admin_url('timesheet/view_approval/' . $approval->id); ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="<?php echo _l('view'); ?>">
                                                        <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                            data-approval-id="<?php echo $approval->id; ?>" 
                                                            title="<?php echo _l('timesheet_approve'); ?>">
                                                        <i class="fa fa-check"></i> <?php echo _l('timesheet_approve'); ?>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                            data-approval-id="<?php echo $approval->id; ?>" 
                                                            title="<?php echo _l('timesheet_reject'); ?>">
                                                        <i class="fa fa-times"></i> <?php echo _l('timesheet_reject'); ?>
                                                    </button>
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
var manage_data = {
    admin_url: '<?php echo admin_url(); ?>',
    pending_approvals: <?php echo json_encode($pending_approvals); ?>
};
</script>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/manage.js'); ?>"></script>
</body>
</html>
