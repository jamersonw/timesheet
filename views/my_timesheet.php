<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet_modals.css'); ?>">
<div id="wrapper">
    <div class="content"><?php echo form_open($this->uri->uri_string(), array('id' => 'timesheet-form')); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-clock-o"></i> <?php echo _l('timesheet_my_timesheet'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <div class="row mbot15">
                            <div class="col-md-6">
                                <h4><?php echo _l('timesheet_week_of') . ' ' . _d($week_start) . ' - ' . _d($week_end); ?></h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?php echo admin_url('timesheet?week=' . date('Y-m-d', strtotime('-7 days', strtotime($week_start)))); ?>" class="btn btn-default"><i class="fa fa-chevron-left"></i> <?php echo _l('timesheet_previous_week'); ?></a>
                                    <a href="<?php echo admin_url('timesheet'); ?>" class="btn btn-info"><?php echo _l('timesheet_this_week'); ?></a>
                                    <a href="<?php echo admin_url('timesheet?week=' . date('Y-m-d', strtotime('+7 days', strtotime($week_start)))); ?>" class="btn btn-default"><?php echo _l('timesheet_next_week'); ?> <i class="fa fa-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>

                        <?php if ($can_edit): ?>
                        <div class="row mbot15">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" id="add-project-row"><i class="fa fa-plus"></i> <?php echo _l('timesheet_select_project'); ?></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered timesheet-table">
                                <thead>
                                    <tr>
                                        <th width="200"><?php echo _l('project'); ?></th>
                                        <?php foreach ($week_dates as $i => $date): ?>
                                            <th class="text-center" width="100">
                                                <?php 
                                                $day_name_key = 'timesheet_' . strtolower(date('l', strtotime($date)));
                                                echo _l($day_name_key) . '<br><small>' . _d($date) . '</small>'; 
                                                ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" width="100"><?php echo _l('timesheet_total'); ?></th>
                                        <?php if ($can_edit): ?><th width="50"></th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody id="timesheet-entries">
                                    <?php foreach ($entries as $entry): ?>
                                    <tr data-project-id="<?php echo $entry['project_id']; ?>" data-task-id="<?php echo $entry['task_id']; ?>">
                                        <td>
                                            <strong><?php echo $entry['project_name']; ?></strong>
                                            <?php if (!empty($entry['task_name'])): ?>
                                                <br><small class="text-muted"><?php echo $entry['task_name']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                        <td class="text-center">
                                            <input type="text" 
                                                   class="form-control hours-input text-center" 
                                                   data-day="<?php echo $day; ?>"
                                                   value="<?php echo ($entry['days'][$day]['hours'] > 0) ? str_replace('.', ',', $entry['days'][$day]['hours']) : ''; ?>"
                                                   placeholder="0,00"
                                                   <?php if (!$can_edit) echo 'disabled'; ?>>
                                        </td>
                                        <?php endfor; ?>
                                        <td class="text-center total-hours"><strong><?php echo str_replace('.', ',', $entry['total_hours']); ?></strong></td>
                                        <?php if ($can_edit): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="info">
                                        <td><strong><?php echo _l('timesheet_total'); ?>:</strong></td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                        <td class="text-center"><strong class="daily-total" data-day="<?php echo $day; ?>"><?php echo str_replace('.', ',', $daily_totals[$day]); ?></strong></td>
                                        <?php endfor; ?>
                                        <td class="text-center"><strong class="week-total"><?php echo str_replace('.', ',', $week_total); ?></strong></td>
                                        <?php if ($can_edit): ?><td></td><?php endif; ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row m-t-20">
                            <div class="col-md-12 text-right">
                                <span id="save-indicator" class="text-muted" style="margin-right: 15px; font-style: italic;"></span>

                                <?php if ($approval_status && $approval_status->status == 'pending'): ?>
                                    <div class="alert alert-info pull-left" style="margin-top: -10px;"><?php echo _l('timesheet_status_pending_message'); ?></div>
                                    <button type="button" class="btn btn-warning" id="cancel-submission"><i class="fa fa-undo"></i> <?php echo _l('timesheet_cancel_submission'); ?></button>
                                <?php elseif ($approval_status && $approval_status->status == 'rejected'): ?>
                                    <div class="alert alert-danger pull-left" style="margin-top: -10px;">
                                       <strong><?php echo _l('timesheet_status_rejected_message'); ?></strong><br>
                                       <strong><?php echo _l('timesheet_rejection_reason'); ?>:</strong> <?php echo nl2br($approval_status->rejection_reason); ?>
                                    </div>
                                    <button type="button" class="btn btn-success" id="submit-timesheet"><i class="fa fa-paper-plane"></i> <?php echo _l('timesheet_submit'); ?></button>
                                <?php elseif ($approval_status && $approval_status->status == 'approved'): ?>
                                    <div class="alert alert-success pull-left" style="margin-top: -10px;"><?php echo _l('timesheet_status_approved_message'); ?></div>
                                <?php else: // Draft or no status ?>
                                    <button type="button" class="btn btn-success" id="submit-timesheet"><i class="fa fa-paper-plane"></i> <?php echo _l('timesheet_submit'); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="project-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('timesheet_select_project'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?php echo _l('project'); ?></label>
                    <select class="form-control" id="project-select">
                        <option value=""><?php echo _l('timesheet_select_project'); ?></option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project->id; ?>"><?php echo $project->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="task-group" style="display: none;">
                    <label><?php echo _l('task'); ?></label>
                    <select class="form-control" id="task-select">
                        <option value=""><?php echo _l('timesheet_select_task'); ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="add-project-confirm"><?php echo _l('add'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
var timesheet_data = {
    week_start: '<?php echo $week_start; ?>',
    admin_url: '<?php echo admin_url(); ?>',
    confirm_cancel_submission: "<?php echo _l('timesheet_confirm_cancel_submission'); ?>",
    confirm_submit: "<?php echo _l('timesheet_confirm_submit'); ?>"
};
</script>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
</body>
</html>