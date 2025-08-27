<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Timesheet_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tasks_model');

        // Garantir que o campo perfex_timer_id existe na tabela
        $this->ensure_perfex_timer_id_field();
    }

    /**
     * Verifica e cria o campo perfex_timer_id se não existir
     * Solução para casos onde a migration falhou
     */
    private function ensure_perfex_timer_id_field()
    {
        try {
            if (!$this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
                log_activity('[Timesheet] Campo perfex_timer_id não encontrado, criando automaticamente...');

                // Criar o campo
                $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) NULL AFTER `task_id`');

                // Criar o índice
                $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD INDEX `idx_perfex_timer_id` (`perfex_timer_id`)');

                log_activity('[Timesheet] Campo perfex_timer_id criado com sucesso automaticamente');
            }
        } catch (Exception $e) {
            log_activity('[Timesheet ERROR] Erro ao criar campo perfex_timer_id: ' . $e->getMessage());
        }
    }

    /**
     * Get timesheet entries for a staff member and week, grouped by project/task.
     */
    public function get_week_entries_grouped($staff_id, $week_start_date)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name');
        $this->db->from(db_prefix() . 'timesheet_entries te');
        $this->db->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left');
        $this->db->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left');
        $this->db->where('te.staff_id', $staff_id);
        $this->db->where('te.week_start_date', $week_start_date);
        $entries = $this->db->get()->result();

        $grouped = [];
        foreach ($entries as $entry) {
            $key = $entry->project_id . '_' . ($entry->task_id ?: '0');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'project_id'   => $entry->project_id,
                    'project_name' => $entry->project_name,
                    'task_id'      => $entry->task_id,
                    'task_name'    => $entry->task_name,
                    'days'         => array_fill(1, 7, ['hours' => 0]),
                    'total_hours'  => 0,
                ];
            }
            $grouped[$key]['days'][$entry->day_of_week]['hours'] = $entry->hours;
            $grouped[$key]['total_hours'] += $entry->hours;
        }
        return array_values($grouped);
    }

    /**
     * Função de debug para listar todos os timers do Perfex
     */
    public function debug_list_perfex_timers($limit = 10) {
        try {
            log_activity('[Timesheet Debug] Listando últimos ' . $limit . ' timers do Perfex...');
            
            $this->db->select('*');
            $this->db->from(db_prefix() . 'taskstimers');
            $this->db->order_by('id', 'DESC');
            $this->db->limit($limit);
            $timers = $this->db->get()->result();
            
            log_activity('[Timesheet Debug] Encontrados ' . count($timers) . ' timers');
            
            foreach ($timers as $timer) {
                $start_time = is_numeric($timer->start_time) ? date('Y-m-d H:i:s', $timer->start_time) : $timer->start_time;
                $end_time = $timer->end_time ? (is_numeric($timer->end_time) ? date('Y-m-d H:i:s', $timer->end_time) : $timer->end_time) : 'EM ANDAMENTO';
                
                log_activity('[Timesheet Debug] Timer ID: ' . $timer->id . ' | Task: ' . $timer->task_id . ' | Staff: ' . $timer->staff_id . ' | Início: ' . $start_time . ' | Fim: ' . $end_time);
            }
            
            return $timers;
            
        } catch (Exception $e) {
            log_activity('[Timesheet Debug ERROR] Erro ao listar timers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save or update a single timesheet entry.
     */
    public function save_entry($data)
    {
        $this->db->where('staff_id', $data['staff_id']);
        $this->db->where('project_id', $data['project_id']);
        $this->db->where('task_id', $data['task_id']);
        $this->db->where('week_start_date', $data['week_start_date']);
        $this->db->where('day_of_week', $data['day_of_week']);
        $existing = $this->db->get(db_prefix() . 'timesheet_entries')->row();

        if ($existing) {
            if (empty($data['hours']) || (float)$data['hours'] == 0) {
                return $this->db->delete(db_prefix() . 'timesheet_entries', ['id' => $existing->id]);
            } else {
                $this->db->where('id', $existing->id);
                return $this->db->update(db_prefix() . 'timesheet_entries', ['hours' => $data['hours']]);
            }
        } elseif ((float)$data['hours'] > 0) {
            return $this->db->insert(db_prefix() . 'timesheet_entries', $data);
        }

        return true;
    }

    /**
     * Submit a week's timesheet for approval.
     */
    public function submit_week($staff_id, $week_start_date)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'submitted']);

        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $existing = $this->db->get(db_prefix() . 'timesheet_approvals')->row();

        $approval_data = [
            'staff_id'        => $staff_id,
            'week_start_date' => $week_start_date,
            'status'          => 'pending',
            'submitted_at'    => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('id', $existing->id);
            return $this->db->update(db_prefix() . 'timesheet_approvals', $approval_data);
        } else {
            return $this->db->insert(db_prefix() . 'timesheet_approvals', $approval_data);
        }
    }

    /**
     * Cancel a week's submission, returning it to draft status.
     */
    public function cancel_week_submission($staff_id, $week_start_date)
    {
        $approval = $this->get_week_approval_status($staff_id, $week_start_date);
        if (!$approval || $approval->status !== 'pending') {
            return false;
        }

        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'draft']);

        $this->db->where('id', $approval->id);
        $this->db->delete(db_prefix() . 'timesheet_approvals');

        return true;
    }

    /**
     * Get the approval status for a specific week and staff member.
     */
    public function get_week_approval_status($staff_id, $week_start_date)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        return $this->db->get(db_prefix() . 'timesheet_approvals')->row();
    }

    /**
     * Approve or reject a timesheet submission.
     */
    public function approve_reject_timesheet($approval_id, $action, $approver_id, $reason = null)
    {
        try {
            $data = [
                'status'      => $action,
                'approved_by' => $approver_id,
                'approved_at' => date('Y-m-d H:i:s'),
            ];
            if ($action === 'rejected') {
                $data['rejection_reason'] = $reason;
            }

            $this->db->where('id', $approval_id);
            if ($this->db->update(db_prefix() . 'timesheet_approvals', $data)) {
                $approval = $this->db->get_where(db_prefix() . 'timesheet_approvals', ['id' => $approval_id])->row();

                if (!$approval) {
                    log_activity('[Timesheet Approval] ERRO: Aprovação ID ' . $approval_id . ' não encontrada após atualização');
                    return false;
                }

                $new_entry_status = ($action === 'approved' ? 'approved' : 'draft');
                $this->db->where('staff_id', $approval->staff_id);
                $this->db->where('week_start_date', $approval->week_start_date);
                if (!$this->db->update(db_prefix() . 'timesheet_entries', ['status' => $new_entry_status])) {
                    log_activity('[Timesheet Approval] ERRO: Falha ao atualizar status das entradas para staff ' . $approval->staff_id);
                    return false;
                }

                if ($action === 'approved') {
                    log_activity('[Timesheet Approval] Iniciando sincronização com quadro de horas para approval ID ' . $approval_id);
                    if (!$this->log_approved_hours_to_tasks($approval->staff_id, $approval->week_start_date, $approver_id)) {
                        log_activity('[Timesheet Approval] AVISO: Falha na sincronização com quadro de horas, mas aprovação foi mantida');
                        // Não retornamos false aqui pois a aprovação foi bem sucedida, apenas a sincronização falhou
                    }
                }

                log_activity('[Timesheet Approval] Aprovação processada com sucesso - ID: ' . $approval_id . ', Ação: ' . $action . ', Staff: ' . $approval->staff_id);
                return true;
            } else {
                log_activity('[Timesheet Approval] ERRO: Falha ao atualizar approval ID ' . $approval_id);
                return false;
            }
        } catch (Exception $e) {
            log_activity('[Timesheet Approval] ERRO FATAL: ' . $e->getMessage() . ' - Approval ID: ' . $approval_id);
            return false;
        }
    }

    /**
     * Logs approved hours to the corresponding Perfex tasks.
     */
    public function log_approved_hours_to_tasks($staff_id, $week_start_date, $approver_id)
    {
        try {
            $this->load->helper('staff');

            // Buscar todas as entradas da semana que tenham horas > 0 e task_id válido
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('hours >', 0);
            $this->db->where('task_id IS NOT NULL');
            $this->db->where('task_id !=', '');
            $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

            if (empty($entries)) {
                log_activity('[Timesheet Sync] Nenhuma entrada válida encontrada para staff ' . $staff_id . ' na semana ' . $week_start_date);
                return true;
            }

            log_activity('[Timesheet Sync] Processando ' . count($entries) . ' entradas para staff ' . $staff_id . ' na semana ' . $week_start_date);

            $timers_created = 0;
            $timers_skipped = 0;

            foreach ($entries as $entry) {
                try {
                    // Validar se a tarefa existe
                    $task = $this->db->get_where(db_prefix() . 'tasks', ['id' => $entry->task_id])->row();
                    if (!$task) {
                        log_activity('[Timesheet Sync] AVISO: Tarefa ID ' . $entry->task_id . ' não encontrada. Pulando entrada ' . $entry->id);
                        continue;
                    }

                    // Calcular a data específica do dia da semana
                    $day_offset = $entry->day_of_week - 1; // day_of_week é 1-7, precisamos 0-6
                    $entry_date = date('Y-m-d', strtotime($week_start_date . ' +' . $day_offset . ' days'));

                    // Verificar se a data é válida
                    if (!$entry_date || $entry_date == '1970-01-01') {
                        log_activity('[Timesheet Sync] ERRO: Data inválida calculada para entrada ' . $entry->id . '. Week start: ' . $week_start_date . ', Day offset: ' . $day_offset);
                        continue;
                    }

                    // Definir horários de trabalho (00:00 às X horas baseado nas horas trabalhadas)
                    $start_time = $entry_date . ' 00:00:00';
                    $end_timestamp = strtotime($start_time) + ($entry->hours * 3600);
                    $end_time = date('Y-m-d H:i:s', $end_timestamp);

                    // Verificar se já existe um timer para esta tarefa, staff e data específica
                    $this->db->where('task_id', $entry->task_id);
                    $this->db->where('staff_id', $staff_id);
                    $this->db->where('DATE(FROM_UNIXTIME(start_time))', $entry_date);
                    $existing_timer = $this->db->get(db_prefix() . 'taskstimers')->row();

                    if ($existing_timer) {
                        log_activity('[Timesheet Sync] Timer já existe para tarefa ' . $entry->task_id . ' em ' . $entry_date . ' - Timer ID: ' . $existing_timer->id);

                        // Atualizar referência na entrada do timesheet se não existir
                        if (empty($entry->perfex_timer_id)) {
                            $this->db->where('id', $entry->id);
                            $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $existing_timer->id]);
                        }

                        $timers_skipped++;
                        continue;
                    }

                    // Preparar dados do timer
                    $timer_data = [
                        'task_id'    => $entry->task_id,
                        'staff_id'   => $staff_id,
                        'start_time' => strtotime($start_time),
                        'end_time'   => strtotime($end_time),
                        'note'       => 'Horas aprovadas via módulo Timesheet (' . $entry->hours . 'h em ' . date('d/m/Y', strtotime($entry_date)) . ')',
                    ];

                    log_activity('[Timesheet Sync] Criando timer - Tarefa: ' . $entry->task_id . ', Data: ' . $entry_date . ', Horas: ' . $entry->hours . 'h, Período: ' . date('H:i', strtotime($start_time)) . '-' . date('H:i', strtotime($end_time)));

                    // Criar timer no quadro de horas do Perfex
                    if ($this->db->insert(db_prefix() . 'taskstimers', $timer_data)) {
                        $timer_id = $this->db->insert_id();

                        // Salvar referência do timer criado na entrada do timesheet
                        $this->db->where('id', $entry->id);
                        $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $timer_id]);

                        log_activity('[Timesheet Sync SUCCESS] Timer ID ' . $timer_id . ' criado para entrada ' . $entry->id . ' - Tarefa ' . $entry->task_id . ' em ' . $entry_date . ' (' . $entry->hours . 'h)');
                        $timers_created++;
                    } else {
                        $db_error = $this->db->error();
                        log_activity('[Timesheet Sync ERROR] Falha ao criar timer para entrada ' . $entry->id . ' - Tarefa ' . $entry->task_id . ' em ' . $entry_date . '. Erro DB: ' . $db_error['message']);
                    }

                } catch (Exception $e) {
                    log_activity('[Timesheet Sync ERROR] Erro ao processar entrada ' . $entry->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            log_activity('[Timesheet Sync] Sincronização finalizada para staff ' . $staff_id . ' na semana ' . $week_start_date . ' - Criados: ' . $timers_created . ', Ignorados: ' . $timers_skipped);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Sync FATAL ERROR] Erro fatal na sincronização: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * FUNCIONALIDADE REMOVIDA - VERSÃO 1.4.0
     * Esta função foi removida pois o módulo agora opera apenas no modo unidirecional.
     * O quadro de horas é apenas para visualização.
     */

    /**
     * Cria timer no Perfex baseado em entrada do timesheet
     * Usado quando usuário cria/edita horas diretamente no timesheet
     */
    public function create_perfex_timer_from_timesheet($entry_id)
    {
        try {
            // Buscar entrada do timesheet
            $entry = $this->db->get_where(db_prefix() . 'timesheet_entries', ['id' => $entry_id])->row();
            if (!$entry || $entry->hours <= 0) {
                return false;
            }

            // Calcular data específica do dia da semana
            $day_offset = $entry->day_of_week - 1;
            $entry_date = date('Y-m-d', strtotime($entry->week_start_date . ' +' . $day_offset . ' days'));

            // Definir horários: 00:00:00 até 00:00:00 + horas trabalhadas
            $start_time = $entry_date . ' 00:00:00';
            $end_timestamp = strtotime($start_time) + ($entry->hours * 3600);
            $end_time = date('Y-m-d H:i:s', $end_timestamp);

            // Verificar se já existe timer para este dia/tarefa/staff
            $this->db->where('task_id', $entry->task_id);
            $this->db->where('staff_id', $entry->staff_id);
            $this->db->where('DATE(FROM_UNIXTIME(start_time))', $entry_date);
            $existing_timer = $this->db->get(db_prefix() . 'taskstimers')->row();

            if ($existing_timer) {
                // Atualizar timer existente
                $timer_data = [
                    'start_time' => strtotime($start_time),
                    'end_time'   => strtotime($end_time),
                    'note'       => 'Atualizado via Timesheet (' . $entry->hours . 'h em ' . date('d/m/Y', strtotime($entry_date)) . ')'
                ];

                $this->db->where('id', $existing_timer->id);
                if ($this->db->update(db_prefix() . 'taskstimers', $timer_data)) {
                    // Atualizar referência na entrada
                    $this->db->where('id', $entry->id);
                    $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $existing_timer->id]);
                    
                    log_activity('[Timesheet→Perfex] Timer atualizado ID ' . $existing_timer->id . ' para ' . $entry->hours . 'h');
                    return $existing_timer->id;
                }
            } else {
                // Criar novo timer
                $timer_data = [
                    'task_id'    => $entry->task_id,
                    'staff_id'   => $entry->staff_id,
                    'start_time' => strtotime($start_time),
                    'end_time'   => strtotime($end_time),
                    'note'       => 'Criado via Timesheet (' . $entry->hours . 'h em ' . date('d/m/Y', strtotime($entry_date)) . ')'
                ];

                if ($this->db->insert(db_prefix() . 'taskstimers', $timer_data)) {
                    $timer_id = $this->db->insert_id();
                    
                    // Salvar referência na entrada
                    $this->db->where('id', $entry->id);
                    $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $timer_id]);
                    
                    log_activity('[Timesheet→Perfex] Timer criado ID ' . $timer_id . ' para ' . $entry->hours . 'h');
                    return $timer_id;
                }
            }

            return false;

        } catch (Exception $e) {
            log_activity('[Timesheet→Perfex ERROR] Erro ao criar timer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * FUNCIONALIDADE REMOVIDA - VERSÃO 1.4.0
     * Esta função foi removida pois o módulo agora opera apenas no modo unidirecional.
     * Alterações no quadro de horas não sincronizam de volta para o timesheet.
     */

    /**
     * FUNCIONALIDADE REMOVIDA - VERSÃO 1.4.0
     * Esta função foi removida pois o módulo agora opera apenas no modo unidirecional.
     * O quadro de horas é apenas para visualização.
     */

    public function get_pending_approvals($manager_id)
    {
        $this->db->select('ta.*, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'timesheet_approvals ta');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
        $this->db->where('ta.status', 'pending');
        $this->db->order_by('ta.submitted_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_all_approvals($manager_id, $limit = 50)
    {
        $this->db->select('ta.*, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'timesheet_approvals ta');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
        $this->db->order_by('ta.submitted_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_week_total_hours($staff_id, $week_start_date)
    {
        $this->db->select_sum('hours');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $result = $this->db->get(db_prefix() . 'timesheet_entries')->row();
        return $result ? $result->hours : 0;
    }

    public function get_week_daily_totals($staff_id, $week_start_date)
    {
        $this->db->select('day_of_week, SUM(hours) as total_hours');
        $this->db->from(db_prefix() . 'timesheet_entries');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $this->db->group_by('day_of_week');
        $results = $this->db->get()->result();
        $totals = array_fill(1, 7, 0);
        foreach ($results as $result) {
            $totals[$result->day_of_week] = $result->total_hours;
        }
        return $totals;
    }

    public function get_approval_details($approval_id)
    {
        $this->db->select('ta.*, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'timesheet_approvals ta');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
        $this->db->where('ta.id', $approval_id);
        return $this->db->get()->row();
    }

    /**
     * Check if a staff member can edit a specific week
     */
    public function can_edit_week($staff_id, $week_start_date)
    {
        $approval = $this->get_week_approval_status($staff_id, $week_start_date);

        // Pode editar se não há aprovação ou se foi rejeitado
        return !$approval || $approval->status == 'rejected';
    }

    /**
     * Check if a staff member can submit a specific week
     * Versão mais permissiva para submissão
     */
    public function can_submit_week($staff_id, $week_start_date)
    {
        $approval = $this->get_week_approval_status($staff_id, $week_start_date);

        // Pode submeter se não há aprovação, se foi rejeitado, ou se está em draft
        return !$approval || in_array($approval->status, ['rejected', 'draft']);
    }

    /**
     * Get all approvals for a specific week (all staff members)
     */
    public function get_weekly_approvals($week_start_date)
    {
        try {
            log_activity('[Timesheet DEBUG] get_weekly_approvals() iniciado para semana: ' . $week_start_date);
            
            $this->db->select('ta.*, s.firstname, s.lastname, s.email');
            log_activity('[Timesheet DEBUG] SELECT definido');
            
            $this->db->from(db_prefix() . 'timesheet_approvals ta');
            log_activity('[Timesheet DEBUG] FROM definido: ' . db_prefix() . 'timesheet_approvals');
            
            $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
            log_activity('[Timesheet DEBUG] JOIN definido com staff');
            
            $this->db->where('ta.week_start_date', $week_start_date);
            log_activity('[Timesheet DEBUG] WHERE definido: week_start_date = ' . $week_start_date);
            
            $this->db->order_by('ta.status', 'ASC'); // pending first
            $this->db->order_by('s.firstname', 'ASC');
            log_activity('[Timesheet DEBUG] ORDER BY definido');
            
            // Log da query antes de executar
            $query_string = $this->db->get_compiled_select();
            log_activity('[Timesheet DEBUG] Query compilada: ' . $query_string);
            
            // Reset query builder e executar novamente
            $this->db->select('ta.*, s.firstname, s.lastname, s.email');
            $this->db->from(db_prefix() . 'timesheet_approvals ta');
            $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
            $this->db->where('ta.week_start_date', $week_start_date);
            $this->db->order_by('ta.status', 'ASC');
            $this->db->order_by('s.firstname', 'ASC');
            
            log_activity('[Timesheet DEBUG] Executando query...');
            $result = $this->db->get()->result();
            log_activity('[Timesheet DEBUG] Query executada com sucesso. Registros encontrados: ' . count($result));
            
            if (!$result) {
                log_activity('[Timesheet DEBUG] Nenhum resultado encontrado, retornando array vazio');
                return [];
            }
            
            log_activity('[Timesheet DEBUG] Retornando ' . count($result) . ' aprovações');
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error in get_weekly_approvals: ' . $e->getMessage());
            log_activity('[Timesheet ERROR] Erro em get_weekly_approvals: ' . $e->getMessage());
            log_activity('[Timesheet ERROR] Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Cancel an approval and revert to draft status
     */
    public function cancel_approval($approval_id, $manager_id)
    {
        try {
            // Get approval details
            $approval = $this->db->get_where(db_prefix() . 'timesheet_approvals', ['id' => $approval_id])->row();
            
            if (!$approval || $approval->status !== 'approved') {
                log_activity('[Timesheet Cancel] Tentativa de cancelar aprovação inválida - ID: ' . $approval_id);
                return false;
            }

            // Remove timers created from this approval
            $this->remove_approved_timers($approval->staff_id, $approval->week_start_date);

            // Update entries back to draft
            $this->db->where('staff_id', $approval->staff_id);
            $this->db->where('week_start_date', $approval->week_start_date);
            $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'draft']);

            // Delete the approval record
            $this->db->where('id', $approval_id);
            $this->db->delete(db_prefix() . 'timesheet_approvals');

            // Send notification to staff member
            $this->load->model('notifications_model');
            add_notification([
                'description' => 'Sua aprovação de timesheet foi cancelada pelo gerente. Por favor, revise e reenvie.',
                'tousers' => [$approval->staff_id],
                'link' => 'timesheet/index?week=' . $approval->week_start_date,
                'additional_data' => serialize([
                    'week_start' => $approval->week_start_date,
                    'cancelled_by' => $manager_id
                ])
            ]);

            log_activity('[Timesheet Cancel] Aprovação cancelada - ID: ' . $approval_id . ' por manager: ' . $manager_id);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Cancel ERROR] Erro ao cancelar aprovação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove timers created from approved timesheet
     */
    private function remove_approved_timers($staff_id, $week_start_date)
    {
        try {
            // Get all entries from this week that have timer references
            $this->db->select('perfex_timer_id');
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('perfex_timer_id IS NOT NULL');
            $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

            $removed_count = 0;
            foreach ($entries as $entry) {
                // Remove the timer from Perfex
                $this->db->where('id', $entry->perfex_timer_id);
                if ($this->db->delete(db_prefix() . 'taskstimers')) {
                    $removed_count++;
                    log_activity('[Timesheet Cancel] Timer removido ID: ' . $entry->perfex_timer_id);
                }
            }

            // Clear timer references from timesheet entries
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => NULL]);

            log_activity('[Timesheet Cancel] Removidos ' . $removed_count . ' timers da semana ' . $week_start_date);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Cancel ERROR] Erro ao remover timers: ' . $e->getMessage());
            return false;
        }
    }
}