<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get week start date (Monday) for a given date
 */
if (!function_exists('timesheet_get_week_start')) {
    function timesheet_get_week_start($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp); // 1 = Monday, 7 = Sunday
        
        // Calculate days to subtract to get to Monday
        $daysToSubtract = $dayOfWeek - 1;
        
        return date('Y-m-d', strtotime("-{$daysToSubtract} days", $timestamp));
    }
}

/**
 * Get week end date (Sunday) for a given date
 */
if (!function_exists('timesheet_get_week_end')) {
    function timesheet_get_week_end($date = null)
    {
        $weekStart = timesheet_get_week_start($date);
        return date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));
    }
}

/**
 * Get week dates array (Monday to Sunday)
 */
if (!function_exists('timesheet_get_week_dates')) {
    function timesheet_get_week_dates($weekStart)
    {
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = date('Y-m-d', strtotime("+{$i} days", strtotime($weekStart)));
        }
        return $dates;
    }
}

/**
 * Format hours for display
 */
if (!function_exists('timesheet_format_hours')) {
    function timesheet_format_hours($hours)
    {
        if (empty($hours) || $hours == 0) {
            return '0.00';
        }
        return number_format((float)$hours, 2);
    }
}

/**
 * Get projects assigned to staff member
 */
if (!function_exists('timesheet_get_staff_projects')) {
    function timesheet_get_staff_projects($staff_id)
    {
        $CI = &get_instance();
        
        $CI->db->select('p.id, p.name');
        $CI->db->from(db_prefix() . 'projects p');
        $CI->db->join(db_prefix() . 'project_members pm', 'pm.project_id = p.id');
        $CI->db->where('pm.staff_id', $staff_id);
        $CI->db->where('p.status', 2); // Active projects only
        $CI->db->order_by('p.name', 'ASC');
        
        return $CI->db->get()->result();
    }
}

/**
 * Get tasks assigned to staff member for a project
 */
if (!function_exists('timesheet_get_staff_project_tasks')) {
    function timesheet_get_staff_project_tasks($staff_id, $project_id)
    {
        $CI = &get_instance();
        
        $CI->db->select('t.id, t.name');
        $CI->db->from(db_prefix() . 'tasks t');
        $CI->db->join(db_prefix() . 'task_assigned ta', 'ta.taskid = t.id');
        $CI->db->where('ta.staffid', $staff_id);
        $CI->db->where('t.rel_id', $project_id);
        $CI->db->where('t.rel_type', 'project');
        $CI->db->where('t.status !=', 5); // Exclude completed tasks (status = 5)
        $CI->db->order_by('t.name', 'ASC');
        
        return $CI->db->get()->result();
    }
}

/**
 * Check if staff can manage project timesheets (is project creator or admin)
 */
if (!function_exists('timesheet_can_manage_project')) {
    function timesheet_can_manage_project($staff_id, $project_id)
    {
        $CI = &get_instance();
        
        // Check if admin
        if (is_admin($staff_id)) {
            return true;
        }
        
        // Check if project creator
        $CI->db->select('addedfrom');
        $CI->db->from(db_prefix() . 'projects');
        $CI->db->where('id', $project_id);
        $project = $CI->db->get()->row();
        
        return $project && $project->addedfrom == $staff_id;
    }
}

/**
 * Format hours for display
 */
if (!function_exists('timesheet_format_hours')) {
    function timesheet_format_hours($hours)
    {
        if (empty($hours) || $hours == 0) {
            return '';
        }
        return str_replace('.', ',', number_format((float)$hours, 2));
    }
}

/**
 * Get timesheet status badge HTML
 */
if (!function_exists('timesheet_status_badge')) {
    function timesheet_status_badge($status)
    {
        $badges = [
            'draft' => '<span class="label label-default">Rascunho</span>',
            'pending' => '<span class="label label-warning">Pendente</span>',
            'approved' => '<span class="label label-success">Aprovado</span>',
            'rejected' => '<span class="label label-danger">Rejeitado</span>',
        ];
        
        return isset($badges[$status]) ? $badges[$status] : '<span class="label label-default">' . $status . '</span>';
    }
}

/**
 * Get timesheet status badge HTML
 */
if (!function_exists('timesheet_status_badge')) {
    function timesheet_status_badge($status)
    {
        $badges = [
            'draft' => '<span class="label label-default">' . _l('timesheet_status_draft') . '</span>',
            'submitted' => '<span class="label label-info">' . _l('timesheet_status_submitted') . '</span>',
            'approved' => '<span class="label label-success">' . _l('timesheet_status_approved') . '</span>',
            'rejected' => '<span class="label label-danger">' . _l('timesheet_status_rejected') . '</span>',
        ];
        
        return isset($badges[$status]) ? $badges[$status] : $status;
    }
}

