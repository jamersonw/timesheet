<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet.css'); ?>">
<link rel="stylesheet" href="<?php echo module_dir_url('timesheet', 'assets/css/timesheet_modals.css'); ?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-calendar-check-o"></i> <?php echo _l('timesheet_weekly_approvals'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <!-- Week Navigation -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="week-navigation text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo admin_url('timesheet/manage_weekly?week=' . date('Y-m-d', strtotime($week_start . ' -7 days'))); ?>" 
                                           class="btn btn-default">
                                            <i class="fa fa-chevron-left"></i> <?php echo _l('timesheet_previous_week'); ?>
                                        </a>
                                        <span class="btn btn-info">
                                            <i class="fa fa-calendar"></i> 
                                            <?php echo _d($week_start) . ' - ' . _d($week_end); ?>
                                        </span>
                                        <a href="<?php echo admin_url('timesheet/manage_weekly?week=' . date('Y-m-d', strtotime($week_start . ' +7 days'))); ?>" 
                                           class="btn btn-default">
                                            <?php echo _l('timesheet_next_week'); ?> <i class="fa fa-chevron-right"></i>
                                        </a>
                                    </div>
                                    <div class="mt-2">
                                        <a href="<?php echo admin_url('timesheet/manage_weekly'); ?>" class="btn btn-sm btn-success">
                                            <i class="fa fa-calendar"></i> <?php echo _l('timesheet_current_week'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($weekly_approvals)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Nenhuma aprovação pendente para a semana selecionada.</strong>
                                <br><small>Semana de <?php echo _d($week_start) . ' a ' . _d($week_end); ?></small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong><?php echo count($weekly_approvals); ?> aprovação(ões) pendente(s) para a semana selecionada</strong>
                                <br><small>Semana de <?php echo _d($week_start) . ' a ' . _d($week_end); ?></small>
                            </div>

                            <!-- Controles de Seleção em Lote -->
                            <div class="panel panel-info batch-controls" style="margin-bottom: 20px;">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="panel-title" style="margin: 0;">
                                                <i class="fa fa-check-square-o"></i> Seleção em Lote
                                            </h4>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <span class="selection-counter">
                                                <strong>0</strong> tarefas selecionadas
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="select-all-tasks" />
                                                    <strong>Selecionar Todas as Tarefas Pendentes</strong>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <div class="btn-group batch-actions" style="margin-left: 10px;">
                                                <button type="button" class="btn btn-success btn-sm batch-approve-btn" disabled>
                                                    <i class="fa fa-check"></i> Aprovar Selecionadas
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm batch-reject-btn" disabled>
                                                    <i class="fa fa-times"></i> Rejeitar Selecionadas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php foreach ($weekly_approvals as $approval): ?>
                            <div class="panel panel-default approval-panel approval-<?php echo $approval->status; ?>" data-approval-id="<?php echo $approval->id; ?>">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h4 class="panel-title" style="margin: 0;">
                                                <i class="fa fa-user"></i> 
                                                <strong><?php echo $approval->firstname . ' ' . $approval->lastname; ?></strong>
                                                <?php if ($approval->status == 'approved'): ?>
                                                    <span class="label label-success"><i class="fa fa-check"></i> Aprovado</span>
                                                <?php elseif ($approval->status == 'pending'): ?>
                                                    <span class="label label-warning"><i class="fa fa-clock-o"></i> Pendente</span>
                                                <?php endif; ?>
                                                <br><small class="text-muted"><?php echo $approval->email; ?></small>
                                            </h4>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <div class="approval-actions">
                                                <!-- Botão Ver sempre visível -->
                                                <a href="<?php echo admin_url('timesheet/view_approval/' . $approval->id); ?>" 
                                                   class="btn btn-sm btn-info" title="Ver Detalhes">
                                                    <i class="fa fa-eye"></i> Ver
                                                </a>

                                                <?php if ($approval->status == 'pending'): ?>
                                                    <!-- Status Pendente - Botões Aprovar e Rejeitar -->
                                                    <button type="button" class="btn btn-sm btn-success user-batch-approve-btn" 
                                                            data-user-id="<?php echo $approval->id; ?>" 
                                                            title="Aprovar Tarefas Selecionadas">
                                                        <i class="fa fa-check"></i> Aprovar Selecionadas
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger user-batch-reject-btn" 
                                                            data-user-id="<?php echo $approval->id; ?>" 
                                                            title="Rejeitar Tarefas Selecionadas">
                                                        <i class="fa fa-times"></i> Rejeitar Selecionadas
                                                    </button>
                                                <?php elseif ($approval->status == 'approved'): ?>
                                                    <!-- Status Aprovado - Botão Cancelar Aprovação -->
                                                    <button type="button" class="btn btn-sm btn-warning cancel-approval-btn" 
                                                            data-approval-id="<?php echo $approval->id; ?>" 
                                                            title="Cancelar Aprovação">
                                                        <i class="fa fa-undo"></i> Cancelar Aprovação
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fa fa-clock-o"></i> Submetido em: <?php echo _dt($approval->submitted_at); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <small class="text-muted">
                                                Total de horas: 
                                                <span class="total-hours-display" data-approval-id="<?php echo $approval->id; ?>">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timesheet Preview -->
                                <div class="panel-body">
                                    <div class="timesheet-preview" id="preview-<?php echo $approval->id; ?>">
                                        <div class="text-center text-muted">
                                            <i class="fa fa-spinner fa-spin"></i> Carregando preview do timesheet...
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Back to Quick Approvals -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="<?php echo admin_url('timesheet/manage'); ?>" class="btn btn-default">
                                    <i class="fa fa-list"></i> Ver Aprovações Rápidas
                                </a>
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
console.log('[Weekly View] ===== CARREGANDO TELA SEMANAL =====');
console.log('[Weekly View] PHP - Week start:', '<?php echo $week_start; ?>');
console.log('[Weekly View] PHP - Week end:', '<?php echo $week_end; ?>');
console.log('[Weekly View] PHP - Title:', '<?php echo $title; ?>');
console.log('[Weekly View] PHP - Weekly approvals count:', <?php echo count($weekly_approvals); ?>);

<?php if (!empty($weekly_approvals)): ?>
console.log('[Weekly View] PHP - Primeira aprovação:', <?php echo json_encode($weekly_approvals[0]); ?>);
<?php endif; ?>

try {
    var manage_weekly_data = {
        admin_url: '<?php echo admin_url(); ?>',
        weekly_approvals: <?php echo json_encode($weekly_approvals); ?>,
        week_start: '<?php echo $week_start; ?>',
        week_dates: <?php echo json_encode($week_dates); ?>
    };

    console.log('[Weekly View] ✅ manage_weekly_data criado com sucesso:');
    console.log('[Weekly View] - admin_url:', manage_weekly_data.admin_url);
    console.log('[Weekly View] - weekly_approvals count:', manage_weekly_data.weekly_approvals.length);
    console.log('[Weekly View] - week_start:', manage_weekly_data.week_start);
    console.log('[Weekly View] - week_dates:', manage_weekly_data.week_dates);

    if (manage_weekly_data.weekly_approvals.length > 0) {
        console.log('[Weekly View] Primeira aprovação no JS:', manage_weekly_data.weekly_approvals[0]);
    }

} catch (error) {
    console.error('[Weekly View ERROR] Erro ao criar manage_weekly_data:', error);
    console.error('[Weekly View ERROR] Stack trace:', error.stack);
}

console.log('[Weekly View] ===== FIM DO CARREGAMENTO =====');
</script>

<style>
.approval-panel {
    margin-bottom: 20px;
}

.approval-panel.approval-pending {
    border-left: 4px solid #f39c12;
}

.approval-panel.approval-pending .panel-heading {
    background-color: #fdf2e9;
}

.approval-panel.approval-approved {
    border-left: 4px solid #5cb85c;
}

.approval-panel.approval-approved .panel-heading {
    background-color: #d4edda;
}

.approval-actions .btn {
    margin-left: 5px;
}

.week-navigation {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.timesheet-preview table {
    font-size: 12px;
}

.timesheet-preview .table th,
.timesheet-preview .table td {
    padding: 4px 6px;
    text-align: center;
}

.total-hours-display {
    font-weight: bold;
    color: #2c3e50;
}

/* Estilos para seleção em lote */
.batch-controls {
    border-left: 4px solid #5bc0de;
}

.batch-controls .panel-heading {
    background-color: #d9edf7;
}

.selection-counter {
    font-size: 14px;
    color: #31708f;
}

.task-selection-area {
    border: 1px solid #ddd;
}

.task-checkbox {
    margin-right: 8px;
}

.task-checkbox:disabled {
    opacity: 0.5;
}

.batch-actions .btn {
    margin-left: 5px;
}

.batch-actions .btn:disabled {
    opacity: 0.6;
}

/* Estilos para controles por usuário */
.user-batch-controls {
    border-left: 4px solid #17a2b8;
}

.user-selection-counter {
    font-size: 14px;
    color: #138496;
}

.user-batch-actions .btn {
    margin-left: 5px;
}

.user-batch-actions .btn:disabled {
    opacity: 0.6;
}

.select-user-tasks {
    margin-right: 8px;
}

/* Indicadores visuais para tarefas selecionadas */
.task-checkbox:checked + strong {
    color: #3c763d;
}

/* Animações suaves */
.batch-controls {
    transition: all 0.3s ease;
}

.task-selection-area {
    transition: background-color 0.2s ease;
}

.task-selection-area:hover {
    background-color: #f0f0f0;
}

/* Estilos específicos para checkboxes na tabela */
.timesheet-preview .task-checkbox,
.timesheet-preview .select-user-tasks-header {
    cursor: pointer;
    transform: scale(1.2);
}

.timesheet-preview .task-checkbox:hover,
.timesheet-preview .select-user-tasks-header:hover {
    transform: scale(1.3);
}

.timesheet-preview tr:hover {
    background-color: #f8f9fa;
}

.timesheet-preview .task-checkbox:checked {
    accent-color: #28a745;
}
</style>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/manage_weekly.js'); ?>"></script>
</body>
</html>