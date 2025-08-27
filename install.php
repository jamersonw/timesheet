<?php

defined('BASEPATH') or exit('No direct script access allowed');

try {
    log_activity('[Timesheet Install] Iniciando processo de instalação do módulo v1.4.4');
    
    // Verificar se CI está disponível
    if (!isset($CI)) {
        log_activity('[Timesheet Install ERROR] Variável $CI não está disponível');
        throw new Exception('CodeIgniter instance not available');
    }
    
    log_activity('[Timesheet Install] CodeIgniter instance OK');

// Create timesheet_entries table
log_activity('[Timesheet Install] Verificando tabela timesheet_entries...');
if (!$CI->db->table_exists(db_prefix() . 'timesheet_entries')) {
    log_activity('[Timesheet Install] Criando tabela timesheet_entries...');
    $result = $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheet_entries` (
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
    
    if ($result) {
        log_activity('[Timesheet Install] Tabela timesheet_entries criada com sucesso');
    } else {
        log_activity('[Timesheet Install ERROR] Falha ao criar tabela timesheet_entries: ' . $CI->db->error()['message']);
        throw new Exception('Failed to create timesheet_entries table');
    }
} else {
    log_activity('[Timesheet Install] Tabela timesheet_entries já existe');
}

// Create timesheet_approvals table
log_activity('[Timesheet Install] Verificando tabela timesheet_approvals...');
if (!$CI->db->table_exists(db_prefix() . 'timesheet_approvals')) {
    log_activity('[Timesheet Install] Criando tabela timesheet_approvals...');
    $result = $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheet_approvals` (
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
    
    if ($result) {
        log_activity('[Timesheet Install] Tabela timesheet_approvals criada com sucesso');
    } else {
        log_activity('[Timesheet Install ERROR] Falha ao criar tabela timesheet_approvals: ' . $CI->db->error()['message']);
        throw new Exception('Failed to create timesheet_approvals table');
    }
} else {
    log_activity('[Timesheet Install] Tabela timesheet_approvals já existe');
}

// Add default options
log_activity('[Timesheet Install] Configurando opções padrão...');
if (!get_option('timesheet_default_hours_per_day')) {
    add_option('timesheet_default_hours_per_day', '8', 1);
    log_activity('[Timesheet Install] Opção timesheet_default_hours_per_day criada');
}

if (!get_option('timesheet_allow_future_entries')) {
    add_option('timesheet_allow_future_entries', '0', 1);
    log_activity('[Timesheet Install] Opção timesheet_allow_future_entries criada');
}

if (!get_option('timesheet_require_task_selection')) {
    add_option('timesheet_require_task_selection', '1', 1);
    log_activity('[Timesheet Install] Opção timesheet_require_task_selection criada');
}

if (!get_option('timesheet_auto_submit_weeks')) {
    add_option('timesheet_auto_submit_weeks', '0', 1);
    log_activity('[Timesheet Install] Opção timesheet_auto_submit_weeks criada');
}

// Add permissions for timesheet module
log_activity('[Timesheet Install] Configurando permissões do módulo...');
$permissions = [];

// Basic timesheet permissions
$permissions[] = [
    'id' => 1,
    'name' => 'Visualizar',
    'short_name' => 'view',
];
$permissions[] = [
    'id' => 2,
    'name' => 'Criar',
    'short_name' => 'create',
];
$permissions[] = [
    'id' => 3,
    'name' => 'Editar',
    'short_name' => 'edit',
];
$permissions[] = [
    'id' => 4,
    'name' => 'Deletar',
    'short_name' => 'delete',
];

// NEW: Approval permission
$permissions[] = [
    'id' => 5,
    'name' => 'Aprovar Timesheet',
    'short_name' => 'approve',
];

try {
    add_module_permissions('timesheet', $permissions);
    log_activity('[Timesheet Install] Permissões do módulo adicionadas com sucesso');
} catch (Exception $e) {
    log_activity('[Timesheet Install ERROR] Falha ao adicionar permissões: ' . $e->getMessage());
    throw $e;
}

// Set module version
log_activity('[Timesheet Install] Configurando versão do módulo...');
if (!get_option('timesheet_module_version')) {
    add_option('timesheet_module_version', '1.4.4', 1);
    log_activity('[Timesheet Install] Versão 1.4.4 definida');
} else {
    update_option('timesheet_module_version', '1.4.4');
    log_activity('[Timesheet Install] Versão atualizada para 1.4.4');
}

log_activity('[Timesheet Install] Instalação concluída com sucesso!');

} catch (Exception $e) {
    log_activity('[Timesheet Install FATAL ERROR] ' . $e->getMessage());
    log_activity('[Timesheet Install FATAL ERROR] Stack trace: ' . $e->getTraceAsString());
    throw $e; // Re-throw para que o Perfex possa capturar
}
?>