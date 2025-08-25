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
        $data['can_edit'] = !$approval_status || $approval_status->status == 'rejected';
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
            'status'          => 'draft' // Campo restaurado para segurança
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
        if (!has_permission('timesheet', '', 'view')) {
            access_denied('timesheet');
        }
        if (!is_admin() && !timesheet_can_manage_any_project(get_staff_user_id())) {
            access_denied('timesheet');
        }

        $data['pending_approvals'] = $this->timesheet_model->get_pending_approvals(get_staff_user_id());
        $data['title'] = _l('timesheet_manage');
        
        $this->load->view('timesheet/manage', $data);
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
     * Approve or reject timesheet
     */
    public function approve_reject()
    {
        // ================== INÍCIO DO CÓDIGO DE DEPURAÇÃO FORÇADA ==================
        // Ativa a exibição de todos os erros PHP diretamente na saída.
        // ATENÇÃO: Remova ou comente este bloco em um ambiente de produção!
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        // =================== FIM DO CÓDIGO DE DEPURAÇÃO FORÇADA ====================

        try {
            if (!has_permission('timesheet', '', 'edit')) {
                // Usamos 'throw new Exception' para que o erro seja pego pelo nosso 'catch'
                throw new Exception('Access denied');
            }

            $approval_id = $this->input->post('approval_id');
            $action = $this->input->post('action');
            $reason = $this->input->post('reason');

            log_activity('[Timesheet APPROVAL] Início do processo. Ação: ' . $action . '. ID de Aprovação: ' . $approval_id);

            $result = $this->timesheet_model->approve_reject_timesheet($approval_id, $action, get_staff_user_id(), $reason);

            if ($result) {
                $message = $action == 'approved' ? _l('timesheet_approved_successfully') : _l('timesheet_rejected_successfully');
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                throw new Exception('O método approve_reject_timesheet no Model retornou false.');
            }

        } catch (Throwable $e) {
            // Se qualquer erro (Exception ou Error) ocorrer no bloco 'try', ele será capturado aqui.
            
            // Registra o erro no log de atividades para garantir que tenhamos um registro.
            log_activity('[Timesheet FATAL ERROR] Erro capturado na função approve_reject: ' . $e->getMessage() . ' no arquivo ' . $e->getFile() . ' na linha ' . $e->getLine());

            // Garante que a resposta seja JSON para o javascript.
            header('Content-Type: application/json');
            
            // Envia uma resposta de erro detalhada para o navegador.
            echo json_encode([
                'success' => false,
                'message' => 'Ocorreu um erro fatal no servidor.',
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString() // Fornece o "caminho" do erro
                ]
            ]);
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
}