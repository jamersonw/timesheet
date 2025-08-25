<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Timesheet_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tasks_model');
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
                return false;
            }
    
            $new_entry_status = ($action === 'approved' ? 'approved' : 'draft');
            $this->db->where('staff_id', $approval->staff_id);
            $this->db->where('week_start_date', $approval->week_start_date);
            $this->db->update(db_prefix() . 'timesheet_entries', ['status' => $new_entry_status]);

            if ($action === 'approved') {
                $this->log_approved_hours_to_tasks($approval->staff_id, $approval->week_start_date, $approver_id);
            }
    
            return true;
        }
        
        return false;
    }
    
    /**
     * Logs approved hours to the corresponding Perfex tasks.
     */
    public function log_approved_hours_to_tasks($staff_id, $week_start_date, $approver_id)
    {
        $this->load->helper('staff');

        $this->db->where('staff_id', $staff_id);
        $this->db->where('week_start_date', $week_start_date);
        $entries = $this->db->get(db_prefix() . 'timesheet_entries')->result();

        if (empty($entries)) {
            return;
        }
        
        $week_dates = timesheet_get_week_dates($week_start_date);

        foreach ($entries as $entry) {
            if (!empty($entry->task_id) && $entry->hours > 0) {
                $seconds = $entry->hours * 3600;
                $entry_date_start = date('Y-m-d H:i:s', strtotime($week_dates[$entry->day_of_week - 1]));
                $entry_date_end = date('Y-m-d H:i:s', strtotime($entry_date_start) + $seconds);

                $timer_data = [
                    'timesheet_task_id'  => $entry->task_id,
                    'timesheet_staff_id' => $staff_id,
                    'start_time'         => $entry_date_start,
                    'end_time'           => $entry_date_end,
                    'note'               => 'Horas aprovadas via módulo Timesheet. Aprovado por: ' . get_staff_full_name($approver_id),
                ];

                // Criar timer no quadro de horas do Perfex e obter o ID
                $timer_id = $this->tasks_model->timesheet($timer_data);
                
                if ($timer_id) {
                    // Salvar referência do timer criado na entrada do timesheet
                    $this->db->where('id', $entry->id);
                    $this->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => $timer_id]);
                    
                    log_activity('[Timesheet Sync] Timer ID ' . $timer_id . ' criado e referenciado na entrada ' . $entry->id);
                } else {
                    log_activity('[Timesheet Sync ERROR] Falha ao criar timer para entrada ' . $entry->id);
                }
            }
        }
    }

    /**
     * Recalculates hours from Perfex timers and updates the timesheet entry.
     */
    public function recalculate_and_update_entry($task_id, $staff_id) {
        log_activity('[Timesheet Sync DEBUG] Iniciando recalculo para Tarefa ID: ' . $task_id . ' e Staff ID: ' . $staff_id);

        // Buscar todos os timers para esta tarefa e staff
        $this->db->select('*');
        $this->db->where('task_id', $task_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('end_time IS NOT NULL'); // Apenas timers finalizados
        $core_timers = $this->db->get(db_prefix() . 'taskstimers')->result();

        log_activity('[Timesheet Sync DEBUG] Encontrados ' . count($core_timers) . ' timers para a tarefa ' . $task_id);

        $daily_totals_seconds = [];
        foreach ($core_timers as $timer) {
            // Verificar se start_time é timestamp Unix ou string de data
            $timestamp = is_numeric($timer->start_time) ? $timer->start_time : strtotime($timer->start_time);
            $date = date('Y-m-d', $timestamp);
            
            if (!isset($daily_totals_seconds[$date])) {
                $daily_totals_seconds[$date] = 0;
            }
            
            // Calcular duração do timer
            $end_time = is_numeric($timer->end_time) ? $timer->end_time : strtotime($timer->end_time);
            $start_time = is_numeric($timer->start_time) ? $timer->start_time : strtotime($timer->start_time);
            $duration = $end_time - $start_time;
            
            $daily_totals_seconds[$date] += $duration;
            
            log_activity('[Timesheet Sync DEBUG] Timer ID ' . $timer->id . ' - Data: ' . $date . ' - Duração: ' . $duration . ' segundos');
        }
        
        log_activity('[Timesheet Sync DEBUG] Totais de segundos por dia calculados: ' . json_encode($daily_totals_seconds));

        // Limpar entradas existentes para esta tarefa e staff
        $this->db->where('staff_id', $staff_id);
        $this->db->where('task_id', $task_id);
        $this->db->delete(db_prefix() . 'timesheet_entries');
        log_activity('[Timesheet Sync DEBUG] Entradas antigas removidas para a tarefa ' . $task_id);

        // Criar novas entradas baseadas nos timers
        $this->load->helper('timesheet/timesheet');
        foreach ($daily_totals_seconds as $date => $total_seconds) {
            $total_hours = round($total_seconds / 3600, 2);
            
            if ($total_hours > 0) { // Apenas criar entrada se há horas
                $week_start = timesheet_get_week_start($date);
                $day_of_week = date('N', strtotime($date));

                // Buscar informações do projeto
                $task = $this->tasks_model->get($task_id);
                if ($task) {
                    $data = [
                        'staff_id'        => $staff_id,
                        'project_id'      => $task->rel_id,
                        'task_id'         => $task_id,
                        'week_start_date' => $week_start,
                        'day_of_week'     => $day_of_week,
                        'hours'           => $total_hours,
                        'status'          => 'draft'
                    ];
                    
                    $this->db->insert(db_prefix() . 'timesheet_entries', $data);
                    log_activity('[Timesheet Sync DEBUG] Entrada criada para ' . $date . ' com ' . $total_hours . ' horas');
                }
            }
        }
        
        log_activity('[Timesheet Sync DEBUG] Sincronização finalizada para a tarefa ' . $task_id);
        return true;
    }

    public function get_pending_approvals($manager_id)
    {
        $this->db->select('ta.*, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'timesheet_approvals ta');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ta.staff_id');
        $this->db->where('ta.status', 'pending');
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
}
