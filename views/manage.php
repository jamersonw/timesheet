<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet_modals.css'); ?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_mid_height">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-check-circle"></i> <?php echo _l('timesheet_approvals'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#pending-tab" role="tab" data-toggle="tab">
                                    <i class="fa fa-clock-o"></i> Pending 
                                    <span class="badge"><?php echo count($pending_approvals); ?></span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#all-tab" role="tab" data-toggle="tab">
                                    <i class="fa fa-list"></i> All Recent
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Pending Approvals Tab -->
                            <div role="tabpanel" class="tab-pane active" id="pending-tab">
                                <div class="mt-3">
                                    <?php if (empty($pending_approvals)): ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> 
                                            No pending timesheet approvals at this time.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Funcionário</th>
                                                        <th>Semana</th>
                                                        <th>Total de Horas</th>
                                                        <th>Enviado em</th>
                                                        <th class="text-center">Ações</th>
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
                                                            <?php echo _d($approval->week_start_date); ?>
                                                            <br><small class="text-muted">
                                                                <?php echo _d($approval->week_start_date) . ' - ' . _d(timesheet_get_week_end($approval->week_start_date)); ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="total-hours-display" data-approval-id="<?php echo $approval->id; ?>">
                                                                <i class="fa fa-spinner fa-spin"></i>
                                                            </span>
                                                        </td>
                                                        <td><?php echo _dt($approval->submitted_at); ?></td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?php echo admin_url('timesheet/view_approval/' . $approval->id); ?>" 
                                                                   class="btn btn-sm btn-info" 
                                                                   title="<?php echo _l('view'); ?>">
                                                                    <i class="fa fa-eye"></i> Ver
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                                        data-approval-id="<?php echo $approval->id; ?>" 
                                                                        title="<?php echo _l('timesheet_approve'); ?>">
                                                                    <i class="fa fa-check"></i> Aprovar
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                                        data-approval-id="<?php echo $approval->id; ?>" 
                                                                        title="<?php echo _l('timesheet_reject'); ?>">
                                                                    <i class="fa fa-times"></i> Rejeitar
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

                            <!-- All Recent Approvals Tab -->
                            <div role="tabpanel" class="tab-pane" id="all-tab">
                                <div class="mt-3">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Funcionário</th>
                                                    <th>Semana</th>
                                                    <th>Total de Horas</th>
                                                    <th>Enviado em</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($all_approvals)): ?>
                                                    <div class="alert alert-info">
                                                        <i class="fa fa-info-circle"></i> 
                                                        No timesheet approvals found.
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($all_approvals as $approval): ?>
                                                    <tr data-approval-id="<?php echo $approval->id; ?>">
                                                        <td>
                                                            <strong><?php echo $approval->firstname . ' ' . $approval->lastname; ?></strong>
                                                            <br><small class="text-muted"><?php echo $approval->email; ?></small>
                                                        </td>
                                                        <td>
                                                            <?php echo _d($approval->week_start_date); ?>
                                                            <br><small class="text-muted">
                                                                <?php echo _d($approval->week_start_date) . ' - ' . _d(timesheet_get_week_end($approval->week_start_date)); ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="total-hours-display" data-approval-id="<?php echo $approval->id; ?>">
                                                                <i class="fa fa-spinner fa-spin"></i>
                                                            </span>
                                                        </td>
                                                        <td><?php echo _dt($approval->submitted_at); ?></td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?php echo admin_url('timesheet/view_approval/' . $approval->id); ?>" 
                                                                   class="btn btn-sm btn-info" 
                                                                   title="<?php echo _l('view'); ?>">
                                                                    <i class="fa fa-eye"></i> Ver
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                                        data-approval-id="<?php echo $approval->id; ?>" 
                                                                        title="<?php echo _l('timesheet_approve'); ?>">
                                                                    <i class="fa fa-check"></i> Aprovar
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                                        data-approval-id="<?php echo $approval->id; ?>" 
                                                                        title="<?php echo _l('timesheet_reject'); ?>">
                                                                    <i class="fa fa-times"></i> Rejeitar
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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