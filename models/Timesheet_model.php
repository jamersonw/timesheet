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
     * VERSÃO 2.0: Cria uma aprovação separada para cada tarefa
     */
    public function submit_week($staff_id, $week_start_date)
    {
        try {
            // Atualizar status das entradas para 'submitted'
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'submitted']);

            // Buscar todas as tarefas únicas da semana com horas > 0
            $this->db->select('project_id, task_id');
            $this->db->distinct();
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('hours >', 0);
            $this->db->where('task_id IS NOT NULL');
            $tasks = $this->db->get(db_prefix() . 'timesheet_entries')->result();

            if (empty($tasks)) {
                log_activity('[Timesheet Submit] Nenhuma tarefa com horas encontrada para staff ' . $staff_id . ' na semana ' . $week_start_date);
                return false;
            }

            $approvals_created = 0;
            $submitted_at = date('Y-m-d H:i:s');

            foreach ($tasks as $task) {
                $approval_data = [
                    'staff_id'        => $staff_id,
                    'project_id'      => $task->project_id,
                    'task_id'         => $task->task_id,
                    'week_start_date' => $week_start_date,
                    'status'          => 'pending',
                    'submitted_at'    => $submitted_at,
                ];

                // Usar INSERT ... ON DUPLICATE KEY UPDATE para lidar com constraint única
                $query = "INSERT INTO `" . db_prefix() . "timesheet_approvals` 
                         (`staff_id`, `project_id`, `task_id`, `week_start_date`, `status`, `submitted_at`) 
                         VALUES (?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         `status` = VALUES(`status`), 
                         `submitted_at` = VALUES(`submitted_at`)";

                try {
                    if ($this->db->query($query, [
                        $staff_id,
                        $task->project_id,
                        $task->task_id,
                        $week_start_date,
                        'pending',
                        $submitted_at
                    ])) {
                        $approvals_created++;
                        log_activity('[Timesheet Submit] Aprovação processada - Task ID: ' . $task->task_id . ', Staff: ' . $staff_id);
                    }
                } catch (Exception $e) {
                    log_activity('[Timesheet Submit ERROR] Erro ao processar tarefa ' . $task->task_id . ': ' . $e->getMessage());
                    continue;
                }
            }

            log_activity('[Timesheet Submit] ' . $approvals_created . ' aprovações criadas/atualizadas para staff ' . $staff_id . ' na semana ' . $week_start_date);
            return $approvals_created > 0;

        } catch (Exception $e) {
            log_activity('[Timesheet Submit ERROR] Erro ao submeter semana: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a week's submission, returning it to draft status.
     * VERSÃO 2.0: Cancela TODAS as aprovações da semana
     */
    public function cancel_week_submission($staff_id, $week_start_date)
    {
        try {
            // Buscar todas as aprovações pendentes da semana
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('status', 'pending');
            $approvals = $this->db->get(db_prefix() . 'timesheet_approvals')->result();

            if (empty($approvals)) {
                log_activity('[Timesheet Cancel] Nenhuma aprovação pendente encontrada para cancelar - Staff: ' . $staff_id . ', Semana: ' . $week_start_date);
                return false;
            }

            // Atualizar status das entradas para 'draft'
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'draft']);

            // Deletar todas as aprovações pendentes da semana
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('status', 'pending');
            $this->db->delete(db_prefix() . 'timesheet_approvals');

            log_activity('[Timesheet Cancel] Canceladas ' . count($approvals) . ' aprovações para staff ' . $staff_id . ' na semana ' . $week_start_date);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Cancel ERROR] Erro ao cancelar submissão: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the approval status for a specific week and staff member.
     * VERSÃO 2.0: Retorna status consolidado de todas as tarefas da semana
     */
    public function get_week_approval_status($staff_id, $week_start_date)
    {
        // Buscar todas as aprovações da semana
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $approvals = $this->db->get(db_prefix() . 'timesheet_approvals')->result();

        if (empty($approvals)) {
            return null; // Nenhuma aprovação encontrada
        }

        // Contar status diferentes
        $pending_count = 0;
        $approved_count = 0;
        $rejected_count = 0;
        $latest_submission = null;
        $rejection_reasons = [];

        foreach ($approvals as $approval) {
            switch ($approval->status) {
                case 'pending':
                    $pending_count++;
                    break;
                case 'approved':
                    $approved_count++;
                    break;
                case 'rejected':
                    $rejected_count++;
                    if (!empty($approval->rejection_reason)) {
                        $rejection_reasons[] = $approval->rejection_reason;
                    }
                    break;
            }

            // Manter o timestamp da submissão mais recente
            if (!$latest_submission || $approval->submitted_at > $latest_submission) {
                $latest_submission = $approval->submitted_at;
            }
        }

        // Determinar status consolidado
        $consolidated_status = 'pending'; // Default

        if ($rejected_count > 0) {
            // Se qualquer tarefa foi rejeitada, status geral é rejeitado
            $consolidated_status = 'rejected';
        } elseif ($pending_count > 0) {
            // Se há tarefas pendentes, status geral é pendente
            $consolidated_status = 'pending';
        } elseif ($approved_count > 0 && $pending_count == 0 && $rejected_count == 0) {
            // Se todas estão aprovadas, status geral é aprovado
            $consolidated_status = 'approved';
        }

        // Retornar objeto simulando a estrutura antiga
        return (object) [
            'id' => $approvals[0]->id, // ID da primeira aprovação (compatibilidade)
            'staff_id' => $staff_id,
            'week_start_date' => $week_start_date,
            'status' => $consolidated_status,
            'submitted_at' => $latest_submission,
            'rejection_reason' => implode('; ', array_unique($rejection_reasons)),
            // Novos campos para informações detalhadas
            'total_tasks' => count($approvals),
            'pending_tasks' => $pending_count,
            'approved_tasks' => $approved_count,
            'rejected_tasks' => $rejected_count
        ];
    }

    /**
     * Approve or reject a timesheet submission.
     * VERSÃO 2.0: Processa aprovação/rejeição de tarefa individual
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

            // Atualizar a aprovação específica da tarefa
            $this->db->where('id', $approval_id);
            if (!$this->db->update(db_prefix() . 'timesheet_approvals', $data)) {
                log_activity('[Timesheet Approval] ERRO: Falha ao atualizar approval ID ' . $approval_id);
                return false;
            }

            // Buscar informações da aprovação
            $approval = $this->db->get_where(db_prefix() . 'timesheet_approvals', ['id' => $approval_id])->row();
            if (!$approval) {
                log_activity('[Timesheet Approval] ERRO: Aprovação ID ' . $approval_id . ' não encontrada após atualização');
                return false;
            }

            // Atualizar status das entradas APENAS desta tarefa específica
            $this->db->where('staff_id', $approval->staff_id);
            $this->db->where('project_id', $approval->project_id);
            $this->db->where('task_id', $approval->task_id);
            $this->db->where('week_start_date', $approval->week_start_date);

            $new_entry_status = ($action === 'approved' ? 'approved' : 'draft');
            if (!$this->db->update(db_prefix() . 'timesheet_entries', ['status' => $new_entry_status])) {
                log_activity('[Timesheet Approval] ERRO: Falha ao atualizar status das entradas para tarefa ' . $approval->task_id);
                return false;
            }

            // Se aprovado, sincronizar apenas as horas desta tarefa
            if ($action === 'approved') {
                log_activity('[Timesheet Approval] Iniciando sincronização da tarefa ' . $approval->task_id . ' para approval ID ' . $approval_id);
                if (!$this->log_approved_task_hours($approval->staff_id, $approval->week_start_date, $approval->task_id, $approver_id)) {
                    log_activity('[Timesheet Approval] AVISO: Falha na sincronização da tarefa, mas aprovação foi mantida');
                }
            }

            log_activity('[Timesheet Approval] Aprovação processada com sucesso - ID: ' . $approval_id . ', Ação: ' . $action . ', Tarefa: ' . $approval->task_id . ', Staff: ' . $approval->staff_id);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Approval] ERRO FATAL: ' . $e->getMessage() . ' - Approval ID: ' . $approval_id);
            return false;
        }
    }

    /**
     * Logs approved hours of a specific task to Perfex.
     * VERSÃO 2.0: Sincroniza apenas uma tarefa específica
     */
    public function log_approved_task_hours($staff_id, $week_start_date, $task_id, $approver_id)
    {
        try {
            $this->load->helper('staff');

            // Buscar entradas da tarefa específica que tenham horas > 0
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('task_id', $task_id);
            $this->db->where('hours >', 0);
            $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

            if (empty($entries)) {
                log_activity('[Timesheet Sync Task] Nenhuma entrada válida encontrada para tarefa ' . $task_id);
                return true;
            }

            log_activity('[Timesheet Sync Task] Processando ' . count($entries) . ' entradas da tarefa ' . $task_id);

            $timers_created = 0;
            $timers_skipped = 0;

            foreach ($entries as $entry) {
                try {
                    // Validar se a tarefa existe
                    $task = $this->db->get_where(db_prefix() . 'tasks', ['id' => $entry->task_id])->row();
                    if (!$task) {
                        log_activity('[Timesheet Sync Task] AVISO: Tarefa ID ' . $entry->task_id . ' não encontrada');
                        continue;
                    }

                    // Calcular data específica do dia da semana
                    $day_offset = $entry->day_of_week - 1;
                    $entry_date = date('Y-m-d', strtotime($week_start_date . ' +' . $day_offset . ' days'));

                    if (!$entry_date || $entry_date == '1970-01-01') {
                        log_activity('[Timesheet Sync Task] ERRO: Data inválida para entrada ' . $entry->id);
                        continue;
                    }

                    // Definir horários de trabalho
                    $start_time = $entry_date . ' 00:00:00';
                    $end_timestamp = strtotime($start_time) + ($entry->hours * 3600);
                    $end_time = date('Y-m-d H:i:s', $end_timestamp);

                    // Verificar se já existe timer para este dia/tarefa/staff
                    $this->db->where('task_id', $entry->task_id);
                    $this->db->where('staff_id', $staff_id);
                    $this->db->where('DATE(FROM_UNIXTIME(start_time))', $entry_date);
                    $existing_timer = $this->db->get(db_prefix() . 'taskstimers')->row();

                    if ($existing_timer) {
                        if (empty($entry->perfex_timer_id)) {
                            $this->db->where('id', $entry->id);
                            $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $existing_timer->id]);
                        }
                        $timers_skipped++;
                        continue;
                    }

                    // Criar novo timer
                    $timer_data = [
                        'task_id'    => $entry->task_id,
                        'staff_id'   => $staff_id,
                        'start_time' => strtotime($start_time),
                        'end_time'   => strtotime($end_time),
                        'note'       => 'Horas aprovadas via Timesheet (' . $entry->hours . 'h em ' . date('d/m/Y', strtotime($entry_date)) . ')',
                    ];

                    if ($this->db->insert(db_prefix() . 'taskstimers', $timer_data)) {
                        $timer_id = $this->db->insert_id();

                        // Salvar referência
                        $this->db->where('id', $entry->id);
                        $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $timer_id]);

                        log_activity('[Timesheet Sync Task SUCCESS] Timer ID ' . $timer_id . ' criado para tarefa ' . $task_id);
                        $timers_created++;
                    } else {
                        $db_error = $this->db->error();
                        log_activity('[Timesheet Sync Task ERROR] Falha ao criar timer: ' . $db_error['message']);
                    }

                } catch (Exception $e) {
                    log_activity('[Timesheet Sync Task ERROR] Erro ao processar entrada ' . $entry->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            log_activity('[Timesheet Sync Task] Tarefa ' . $task_id . ' finalizada - Criados: ' . $timers_created . ', Ignorados: ' . $timers_skipped);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Sync Task FATAL ERROR] Erro fatal: ' . $e->getMessage());
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
        $this->db->select('ta.*, s.firstname, s.lastname, s.email, p.name as project_name, t.name as task_name');
        $this->db->from(db_prefix() . 'timesheet_approvals ta');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
        $this->db->join(db_prefix() . 'projects p', 'p.id = ta.project_id', 'left');
        $this->db->join(db_prefix() . 'tasks t', 't.id = ta.task_id', 'left');
        $this->db->where('ta.status', 'pending');
        $this->db->order_by('ta.submitted_at', 'DESC');
        $this->db->order_by('s.firstname', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get ALL approvals for a specific week (pending + approved)
     * Retorna aprovações agrupadas por funcionário, mostrando pending e approved
     */
    public function get_weekly_all_approvals($week_start_date = null) {
        try {
            if (!$week_start_date) {
                $week_start_date = date('Y-m-d', strtotime('monday this week'));
            }

            log_activity('[Weekly Model Debug] Iniciando busca de aprovações semanais para semana: ' . $week_start_date);

            // Buscar diretamente os funcionários que têm aprovações para a semana específica
            // Vou usar uma abordagem mais explícita para evitar problemas de sintaxe
            $approvals_table = db_prefix() . 'timesheet_approvals';
            $staff_table = db_prefix() . 'staff';
            
            $this->db->select("DISTINCT ta.staff_id, s.firstname, s.lastname, s.email, ta.week_start_date");
            $this->db->from("$approvals_table ta");
            $this->db->join("$staff_table s", 's.staffid = ta.staff_id');
            $this->db->where('ta.week_start_date', $week_start_date);
            $this->db->where_in('ta.status', ['pending', 'approved']); // Mostrar pending e approved
            $this->db->order_by('s.firstname ASC, s.lastname ASC');
            
            // Debug: mostrar query compilada ANTES de executar
            $compiled_query = $this->db->get_compiled_select();
            log_activity('[Weekly Model Debug] Query SQL compilada: ' . $compiled_query);
            
            // Reset e executar query
            $this->db->select("DISTINCT ta.staff_id, s.firstname, s.lastname, s.email, ta.week_start_date");
            $this->db->from("$approvals_table ta");
            $this->db->join("$staff_table s", 's.staffid = ta.staff_id');
            $this->db->where('ta.week_start_date', $week_start_date);
            $this->db->where_in('ta.status', ['pending', 'approved']);
            $this->db->order_by('s.firstname ASC, s.lastname ASC');
            
            $staff_with_approvals = $this->db->get()->result();

            // Verificar se houve erro na query
            $db_error = $this->db->error();
            if ($db_error['code'] !== 0) {
                log_activity('[Weekly Model Debug ERROR] Erro na query de funcionários: ' . $db_error['message']);
                log_activity('[Weekly Model Debug ERROR] Código do erro: ' . $db_error['code']);
                log_activity('[Weekly Model Debug ERROR] Query que causou o erro: ' . $this->db->last_query());
                return [];
            }

            log_activity('[Weekly Model Debug] Funcionários com aprovações encontrados: ' . count($staff_with_approvals));
            log_activity('[Weekly Model Debug] Query executada com sucesso: ' . $this->db->last_query());

            $result = [];

            foreach ($staff_with_approvals as $staff) {
                // Buscar aprovações desta semana para este funcionário (pending e approved)
                $this->db->select('ta.*, t.name as task_name, p.name as project_name');
                $this->db->from(db_prefix() . 'timesheet_approvals ta');
                $this->db->join(db_prefix() . 'tasks t', 'ta.task_id = t.id', 'left');
                $this->db->join(db_prefix() . 'projects p', 't.rel_id = p.id AND t.rel_type = "project"', 'left');
                $this->db->where('ta.staff_id', $staff->staff_id);
                $this->db->where('ta.week_start_date', $week_start_date);
                $this->db->where_in('ta.status', ['pending', 'approved']);
                $this->db->order_by('ta.status ASC, ta.submitted_at DESC'); // Pending primeiro
                
                log_activity('[Weekly Model Debug] Query aprovações: ' . $this->db->get_compiled_select());
                $task_approvals = $this->db->get()->result();

                if ($this->db->error()['code'] !== 0) {
                    $db_error = $this->db->error();
                    log_activity('[Weekly Model Debug ERROR] Erro na query de aprovações: ' . $db_error['message']);
                    continue;
                }
                log_activity('[Weekly Model Debug] Aprovações para ' . $staff->firstname . ' ' . $staff->lastname . ': ' . count($task_approvals));

                if (!empty($task_approvals)) {
                    // Calcular estatísticas consolidadas
                    $pending_count = 0;
                    $approved_count = 0;
                    $latest_submission = null;

                    foreach ($task_approvals as $approval) {
                        if ($approval->status == 'pending') $pending_count++;
                        if ($approval->status == 'approved') $approved_count++;
                        
                        if (!$latest_submission || $approval->submitted_at > $latest_submission) {
                            $latest_submission = $approval->submitted_at;
                        }
                    }

                    // Determinar status consolidado
                    $consolidated_status = $pending_count > 0 ? 'pending' : 'approved';

                    // Criar objeto único representando o funcionário (formato compatível com a view)
                    $staff_approval = (object) [
                        'id' => $task_approvals[0]->id, // ID da primeira aprovação
                        'staff_id' => $staff->staff_id,
                        'firstname' => $staff->firstname,
                        'lastname' => $staff->lastname,
                        'email' => $staff->email,
                        'week_start_date' => $week_start_date,
                        'status' => $consolidated_status,
                        'submitted_at' => $latest_submission,
                        'total_tasks' => count($task_approvals),
                        'pending_tasks' => $pending_count,
                        'approved_tasks' => $approved_count,
                        'task_details' => $task_approvals // Para debug/detalhamento
                    ];

                    $result[] = $staff_approval;

                    log_activity('[Weekly Model Debug] Staff processado: ' . $staff->firstname . ' ' . $staff->lastname . 
                                ' - Status: ' . $consolidated_status . 
                                ' - Total: ' . count($task_approvals) . 
                                ' - Pending: ' . $pending_count . 
                                ' - Approved: ' . $approved_count);
                }
            }

            log_activity('[Weekly Model Debug] Total de aprovações semanais retornadas: ' . count($result));
            return $result;

        } catch (Exception $e) {
            log_activity('[Weekly Model Debug ERROR] Erro fatal: ' . $e->getMessage());
            log_activity('[Weekly Model Debug ERROR] Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    public function get_week_total_hours($staff_id, $week_start_date)
    {
        $this->db->select_sum('hours');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $result = $this->db->get(db_prefix() . 'timesheet_entries')->row();
        return $result ? $result->hours : 0;
    }

    /**
     * Get total hours for a specific task in a week
     * VERSÃO 2.0: Soma horas de uma tarefa específica
     */
    public function get_task_total_hours($staff_id, $week_start_date, $task_id)
    {
        $this->db->select_sum('hours');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $this->db->where('task_id', $task_id);
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
     * VERSÃO 2.0: Pode editar se TODAS as tarefas estão em status editável
     */
    public function can_edit_week($staff_id, $week_start_date)
    {
        $approval_status = $this->get_week_approval_status($staff_id, $week_start_date);

        // Pode editar se não há aprovação ou se status consolidado é rejeitado
        // Também pode editar se há apenas aprovações rejeitadas (para reenvio)
        return !$approval_status || 
               $approval_status->status == 'rejected' || 
               ($approval_status->approved_tasks == 0 && $approval_status->pending_tasks == 0);
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
     * Cancel an approved timesheet, returning it to draft status
     */
    public function cancel_approval($approval_id, $approver_id)
    {
        try {
            // Buscar aprovação
            $approval = $this->db->get_where(db_prefix() . 'timesheet_approvals', ['id' => $approval_id])->row();
            if (!$approval || $approval->status !== 'approved') {
                log_activity('[Timesheet Cancel] Aprovação ID ' . $approval_id . ' não encontrada ou não está aprovada');
                return false;
            }

            // Remover timers criados no Perfex
            $this->remove_perfex_timers_for_week($approval->staff_id, $approval->week_start_date);

            // Remover completamente o registro da tabela de aprovações (como se nunca tivesse sido enviado)
            $this->db->where('id', $approval_id);
            if (!$this->db->delete(db_prefix() . 'timesheet_approvals')) {
                log_activity('[Timesheet Cancel] ERRO: Falha ao remover aprovação ID ' . $approval_id);
                return false;
            }

            // Atualizar status das entradas para draft
            $this->db->where('staff_id', $approval->staff_id);
            $this->db->where('week_start_date', $approval->week_start_date);
            if (!$this->db->update(db_prefix() . 'timesheet_entries', ['status' => 'draft'])) {
                log_activity('[Timesheet Cancel] ERRO: Falha ao atualizar entradas para staff ' . $approval->staff_id);
                return false;
            }

            log_activity('[Timesheet Cancel] Aprovação cancelada com sucesso - ID: ' . $approval_id . ' por usuário: ' . $approver_id);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Cancel] ERRO FATAL: ' . $e->getMessage() . ' - Approval ID: ' . $approval_id);
            return false;
        }
    }

    /**
     * Remove all Perfex timers created for a specific week
     */
    private function remove_perfex_timers_for_week($staff_id, $week_start_date)
    {
        try {
            // Buscar todas as entradas da semana que possuem timer_id
            $this->db->select('id, perfex_timer_id');
            $this->db->where('staff_id', $staff_id);
            $this->db->where('week_start_date', $week_start_date);
            $this->db->where('perfex_timer_id IS NOT NULL');
            $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

            $timers_removed = 0;
            foreach ($entries as $entry) {
                // Remover timer do Perfex
                $this->db->where('id', $entry->perfex_timer_id);
                if ($this->db->delete(db_prefix() . 'taskstimers')) {
                    $timers_removed++;
                    log_activity('[Timesheet Cancel] Timer removido: ' . $entry->perfex_timer_id);
                }

                // Limpar referência na entrada
                $this->db->where('id', $entry->id);
                $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => null]);
            }

            log_activity('[Timesheet Cancel] Removidos ' . $timers_removed . ' timers do Perfex para semana ' . $week_start_date);
            return true;

        } catch (Exception $e) {
            log_activity('[Timesheet Cancel] ERRO ao remover timers: ' . $e->getMessage());
            return false;
        }
    }
}