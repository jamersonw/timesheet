<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Timesheet extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('timesheet/timesheet_model');
        $this->load->helper('timesheet/timesheet');
    }

    public function index()
    {
        if (!has_permission('timesheet', '', 'view')) {
            access_denied('timesheet');
        }

        // Debug: Log das permissões do usuário atual
        $staff_id = get_staff_user_id();
        log_activity('[Timesheet Debug] Usuário ' . $staff_id . ' acessando index');
        log_activity('[Timesheet Debug] Permissão view: ' . (has_permission('timesheet', '', 'view') ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] Permissão create: ' . (has_permission('timesheet', '', 'create') ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] Permissão edit: ' . (has_permission('timesheet', '', 'edit') ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] Permissão delete: ' . (has_permission('timesheet', '', 'delete') ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] Permissão approve: ' . (has_permission('timesheet', '', 'approve') ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] É admin: ' . (is_admin() ? 'SIM' : 'NÃO'));
        log_activity('[Timesheet Debug] Pode gerenciar projetos: ' . (timesheet_can_manage_any_project($staff_id) ? 'SIM' : 'NÃO'));

        // Versão 1.4.0: Modo unidirecional - sem processamento de recálculos pendentes

        $week_start = $this->input->get('week') ?: timesheet_get_week_start();
        $data['week_start'] = $week_start;
        $data['week_end'] = timesheet_get_week_end($week_start);
        $data['week_dates'] = timesheet_get_week_dates($week_start);
        $data['projects'] = timesheet_get_staff_projects(get_staff_user_id());
        $data['entries'] = $this->timesheet_model->get_week_entries_grouped(get_staff_user_id(), $week_start);
        $data['daily_totals'] = $this->timesheet_model->get_week_daily_totals(get_staff_user_id(), $week_start);
        $data['week_total'] = $this->timesheet_model->get_week_total_hours(get_staff_user_id(), $week_start);
        $approval_status = $this->timesheet_model->get_week_approval_status(get_staff_user_id(), $week_start);
        $data['approval_status'] = $approval_status;
        $data['can_edit'] = !$approval_status || in_array($approval_status->status, ['rejected', 'draft']);
        $data['title'] = _l('timesheet_my_timesheet');
        $this->load->view('timesheet/my_timesheet', $data);
    }

    /**
     * Get tasks for a project via AJAX
     */
    public function get_project_tasks($project_id)
    {
        if (!has_permission('timesheet', '', 'view')) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        $tasks = timesheet_get_staff_project_tasks(get_staff_user_id(), $project_id);
        echo json_encode($tasks);
    }

    /**
     * Save timesheet entry via AJAX
     */
    public function save_entry()
    {
        if (!has_permission('timesheet', '', 'create') && !has_permission('timesheet', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }
        $staff_id = get_staff_user_id();
        $project_id = $this->input->post('project_id');
        $task_id = $this->input->post('task_id');
        $week_start = $this->input->post('week_start');
        $day_of_week = $this->input->post('day_of_week');
        $hours = (float)$this->input->post('hours');

        if (empty($task_id) || !is_numeric($task_id)) {
            // Se não há tarefa selecionada, retornar sucesso sem salvar
            echo json_encode(['success' => true, 'message' => 'Nenhuma tarefa selecionada para salvar']);
            return;
        }
        if (!$this->timesheet_model->can_edit_week($staff_id, $week_start)) {
            echo json_encode(['success' => false, 'message' => _l('timesheet_cannot_edit_approved')]);
            return;
        }

        $data = [
            'staff_id'        => $staff_id,
            'project_id'      => $project_id,
            'task_id'         => $task_id,
            'week_start_date' => $week_start,
            'day_of_week'     => $day_of_week,
            'hours'           => $hours,
            'status'          => 'draft'
        ];

        if ($this->timesheet_model->save_entry($data)) {
            echo json_encode(['success' => true, 'message' => _l('timesheet_saved_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao salvar a entrada.']);
        }
    }


  /**
     * Submit week for approval
     */
    public function submit_week()
    {
        if (!has_permission('timesheet', '', 'create')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $staff_id = get_staff_user_id();
        $week_start = $this->input->post('week_start');

        // Usar validação específica para submissão (mais permissiva)
        if (!$this->timesheet_model->can_submit_week($staff_id, $week_start)) {
            echo json_encode(['success' => false, 'message' => _l('timesheet_cannot_submit_approved')]);
            return;
        }

        if ($this->timesheet_model->submit_week($staff_id, $week_start)) {
            echo json_encode([
                'success' => true,
                'message' => _l('timesheet_submitted_successfully')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting timesheet']);
        }
    }

    /**
     * Cancel week submission
     */
    public function cancel_submission()
    {
        if (!is_staff_logged_in()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $week_start = $this->input->post('week_start');
        $staff_id = get_staff_user_id();

        if ($this->timesheet_model->cancel_week_submission($staff_id, $week_start)) {
            echo json_encode([
                'success' => true,
                'message' => _l('timesheet_submission_cancelled')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('timesheet_cannot_cancel_submission')
            ]);
        }
    }

    /**
     * Manage timesheets (for managers/admins)
     */
    public function manage()
    {
        if (!has_permission('timesheet', '', 'approve') && !is_admin() && !timesheet_can_manage_any_project(get_staff_user_id())) {
            access_denied('timesheet');
        }

        $data['pending_approvals'] = $this->timesheet_model->get_pending_approvals(get_staff_user_id());
        $data['title'] = _l('timesheet_manage');

        $this->load->view('timesheet/manage', $data);
    }

    /**
     * Manage weekly timesheets (for managers/admins) - Weekly View
     */
    public function manage_weekly()
    {
        if (!has_permission('timesheet', '', 'approve') && !is_admin() && !timesheet_can_manage_any_project(get_staff_user_id())) {
            access_denied('timesheet');
        }

        $week_start = $this->input->get('week') ?: timesheet_get_week_start();
        $data['week_start'] = $week_start;
        $data['week_end'] = timesheet_get_week_end($week_start);
        $data['week_dates'] = timesheet_get_week_dates($week_start);
        $data['weekly_approvals'] = $this->timesheet_model->get_weekly_all_approvals(get_staff_user_id(), $week_start);
        $data['title'] = _l('timesheet_weekly_approvals');

        $this->load->view('timesheet/manage_weekly', $data);
    }

    /**
     * Get week total hours via AJAX for manager view
     */
    public function get_week_total()
    {
        $staff_id = $this->input->get('staff_id');
        $week_start_date = $this->input->get('week_start_date');
        $total_hours = $this->timesheet_model->get_week_total_hours($staff_id, $week_start_date);
        echo json_encode(['success' => true, 'total_hours' => $total_hours]);
    }

    /**
     * Get timesheet preview for weekly approval view
     */
    public function get_timesheet_preview()
    {
        $staff_id = $this->input->get('staff_id');
        $week_start_date = $this->input->get('week_start_date');
        
        $entries = $this->timesheet_model->get_week_entries_grouped($staff_id, $week_start_date);
        $week_dates = timesheet_get_week_dates($week_start_date);
        $daily_totals = $this->timesheet_model->get_week_daily_totals($staff_id, $week_start_date);
        $week_total = $this->timesheet_model->get_week_total_hours($staff_id, $week_start_date);
        
        if (empty($entries)) {
            echo json_encode(['success' => true, 'html' => '<div class="text-center text-muted">Nenhuma entrada encontrada</div>']);
            return;
        }
        
        // Generate HTML preview
        $html = '<div class="table-responsive"><table class="table table-bordered table-condensed">';
        $html .= '<thead><tr>';
        $html .= '<th width="200">Projeto/Tarefa</th>';
        
        $day_names = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
        for ($i = 0; $i < 7; $i++) {
            $html .= '<th class="text-center" width="60">' . $day_names[$i] . '<br><small>' . date('d/m', strtotime($week_dates[$i])) . '</small></th>';
        }
        $html .= '<th class="text-center" width="60">Total</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($entries as $entry) {
            $html .= '<tr>';
            $html .= '<td><strong>' . $entry['project_name'] . '</strong>';
            if ($entry['task_name']) {
                $html .= '<br><small class="text-muted">' . $entry['task_name'] . '</small>';
            }
            $html .= '</td>';
            
            for ($day = 1; $day <= 7; $day++) {
                $hours = $entry['days'][$day]['hours'];
                $html .= '<td class="text-center">' . ($hours > 0 ? number_format($hours, 1) . 'h' : '-') . '</td>';
            }
            $html .= '<td class="text-center"><strong>' . number_format($entry['total_hours'], 1) . 'h</strong></td>';
            $html .= '</tr>';
        }
        
        // Total row
        $html .= '<tr class="info"><td><strong>Total:</strong></td>';
        for ($day = 1; $day <= 7; $day++) {
            $html .= '<td class="text-center"><strong>' . ($daily_totals[$day] > 0 ? number_format($daily_totals[$day], 1) . 'h' : '-') . '</strong></td>';
        }
        $html .= '<td class="text-center"><strong>' . number_format($week_total, 1) . 'h</strong></td>';
        $html .= '</tr>';
        $html .= '</tbody></table></div>';
        
        echo json_encode(['success' => true, 'html' => $html]);
    }

    /**
     * Approve or reject timesheet
     */
    public function approve_reject()
    {
        if (!has_permission('timesheet', '', 'approve') && !has_permission('timesheet', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $approval_id = $this->input->post('approval_id');
        $action = $this->input->post('action');
        $reason = $this->input->post('reason');

        if (empty($approval_id) || empty($action) || !in_array($action, ['approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        if ($action === 'rejected' && empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            return;
        }

        $result = $this->timesheet_model->approve_reject_timesheet($approval_id, $action, get_staff_user_id(), $reason);

        if ($result) {
            $message = $action == 'approved' ? _l('timesheet_approved_successfully') : _l('timesheet_rejected_successfully');
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error processing approval']);
        }
    }

    /**
     * Cancel an approved timesheet
     */
    public function cancel_approval()
    {
        if (!has_permission('timesheet', '', 'approve') && !has_permission('timesheet', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $approval_id = $this->input->post('approval_id');

        if (empty($approval_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $result = $this->timesheet_model->cancel_approval($approval_id, get_staff_user_id());

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Aprovação cancelada com sucesso. Timesheet voltou para rascunho.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao cancelar aprovação']);
        }
    }

    /**
     * FUNCIONALIDADE REMOVIDA - VERSÃO 1.4.0
     * Endpoint de sincronização AJAX não é mais necessário no modo unidirecional.
     */

    /**
     * Função para corrigir registros sem status
     */
    public function fix_missing_status() {
        if (!is_admin()) {
            echo json_encode(['error' => 'Apenas administradores podem usar esta função']);
            return;
        }

        // Buscar entradas sem status ou com status vazio
        $this->db->where('(status IS NULL OR status = "")');
        $entries_without_status = $this->db->get(db_prefix() . 'timesheet_entries')->result();

        $fixed_count = 0;
        foreach ($entries_without_status as $entry) {
            $this->db->where('id', $entry->id);
            $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'draft']);
            $fixed_count++;
            log_activity('[Timesheet Fix] Entrada ID ' . $entry->id . ' corrigida - status definido como draft');
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Corrigidas ' . $fixed_count . ' entradas sem status',
            'fixed_count' => $fixed_count
        ]);
    }

    /**
     * Endpoint de debug para testar sincronização manualmente
     */
    public function debug_sync() {
        if (!is_admin()) {
            echo json_encode(['error' => 'Apenas administradores podem usar esta função']);
            return;
        }

        $action = $this->input->get('action');

        switch ($action) {
            case 'list_timers':
                $timers = $this->timesheet_model->debug_list_perfex_timers(20);
                echo json_encode(['success' => true, 'timers' => $timers]);
                break;

            case 'test_unidirectional':
                log_activity('[Timesheet Debug v1.4.0] Módulo operando em modo UNIDIRECIONAL - Timesheet → Quadro apenas');
                echo json_encode(['success' => true, 'message' => 'Módulo v1.4.0 - Modo unidirecional ativo']);
                break;

            case 'fix_status':
                $this->fix_missing_status();
                return;

            case 'check_entries':
                $staff_id = $this->input->get('staff_id') ?: get_staff_user_id();
                $week_start = $this->input->get('week_start') ?: timesheet_get_week_start();

                $this->db->where('staff_id', $staff_id);
                $this->db->where('week_start_date', $week_start);
                $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

                echo json_encode([
                    'success' => true, 
                    'entries' => $entries,
                    'count' => count($entries),
                    'staff_id' => $staff_id,
                    'week_start' => $week_start
                ]);
                break;

            default:
                echo json_encode(['error' => 'Ação não reconhecida. Use: list_timers, test_unidirectional, fix_status, check_entries']);
        }
    }

    public function view_approval($approval_id)
    {
        if (!has_permission('timesheet', '', 'view')) {
            access_denied('timesheet');
        }

        $approval = $this->timesheet_model->get_approval_details($approval_id);

        if (!$approval) {
            show_404();
        }

        $data['approval'] = $approval;
        $data['entries'] = $this->timesheet_model->get_week_entries_grouped($approval->staff_id, $approval->week_start_date);
        $data['week_dates'] = timesheet_get_week_dates($approval->week_start_date);
        $data['daily_totals'] = $this->timesheet_model->get_week_daily_totals($approval->staff_id, $approval->week_start_date);
        $data['week_total'] = $this->timesheet_model->get_week_total_hours($approval->staff_id, $approval->week_start_date);

        $data['title'] = _l('timesheet_approvals') . ' - ' . $approval->firstname . ' ' . $approval->lastname;

        $this->load->view('timesheet/view_approval', $data);
    }

    /**
     * FUNCIONALIDADE REMOVIDA - VERSÃO 1.4.0
     * Processamento de recálculos pendentes não é mais necessário no modo unidirecional.
     */
}