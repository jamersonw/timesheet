
<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Função para log seguro
function safe_log_activity($message) {
    if (function_exists('log_activity')) {
        log_activity($message);
    } else {
        error_log($message);
    }
}

// Função para verificar se uma opção existe de forma segura
function safe_get_option($option) {
    if (function_exists('get_option')) {
        return get_option($option);
    }
    return false;
}

// Função para adicionar opção de forma segura
function safe_add_option($option, $value, $autoload = 1) {
    if (function_exists('add_option')) {
        return add_option($option, $value, $autoload);
    }
    return false;
}

// Função para atualizar opção de forma segura
function safe_update_option($option, $value) {
    if (function_exists('update_option')) {
        return update_option($option, $value);
    }
    return false;
}

// Função para verificar se db_prefix existe
function safe_db_prefix() {
    if (function_exists('db_prefix')) {
        return db_prefix();
    }
    return 'tbl_'; // Fallback padrão
}

// Função para adicionar permissões de forma segura
function safe_add_module_permissions($module, $permissions) {
    if (function_exists('add_module_permissions')) {
        return add_module_permissions($module, $permissions);
    }
    return false;
}

try {
    safe_log_activity('[Timesheet Install] Iniciando processo de instalação do módulo v1.4.4');
    
    // Verificar se CI está disponível
    if (!isset($CI)) {
        $CI = &get_instance();
        if (!$CI) {
            safe_log_activity('[Timesheet Install ERROR] Variável $CI não está disponível');
            throw new Exception('CodeIgniter instance not available');
        }
    }
    
    safe_log_activity('[Timesheet Install] CodeIgniter instance OK');

    // Verificar se o banco de dados está disponível
    if (!isset($CI->db) || !is_object($CI->db)) {
        safe_log_activity('[Timesheet Install ERROR] Database não está disponível');
        throw new Exception('Database instance not available');
    }

    $db_prefix = safe_db_prefix();
    safe_log_activity('[Timesheet Install] Usando prefixo de banco: ' . $db_prefix);

    // Create timesheet_entries table
    safe_log_activity('[Timesheet Install] Verificando tabela timesheet_entries...');
    
    $table_exists = false;
    try {
        $table_exists = $CI->db->table_exists($db_prefix . 'timesheet_entries');
    } catch (Exception $e) {
        safe_log_activity('[Timesheet Install ERROR] Erro ao verificar tabela timesheet_entries: ' . $e->getMessage());
        // Continuar tentando criar a tabela
    }

    if (!$table_exists) {
        safe_log_activity('[Timesheet Install] Criando tabela timesheet_entries...');
        
        $sql = 'CREATE TABLE `' . $db_prefix . "timesheet_entries` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
        
        try {
            $result = $CI->db->query($sql);
            if ($result) {
                safe_log_activity('[Timesheet Install] Tabela timesheet_entries criada com sucesso');
            } else {
                $error = $CI->db->error();
                safe_log_activity('[Timesheet Install ERROR] Falha ao criar tabela timesheet_entries: ' . $error['message']);
                throw new Exception('Failed to create timesheet_entries table: ' . $error['message']);
            }
        } catch (Exception $e) {
            safe_log_activity('[Timesheet Install ERROR] Exceção ao criar tabela timesheet_entries: ' . $e->getMessage());
            throw $e;
        }
    } else {
        safe_log_activity('[Timesheet Install] Tabela timesheet_entries já existe');
    }

    // Create timesheet_approvals table
    safe_log_activity('[Timesheet Install] Verificando tabela timesheet_approvals...');
    
    $table_exists = false;
    try {
        $table_exists = $CI->db->table_exists($db_prefix . 'timesheet_approvals');
    } catch (Exception $e) {
        safe_log_activity('[Timesheet Install ERROR] Erro ao verificar tabela timesheet_approvals: ' . $e->getMessage());
        $table_exists = false;
    }

    if (!$table_exists) {
        safe_log_activity('[Timesheet Install] Criando tabela timesheet_approvals...');
        
        $CI->db->query('CREATE TABLE `' . $db_prefix . 'timesheet_approvals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `project_id` int(11) NOT NULL,
            `task_id` int(11) NOT NULL,
            `week_start_date` date NOT NULL,
            `status` enum("pending","approved","rejected") NOT NULL DEFAULT "pending",
            `submitted_at` datetime NOT NULL,
            `approved_by` int(11) NULL,
            `approved_at` datetime NULL,
            `rejection_reason` text NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_staff_week_task` (`staff_id`, `week_start_date`, `task_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_project_task` (`project_id`, `task_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        
        safe_log_activity('[Timesheet Install] Tabela timesheet_approvals criada com sucesso');
    } else {
        safe_log_activity('[Timesheet Install] Tabela timesheet_approvals já existe');
        
        // Verificar se as novas colunas existem (para migração de versões antigas)
        $has_project_id = $CI->db->field_exists('project_id', $db_prefix . 'timesheet_approvals');
        $has_task_id = $CI->db->field_exists('task_id', $db_prefix . 'timesheet_approvals');
        
        if (!$has_project_id) {
            safe_log_activity('[Timesheet Install] Adicionando coluna project_id...');
            $CI->db->query('ALTER TABLE `' . $db_prefix . 'timesheet_approvals` ADD COLUMN `project_id` INT(11) NOT NULL AFTER `staff_id`');
        }
        
        if (!$has_task_id) {
            safe_log_activity('[Timesheet Install] Adicionando coluna task_id...');
            $CI->db->query('ALTER TABLE `' . $db_prefix . 'timesheet_approvals` ADD COLUMN `task_id` INT(11) NOT NULL AFTER `project_id`');
        }
        
        // Atualizar índices se necessário
        try {
            $CI->db->query('DROP INDEX IF EXISTS `idx_staff_week` ON `' . $db_prefix . 'timesheet_approvals`');
            $CI->db->query('CREATE INDEX `idx_staff_week_task` ON `' . $db_prefix . 'timesheet_approvals` (`staff_id`, `week_start_date`, `task_id`)');
            $CI->db->query('CREATE INDEX `idx_project_task` ON `' . $db_prefix . 'timesheet_approvals` (`project_id`, `task_id`)');
        } catch (Exception $e) {
            safe_log_activity('[Timesheet Install] Aviso: Erro ao criar índices: ' . $e->getMessage());
        }
    }provals: ' . $e->getMessage());
    }

    if (!$table_exists) {
        safe_log_activity('[Timesheet Install] Criando tabela timesheet_approvals...');
        
        $sql = 'CREATE TABLE `' . $db_prefix . "timesheet_approvals` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
        
        try {
            $result = $CI->db->query($sql);
            if ($result) {
                safe_log_activity('[Timesheet Install] Tabela timesheet_approvals criada com sucesso');
            } else {
                $error = $CI->db->error();
                safe_log_activity('[Timesheet Install ERROR] Falha ao criar tabela timesheet_approvals: ' . $error['message']);
                throw new Exception('Failed to create timesheet_approvals table: ' . $error['message']);
            }
        } catch (Exception $e) {
            safe_log_activity('[Timesheet Install ERROR] Exceção ao criar tabela timesheet_approvals: ' . $e->getMessage());
            throw $e;
        }
    } else {
        safe_log_activity('[Timesheet Install] Tabela timesheet_approvals já existe');
    }

    // Add default options
    safe_log_activity('[Timesheet Install] Configurando opções padrão...');
    
    $options = [
        'timesheet_default_hours_per_day' => '8',
        'timesheet_allow_future_entries' => '0',
        'timesheet_require_task_selection' => '1',
        'timesheet_auto_submit_weeks' => '0'
    ];

    foreach ($options as $option_name => $option_value) {
        if (!safe_get_option($option_name)) {
            if (safe_add_option($option_name, $option_value, 1)) {
                safe_log_activity('[Timesheet Install] Opção ' . $option_name . ' criada');
            } else {
                safe_log_activity('[Timesheet Install WARNING] Falha ao criar opção ' . $option_name);
            }
        } else {
            safe_log_activity('[Timesheet Install] Opção ' . $option_name . ' já existe');
        }
    }

    // Add permissions for timesheet module
    safe_log_activity('[Timesheet Install] Configurando permissões do módulo...');
    
    $permissions = [
        'view' => 'Visualizar',
        'create' => 'Criar', 
        'edit' => 'Editar',
        'delete' => 'Deletar',
        'approve' => 'Aprovar Timesheet'
    ];

    try {
        // Verificar se CI está disponível para usar o método correto
        if (isset($CI) && method_exists($CI, 'app_modules')) {
            // Usar método nativo do Perfex para registrar permissões
            $capabilities = [];
            $capabilities['capabilities'] = $permissions;
            
            // Inserir permissões diretamente na tabela se necessário
            foreach ($permissions as $permission => $name) {
                $check = $CI->db->get_where(db_prefix() . 'staff_permissions', [
                    'feature' => 'timesheet',
                    'capability' => $permission
                ]);
                
                if ($check->num_rows() == 0) {
                    $insert_data = [
                        'feature' => 'timesheet',
                        'capability' => $permission,
                        'created' => date('Y-m-d H:i:s')
                    ];
                    $CI->db->insert(db_prefix() . 'staff_permissions', $insert_data);
                    safe_log_activity('[Timesheet Install] Permissão ' . $permission . ' (' . $name . ') adicionada');
                }
            }
            
            safe_log_activity('[Timesheet Install] Permissões do módulo configuradas com sucesso');
        } else {
            safe_log_activity('[Timesheet Install WARNING] CI não disponível para configurar permissões');
        }
    } catch (Exception $e) {
        safe_log_activity('[Timesheet Install WARNING] Falha ao adicionar permissões: ' . $e->getMessage());
        // Não interromper a instalação por causa das permissões
    }

    // Set module version
    safe_log_activity('[Timesheet Install] Configurando versão do módulo...');
    
    if (!safe_get_option('timesheet_module_version')) {
        if (safe_add_option('timesheet_module_version', '1.4.4', 1)) {
            safe_log_activity('[Timesheet Install] Versão 1.4.4 definida');
        } else {
            safe_log_activity('[Timesheet Install WARNING] Falha ao definir versão');
        }
    } else {
        if (safe_update_option('timesheet_module_version', '1.4.4')) {
            safe_log_activity('[Timesheet Install] Versão atualizada para 1.4.4');
        } else {
            safe_log_activity('[Timesheet Install WARNING] Falha ao atualizar versão');
        }
    }

    safe_log_activity('[Timesheet Install] Instalação concluída com sucesso!');
    
    // Return success para o sistema do Perfex
    return true;

} catch (Exception $e) {
    safe_log_activity('[Timesheet Install FATAL ERROR] ' . $e->getMessage());
    safe_log_activity('[Timesheet Install FATAL ERROR] File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    safe_log_activity('[Timesheet Install FATAL ERROR] Stack trace: ' . $e->getTraceAsString());
    
    // Não re-throw para evitar quebrar completamente o sistema
    error_log('[Timesheet Install] FATAL ERROR: ' . $e->getMessage());
    return false;
}
?>
