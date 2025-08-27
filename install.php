<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Create timesheet_entries table
if (!$CI->db->table_exists(db_prefix() . 'timesheet_entries')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheet_entries` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `project_id` int(11) NOT NULL,
        `task_id` int(11) DEFAULT NULL,
        `week_start_date` date NOT NULL,
        `day_of_week` tinyint(1) NOT NULL COMMENT '1=Monday, 7=Sunday',
        `hours` decimal(5,2) NOT NULL DEFAULT '0.00',
        `description` text,
        `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `project_id` (`project_id`),
        KEY `task_id` (`task_id`),
        KEY `week_start_date` (`week_start_date`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create timesheet_approvals table
if (!$CI->db->table_exists(db_prefix() . 'timesheet_approvals')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheet_approvals` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `week_start_date` date NOT NULL,
        `status` enum('pending','approved','rejected') DEFAULT 'pending',
        `approved_by` int(11) DEFAULT NULL,
        `approved_at` datetime DEFAULT NULL,
        `rejection_reason` text,
        `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `week_start_date` (`week_start_date`),
        KEY `status` (`status`),
        KEY `approved_by` (`approved_by`),
        UNIQUE KEY `unique_staff_week` (`staff_id`, `week_start_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Add default options
if (!get_option('timesheet_default_hours_per_day')) {
    add_option('timesheet_default_hours_per_day', '8', 1);
}

if (!get_option('timesheet_allow_future_entries')) {
    add_option('timesheet_allow_future_entries', '0', 1);
}

if (!get_option('timesheet_require_task_selection')) {
    add_option('timesheet_require_task_selection', '1', 1);
}

if (!get_option('timesheet_auto_submit_weeks')) {
    add_option('timesheet_auto_submit_weeks', '0', 1);
}

// Set module version
if (!get_option('timesheet_module_version')) {
    add_option('timesheet_module_version', '1.4.2', 1);
} else {
    update_option('timesheet_module_version', '1.4.2');
}
?>