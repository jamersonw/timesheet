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
                            <i class="fa fa-clock-o"></i> <?php echo _l('timesheet_my_timesheet'); ?>
                        </h3>
                    </div>
                    <div class="panel-body">

                        <!-- 1. Week Navigation (Estrutura Original) -->
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

                        <!-- 2. Banner de Status Inteligente (Nova Lógica) -->
                        <?php
                        $status_info = $approval_status;
                        $banner_class = 'info';
                        $banner_icon = 'fa-info-circle';
                        $banner_message = 'Preencha suas horas e submeta a semana para aprovação.';
                        $can_add_project = true;
                        $has_editable_tasks = false; // Flag para controlar o botão de submissão

                        if ($status_info) {
                            if ($status_info->rejected_tasks > 0) {
                                $banner_class = 'danger';
                                $banner_icon = 'fa-exclamation-triangle';
                                $banner_message = '<strong>Ação Necessária:</strong> Você tem ' . $status_info->rejected_tasks . ' tarefa(s) rejeitada(s) que precisam de correção.';
                                $has_editable_tasks = true;
                            } elseif ($status_info->total_tasks > 0 && $status_info->approved_tasks == $status_info->total_tasks) {
                                $banner_class = 'success';
                                $banner_icon = 'fa-check-circle';
                                $banner_message = '<strong>Semana Finalizada:</strong> Todas as suas tarefas foram aprovadas.';
                                $can_add_project = false;
                            } elseif ($status_info->pending_tasks > 0 && $status_info->approved_tasks > 0) {
                                $banner_class = 'info';
                                $banner_icon = 'fa-info-circle';
                                $banner_message = '<strong>Status Misto:</strong> Você tem tarefas em diferentes estágios de aprovação. Verifique a tabela abaixo.';
                            } elseif ($status_info->pending_tasks > 0) {
                                $banner_class = 'warning';
                                $banner_icon = 'fa-clock-o';
                                $banner_message = '<strong>Em Aprovação:</strong> Suas tarefas pendentes estão aguardando avaliação.';
                            }
                            
                            // Verifica se existem tarefas em rascunho (não aprovadas e não pendentes)
                            if ($status_info->total_tasks > ($status_info->approved_tasks + $status_info->pending_tasks)) {
                                $has_editable_tasks = true;
                            }
                        } else if (!empty($entries)) {
                            $has_editable_tasks = true;
                        }
                        ?>
                        <div class="alert alert-<?php echo $banner_class; ?>">
                            <i class="fa <?php echo $banner_icon; ?>"></i> <?php echo $banner_message; ?>
                        </div>

                        <!-- 3. Botão Adicionar Projeto (Lógica Original com ajuste) -->
                        <?php if ($can_add_project): ?>
                        <div class="row mbot15">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" id="add-project-row"><i class="fa fa-plus"></i> <?php echo _l('timesheet_select_project'); ?></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 4. Tabela de Horas (Estrutura Original + Nova Lógica de Status) -->
                        <div class="table-responsive">
                            <table class="table table-bordered timesheet-table">
                                <thead>
                                    <tr>
                                        <th width="250"><?php echo _l('project'); ?> / Tarefa</th>
                                        <?php foreach ($week_dates as $date): ?>
                                            <th class="text-center" width="100">
                                                <?php echo _l('timesheet_' . strtolower(date('l', strtotime($date)))) . '<br><small>' . _d($date) . '</small>'; ?>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" width="100"><?php echo _l('timesheet_total'); ?></th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="timesheet-entries">
                                    <?php foreach ($entries as $entry): ?>
                                    <?php
                                        $is_locked = in_array($entry['status'], ['pending', 'approved']);
                                        $is_rejected = $entry['status'] == 'rejected';
                                        $row_class = $is_rejected ? 'danger' : '';
                                        $can_remove_row = !$is_locked && !$is_rejected;
                                    ?>
                                    <tr data-project-id="<?php echo $entry['project_id']; ?>" data-task-id="<?php echo $entry['task_id']; ?>" class="<?php echo $row_class; ?>">
                                        <td>
                                            <strong><?php echo $entry['project_name']; ?></strong>
                                            <?php if (!empty($entry['task_name'])): ?>
                                                <br><small class="text-muted"><?php echo $entry['task_name']; ?></small>
                                            <?php endif; ?>
                                            <div class="task-status" style="margin-top: 5px;">
                                                <?php if ($entry['status'] == 'pending') : ?>
                                                    <span class="label label-warning">Pendente</span>
                                                <?php elseif ($entry['status'] == 'approved') : ?>
                                                    <span class="label label-success">Aprovado</span>
                                                <?php elseif ($entry['status'] == 'rejected') : ?>
                                                    <span class="label label-danger" style="cursor:pointer;" title="Motivo da Rejeição: <?php echo htmlspecialchars($entry['rejection_reason']); ?>">
                                                        Rejeitado <i class="fa fa-info-circle"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                        <td class="text-center">
                                            <input type="text" 
                                                   class="form-control hours-input text-center" 
                                                   data-day="<?php echo $day; ?>"
                                                   value="<?php echo ($entry['days'][$day]['hours'] > 0) ? str_replace('.', ',', $entry['days'][$day]['hours']) : ''; ?>"
                                                   placeholder="0,00"
                                                   <?php if ($is_locked) echo 'disabled'; ?>>
                                        </td>
                                        <?php endfor; ?>
                                        <td class="text-center total-hours"><strong><?php echo str_replace('.', ',', $entry['total_hours']); ?></strong></td>
                                        <td class="text-center">
                                            <?php if ($can_remove_row): ?>
                                                <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
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
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- 5. Botões de Ação (Lógica Nova com IDs Originais) -->
                        <div class="row m-t-20">
                            <div class="col-md-12 text-right">
                                <span id="save-indicator" class="text-muted" style="margin-right: 15px; font-style: italic;"></span>
                                
                                <?php if ($status_info && $status_info->pending_tasks > 0) : ?>
                                    <button type="button" class="btn btn-warning" id="cancel-submission">
                                        <i class="fa fa-undo"></i> Retirar Tarefas Pendentes
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($has_editable_tasks) : ?>
                                    <button type="button" class="btn btn-success" id="submit-timesheet">
                                        <i class="fa fa-paper-plane"></i> Submeter para Aprovação
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal (Estrutura Original) -->
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

var timesheet_lang = {
    no_activities_warning: "<?php echo _l('timesheet_no_activities_warning'); ?>",
    no_activities_title: "<?php echo _l('timesheet_no_activities_title'); ?>",
    confirm_submit_default: "<?php echo _l('timesheet_confirm_submit_default'); ?>",
    attention: "<?php echo _l('timesheet_attention'); ?>",
    submitting_zero_hours: "<?php echo _l('timesheet_submitting_zero_hours'); ?>",
    submit_for_approval: "<?php echo _l('timesheet_submit_for_approval'); ?>",
    submit: "<?php echo _l('timesheet_submit'); ?>",
    cancel: "<?php echo _l('cancel'); ?>",
    cancel_submission: "<?php echo _l('timesheet_cancel_submission'); ?>",
    confirm_cancel_submission_default: "<?php echo _l('timesheet_confirm_cancel_submission_default'); ?>",
    keep_as_is: "<?php echo _l('timesheet_keep_as_is'); ?>",
    select_project_task_required: "<?php echo _l('timesheet_select_project_task_required'); ?>",
    required_selection: "<?php echo _l('timesheet_required_selection'); ?>",
    project_already_added: "<?php echo _l('timesheet_project_already_added'); ?>",
    duplicate_project: "<?php echo _l('timesheet_duplicate_project'); ?>",
    remove_row: "<?php echo _l('timesheet_remove_row'); ?>",
    confirm_remove_row: "<?php echo _l('timesheet_confirm_remove_row'); ?>",
    remove: "<?php echo _l('remove'); ?>"
};
</script>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet.js'); ?>"></script>
<script src="<?php echo module_dir_url('timesheet', 'assets/js/timesheet_modals.js'); ?>"></script>
</body>
</html>
```

Com essas duas correções, o fluxo de aprovação parcial ficará completo e robusto. O usuário poderá editar e reenviar as tarefas que voltaram para rascunho sem ser bloqueado pelas tarefas já aprovad