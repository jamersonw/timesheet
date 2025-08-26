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
                            <i class="fa fa-clock-o"></i> 
                            <?php echo _l('timesheet_approvals'); ?> - 
                            <?php echo $approval->firstname . ' ' . $approval->lastname; ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <!-- Staff and Week Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h4>
                                    <i class="fa fa-user"></i> 
                                    <?php echo $approval->firstname . ' ' . $approval->lastname; ?>
                                </h4>
                                <p class="text-muted"><?php echo $approval->email; ?></p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h4>
                                    <i class="fa fa-calendar"></i> 
                                    <?php echo _l('timesheet_week_of') . ' ' . _d($approval->week_start_date); ?>
                                </h4>
                                <p class="text-muted">
                                    <?php echo _d($approval->week_start_date) . ' - ' . _d(timesheet_get_week_end($approval->week_start_date)); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-<?php echo $approval->status == 'approved' ? 'success' : ($approval->status == 'rejected' ? 'danger' : 'info'); ?>">
                                    <strong>Status: <?php echo timesheet_status_badge($approval->status); ?></strong>
                                    <?php if ($approval->status == 'pending'): ?>
                                        <span class="pull-right">
                                            <small>Submitted: <?php echo _dt($approval->submitted_at); ?></small>
                                        </span>
                                    <?php elseif ($approval->approved_at): ?>
                                        <span class="pull-right">
                                            <small>
                                                <?php echo $approval->status == 'approved' ? 'Approved' : 'Rejected'; ?>: 
                                                <?php echo _dt($approval->approved_at); ?>
                                            </small>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($approval->status == 'rejected' && $approval->rejection_reason): ?>
                                        <br><br>
                                        <strong>Rejection Reason:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($approval->rejection_reason)); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Timesheet Details -->
                        <div class="table-responsive">
                            <table class="table table-bordered timesheet-table">
                                <thead>
                                    <tr>
                                        <th width="200"><?php echo _l('projects'); ?></th>
                                        <?php foreach ($week_dates as $i => $date): ?>
                                            <th class="text-center" width="100">
                                                <?php 
                                                $day_names = [
                                                    _l('timesheet_monday'),
                                                    _l('timesheet_tuesday'), 
                                                    _l('timesheet_wednesday'),
                                                    _l('timesheet_thursday'),
                                                    _l('timesheet_friday'),
                                                    _l('timesheet_saturday'),
                                                    _l('timesheet_sunday')
                                                ];
                                                echo $day_names[$i] . '<br><small>' . _d($date) . '</small>';
                                                ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" width="100"><?php echo _l('timesheet_total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($entries)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                No timesheet entries found for this week.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($entries as $entry): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $entry['project_name']; ?></strong>
                                                <?php if ($entry['task_name']): ?>
                                                    <br><small class="text-muted"><?php echo $entry['task_name']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <?php for ($day = 1; $day <= 7; $day++): ?>
                                            <td class="text-center">
                                                <?php echo timesheet_format_hours($entry['days'][$day]['hours']); ?>
                                            </td>
                                            <?php endfor; ?>
                                            <td class="text-center">
                                                <strong><?php echo timesheet_format_hours($entry['total_hours']); ?></strong>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="info">
                                        <td><strong><?php echo _l('timesheet_total'); ?>:</strong></td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                        <td class="text-center">
                                            <strong><?php echo timesheet_format_hours($daily_totals[$day]); ?></strong>
                                        </td>
                                        <?php endfor; ?>
                                        <td class="text-center">
                                            <strong><?php echo timesheet_format_hours($week_total); ?></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <?php if ($approval->status == 'pending'): ?>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <a href="<?php echo admin_url('timesheet/manage'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                                <button type="button" class="btn btn-success approve-btn" data-approval-id="<?php echo $approval->id; ?>">
                                    <i class="fa fa-check"></i> <?php echo _l('timesheet_approve'); ?>
                                </button>
                                <button type="button" class="btn btn-danger reject-btn" data-approval-id="<?php echo $approval->id; ?>">
                                    <i class="fa fa-times"></i> <?php echo _l('timesheet_reject'); ?>
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <a href="<?php echo admin_url('timesheet/manage'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
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

<!-- Custom Modals -->
<div id="custom-confirm-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirmation</h4>
            </div>
            <div class="modal-body">
                <p id="confirm-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-yes">Yes</button>
            </div>
        </div>
    </div>
</div>

<div id="custom-alert-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Alert</h4>
            </div>
            <div class="modal-body">
                <p id="alert-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
var approval_data = {
    admin_url: '<?php echo admin_url(); ?>',
    approval_id: <?php echo $approval->id; ?>
};
</script>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/manage.js'); ?>"></script>
</body>
</html>