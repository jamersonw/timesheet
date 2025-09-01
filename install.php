<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Função para log seguro com timestamp e mais detalhes
function safe_log_activity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[{$timestamp}] [{$level}] {$message}";

    if (function_exists('log_activity')) {
        log_activity($formatted_message);
    } else {
        error_log($formatted_message);
    }

    // Log adicional em arquivo específico para debug
    $log_file = __DIR__ . '/timesheet_install.log';
    file_put_contents($log_file, $formatted_message . "\n", FILE_APPEND | LOCK_EX);
}

// Função para verificar se uma opção existe de forma segura
function safe_get_option($option) {
    try {
        if (function_exists('get_option')) {
            $result = get_option($option);
            safe_log_activity("Opção '{$option}' consultada: " . ($result ? 'EXISTE' : 'NÃO EXISTE'), 'DEBUG');
            return $result;
        }
        safe_log_activity("Função get_option não disponível", 'WARNING');
        return false;
    } catch (Exception $e) {
        safe_log_activity("Erro ao consultar opção '{$option}': " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Função para adicionar opção de forma segura
function safe_add_option($option, $value, $autoload = 1) {
    try {
        if (function_exists('add_option')) {
            $result = add_option($option, $value, $autoload);
            safe_log_activity("Opção '{$option}' adicionada com valor '{$value}': " . ($result ? 'SUCESSO' : 'FALHOU'), $result ? 'INFO' : 'ERROR');
            return $result;
        }
        safe_log_activity("Função add_option não disponível", 'WARNING');
        return false;
    } catch (Exception $e) {
        safe_log_activity("Erro ao adicionar opção '{$option}': " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Função para atualizar opção de forma segura
function safe_update_option($option, $value) {
    try {
        if (function_exists('update_option')) {
            $result = update_option($option, $value);
            safe_log_activity("Opção '{$option}' atualizada para '{$value}': " . ($result ? 'SUCESSO' : 'FALHOU'), $result ? 'INFO' : 'ERROR');
            return $result;
        }
        safe_log_activity("Função update_option não disponível", 'WARNING');
        return false;
    } catch (Exception $e) {
        safe_log_activity("Erro ao atualizar opção '{$option}': " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Função para verificar se db_prefix existe
function safe_db_prefix() {
    try {
        if (function_exists('db_prefix')) {
            $prefix = db_prefix();
            safe_log_activity("Prefixo do banco obtido: '{$prefix}'", 'DEBUG');
            return $prefix;
        }
        safe_log_activity("Função db_prefix não disponível, usando fallback 'tbl_'", 'WARNING');
        return 'tbl_'; // Fallback padrão
    } catch (Exception $e) {
        safe_log_activity("Erro ao obter prefixo do banco: " . $e->getMessage(), 'ERROR');
        return 'tbl_';
    }
}

// Função para verificar status de conectividade do banco
function check_database_connection($CI) {
    try {
        safe_log_activity("Testando conectividade do banco de dados...", 'DEBUG');

        // Teste básico de conexão
        $test_query = $CI->db->query('SELECT 1 as test');
        if (!$test_query) {
            $error = $CI->db->error();
            safe_log_activity("Falha no teste de conexão: " . $error['message'], 'ERROR');
            return false;
        }

        $result = $test_query->row();
        if ($result && $result->test == 1) {
            safe_log_activity("Teste de conexão bem-sucedido", 'INFO');
            return true;
        } else {
            safe_log_activity("Teste de conexão retornou resultado inesperado", 'ERROR');
            return false;
        }

    } catch (Exception $e) {
        safe_log_activity("Exceção durante teste de conexão: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Função para verificar permissões de criação de tabelas
function check_table_creation_permissions($CI, $db_prefix) {
    try {
        safe_log_activity("Verificando permissões para criação de tabelas...", 'DEBUG');

        $test_table = $db_prefix . 'timesheet_test_permissions';

        // Tentar criar tabela de teste
        $create_sql = "CREATE TABLE `{$test_table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `test_field` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        $create_result = $CI->db->query($create_sql);

        if ($create_result) {
            safe_log_activity("Tabela de teste criada com sucesso", 'INFO');

            // Tentar inserir dados
            $insert_result = $CI->db->query("INSERT INTO `{$test_table}` (test_field) VALUES ('test_data')");
            if ($insert_result) {
                safe_log_activity("Inserção de teste bem-sucedida", 'INFO');
            } else {
                safe_log_activity("Falha na inserção de teste", 'WARNING');
            }

            // Limpar tabela de teste
            $CI->db->query("DROP TABLE IF EXISTS `{$test_table}`");
            safe_log_activity("Tabela de teste removida", 'DEBUG');

            return true;
        } else {
            $error = $CI->db->error();
            safe_log_activity("Falha ao criar tabela de teste: " . $error['message'], 'ERROR');
            return false;
        }

    } catch (Exception $e) {
        safe_log_activity("Exceção durante verificação de permissões: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

try {
    safe_log_activity('=== INICIANDO INSTALAÇÃO DO MÓDULO TIMESHEET v1.4.5 ===', 'INFO');
    safe_log_activity('PHP Version: ' . PHP_VERSION, 'DEBUG');
    safe_log_activity('Memory Limit: ' . ini_get('memory_limit'), 'DEBUG');
    safe_log_activity('Max Execution Time: ' . ini_get('max_execution_time'), 'DEBUG');

    // Verificar se CI está disponível
    if (!isset($CI)) {
        safe_log_activity('Variável $CI não definida, tentando obter instância...', 'DEBUG');
        $CI = &get_instance();
        if (!$CI) {
            safe_log_activity('ERRO CRÍTICO: CodeIgniter instance não está disponível', 'FATAL');
            throw new Exception('CodeIgniter instance not available');
        }
    }

    safe_log_activity('CodeIgniter instance obtida com sucesso', 'INFO');
    safe_log_activity('CI Class: ' . get_class($CI), 'DEBUG');

    // Verificar se o banco de dados está disponível
    if (!isset($CI->db) || !is_object($CI->db)) {
        safe_log_activity('ERRO CRÍTICO: Database instance não está disponível', 'FATAL');
        throw new Exception('Database instance not available');
    }

    safe_log_activity('Database instance disponível', 'INFO');
    safe_log_activity('Database Driver: ' . $CI->db->platform(), 'DEBUG');

    // Testar conectividade do banco
    if (!check_database_connection($CI)) {
        throw new Exception('Database connection test failed');
    }

    $db_prefix = safe_db_prefix();
    safe_log_activity('Usando prefixo de banco: ' . $db_prefix, 'INFO');

    // Verificar permissões de criação de tabelas
    if (!check_table_creation_permissions($CI, $db_prefix)) {
        safe_log_activity('AVISO: Permissões de criação de tabela podem estar limitadas', 'WARNING');
    }

    // ===============================
    // CRIAÇÃO DA TABELA timesheet_entries
    // ===============================

    safe_log_activity('--- VERIFICANDO TABELA timesheet_entries ---', 'INFO');

    $entries_table = $db_prefix . 'timesheet_entries';
    $table_exists = false;

    try {
        $table_exists = $CI->db->table_exists($entries_table);
        safe_log_activity("Verificação table_exists para '{$entries_table}': " . ($table_exists ? 'TRUE' : 'FALSE'), 'DEBUG');
    } catch (Exception $e) {
        safe_log_activity('Erro ao verificar tabela timesheet_entries: ' . $e->getMessage(), 'ERROR');
        // Tentar método alternativo
        try {
            $result = $CI->db->query("SHOW TABLES LIKE '{$entries_table}'");
            $table_exists = ($result && $result->num_rows() > 0);
            safe_log_activity("Verificação alternativa SHOW TABLES: " . ($table_exists ? 'TRUE' : 'FALSE'), 'DEBUG');
        } catch (Exception $e2) {
            safe_log_activity('Método alternativo também falhou: ' . $e2->getMessage(), 'ERROR');
            $table_exists = false;
        }
    }

    if (!$table_exists) {
        safe_log_activity('Criando tabela timesheet_entries...', 'INFO');

        $sql = "CREATE TABLE `{$entries_table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `project_id` int(11) NOT NULL,
            `task_id` int(11) DEFAULT NULL,
            `perfex_timer_id` int(11) DEFAULT NULL,
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
            KEY `perfex_timer_id` (`perfex_timer_id`),
            KEY `week_start_date` (`week_start_date`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        safe_log_activity('SQL para timesheet_entries: ' . substr($sql, 0, 200) . '...', 'DEBUG');

        try {
            $result = $CI->db->query($sql);
            if ($result) {
                safe_log_activity('✅ Tabela timesheet_entries criada com sucesso', 'SUCCESS');
            } else {
                $error = $CI->db->error();
                safe_log_activity('❌ Falha ao criar tabela timesheet_entries: ' . $error['message'], 'FATAL');
                safe_log_activity('Código do erro: ' . $error['code'], 'DEBUG');
                throw new Exception('Failed to create timesheet_entries table: ' . $error['message']);
            }
        } catch (Exception $e) {
            safe_log_activity('Exceção ao criar tabela timesheet_entries: ' . $e->getMessage(), 'FATAL');
            throw $e;
        }
    } else {
        safe_log_activity('✓ Tabela timesheet_entries já existe', 'INFO');

        // Verificar se todas as colunas necessárias existem
        $required_columns = ['perfex_timer_id'];
        foreach ($required_columns as $column) {
            try {
                $has_column = $CI->db->field_exists($column, $entries_table);
                if (!$has_column) {
                    safe_log_activity("Adicionando coluna faltante '{$column}' na tabela timesheet_entries...", 'INFO');
                    $CI->db->query("ALTER TABLE `{$entries_table}` ADD COLUMN `{$column}` INT(11) NULL");
                    safe_log_activity("✅ Coluna '{$column}' adicionada", 'SUCCESS');
                }
            } catch (Exception $e) {
                safe_log_activity("Erro ao verificar/adicionar coluna '{$column}': " . $e->getMessage(), 'ERROR');
            }
        }
    }

    // ===============================
    // CRIAÇÃO DA TABELA timesheet_approvals
    // ===============================

    safe_log_activity('--- VERIFICANDO TABELA timesheet_approvals ---', 'INFO');

    $approvals_table = $db_prefix . 'timesheet_approvals';
    $table_exists = false;

    try {
        $table_exists = $CI->db->table_exists($approvals_table);
        safe_log_activity("Verificação table_exists para '{$approvals_table}': " . ($table_exists ? 'TRUE' : 'FALSE'), 'DEBUG');
    } catch (Exception $e) {
        safe_log_activity('Erro ao verificar tabela timesheet_approvals: ' . $e->getMessage(), 'ERROR');
        try {
            $result = $CI->db->query("SHOW TABLES LIKE '{$approvals_table}'");
            $table_exists = ($result && $result->num_rows() > 0);
            safe_log_activity("Verificação alternativa SHOW TABLES: " . ($table_exists ? 'TRUE' : 'FALSE'), 'DEBUG');
        } catch (Exception $e2) {
            safe_log_activity('Método alternativo também falhou: ' . $e2->getMessage(), 'ERROR');
            $table_exists = false;
        }
    }

    if (!$table_exists) {
        safe_log_activity('Criando tabela timesheet_approvals...', 'INFO');

        $sql = "CREATE TABLE `{$approvals_table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `project_id` int(11) NOT NULL,
            `task_id` int(11) NOT NULL,
            `week_start_date` date NOT NULL,
            `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `submitted_at` datetime NOT NULL,
            `approved_by` int(11) NULL,
            `approved_at` datetime NULL,
            `rejection_reason` text NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_staff_week_task` (`staff_id`, `week_start_date`, `task_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_project_task` (`project_id`, `task_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        safe_log_activity('SQL para timesheet_approvals: ' . substr($sql, 0, 200) . '...', 'DEBUG');

        try {
            $result = $CI->db->query($sql);
            if ($result) {
                safe_log_activity('✅ Tabela timesheet_approvals criada com sucesso', 'SUCCESS');
            } else {
                $error = $CI->db->error();
                safe_log_activity('❌ Falha ao criar tabela timesheet_approvals: ' . $error['message'], 'FATAL');
                safe_log_activity('Código do erro: ' . $error['code'], 'DEBUG');
                throw new Exception('Failed to create timesheet_approvals table: ' . $error['message']);
            }
        } catch (Exception $e) {
            safe_log_activity('Exceção ao criar tabela timesheet_approvals: ' . $e->getMessage(), 'FATAL');
            throw $e;
        }
    } else {
        safe_log_activity('✓ Tabela timesheet_approvals já existe', 'INFO');

        // Verificar se as novas colunas existem (para migração de versões antigas)
        $required_columns = ['project_id', 'task_id'];
        foreach ($required_columns as $column) {
            try {
                $has_column = $CI->db->field_exists($column, $approvals_table);
                if (!$has_column) {
                    safe_log_activity("Adicionando coluna faltante '{$column}' na tabela timesheet_approvals...", 'INFO');
                    if ($column == 'project_id') {
                        $CI->db->query("ALTER TABLE `{$approvals_table}` ADD COLUMN `project_id` INT(11) NOT NULL AFTER `staff_id`");
                    } elseif ($column == 'task_id') {
                        $CI->db->query("ALTER TABLE `{$approvals_table}` ADD COLUMN `task_id` INT(11) NOT NULL AFTER `project_id`");
                    }
                    safe_log_activity("✅ Coluna '{$column}' adicionada", 'SUCCESS');
                } else {
                    safe_log_activity("✓ Coluna '{$column}' já existe", 'DEBUG');
                }
            } catch (Exception $e) {
                safe_log_activity("Erro ao verificar/adicionar coluna '{$column}': " . $e->getMessage(), 'ERROR');
            }
        }

        // Atualizar índices se necessário
        try {
            safe_log_activity('Atualizando índices da tabela timesheet_approvals...', 'DEBUG');
            $CI->db->query("DROP INDEX IF EXISTS `idx_staff_week` ON `{$approvals_table}`");
            $CI->db->query("CREATE INDEX IF NOT EXISTS `idx_staff_week_task` ON `{$approvals_table}` (`staff_id`, `week_start_date`, `task_id`)");
            $CI->db->query("CREATE INDEX IF NOT EXISTS `idx_project_task` ON `{$approvals_table}` (`project_id`, `task_id`)");
            safe_log_activity('✅ Índices atualizados', 'SUCCESS');
        } catch (Exception $e) {
            safe_log_activity('Aviso: Erro ao atualizar índices: ' . $e->getMessage(), 'WARNING');
        }
    }

    // ===============================
    // CONFIGURAÇÃO DE OPÇÕES PADRÃO
    // ===============================

    safe_log_activity('--- CONFIGURANDO OPÇÕES PADRÃO ---', 'INFO');

    $options = [
        'timesheet_default_hours_per_day' => '8',
        'timesheet_allow_future_entries' => '0',
        'timesheet_require_task_selection' => '1',
        'timesheet_auto_submit_weeks' => '0'
    ];

    foreach ($options as $option_name => $option_value) {
        try {
            if (!safe_get_option($option_name)) {
                if (safe_add_option($option_name, $option_value, 1)) {
                    safe_log_activity("✅ Opção '{$option_name}' criada com valor '{$option_value}'", 'SUCCESS');
                } else {
                    safe_log_activity("❌ Falha ao criar opção '{$option_name}'", 'ERROR');
                }
            } else {
                safe_log_activity("✓ Opção '{$option_name}' já existe", 'DEBUG');
            }
        } catch (Exception $e) {
            safe_log_activity("Erro ao configurar opção '{$option_name}': " . $e->getMessage(), 'ERROR');
        }
    }

    // ===============================
    // CONFIGURAÇÃO DE PERMISSÕES
    // ===============================

    safe_log_activity('--- CONFIGURANDO PERMISSÕES DO MÓDULO ---', 'INFO');

    $permissions = [
        'view' => 'Visualizar',
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Deletar',
        'approve' => 'Aprovar Timesheet'
    ];

    try {
        if (isset($CI) && method_exists($CI, 'load')) {
            safe_log_activity('Configurando permissões via método nativo...', 'DEBUG');

            // Verificar se a tabela de permissões existe
            $permissions_table = $db_prefix . 'staff_permissions';
            $table_exists = $CI->db->table_exists($permissions_table);

            if ($table_exists) {
                safe_log_activity("Tabela de permissões '{$permissions_table}' encontrada", 'DEBUG');

                foreach ($permissions as $permission => $name) {
                    try {
                        $check = $CI->db->get_where($permissions_table, [
                            'feature' => 'timesheet',
                            'capability' => $permission
                        ]);

                        if ($check->num_rows() == 0) {
                            $insert_data = [
                                'feature' => 'timesheet',
                                'capability' => $permission,
                                'created' => date('Y-m-d H:i:s')
                            ];

                            if ($CI->db->insert($permissions_table, $insert_data)) {
                                safe_log_activity("✅ Permissão '{$permission}' ({$name}) adicionada", 'SUCCESS');
                            } else {
                                $error = $CI->db->error();
                                safe_log_activity("❌ Falha ao adicionar permissão '{$permission}': " . $error['message'], 'ERROR');
                            }
                        } else {
                            safe_log_activity("✓ Permissão '{$permission}' já existe", 'DEBUG');
                        }
                    } catch (Exception $e) {
                        safe_log_activity("Erro ao processar permissão '{$permission}': " . $e->getMessage(), 'ERROR');
                    }
                }

                safe_log_activity('✅ Permissões do módulo configuradas', 'SUCCESS');
            } else {
                safe_log_activity("❌ Tabela de permissões '{$permissions_table}' não encontrada", 'ERROR');
            }

        } else {
            safe_log_activity('CI não disponível para configurar permissões', 'WARNING');
        }
    } catch (Exception $e) {
        safe_log_activity('Falha ao adicionar permissões: ' . $e->getMessage(), 'WARNING');
        // Não interromper a instalação por causa das permissões
    }

    // ===============================
    // CONFIGURAÇÃO DA VERSÃO
    // ===============================

    safe_log_activity('--- CONFIGURANDO VERSÃO DO MÓDULO ---', 'INFO');

    $version = '1.5.1';
    try {
        if (!safe_get_option('timesheet_module_version')) {
            if (safe_add_option('timesheet_module_version', $version, 1)) {
                safe_log_activity("✅ Versão {$version} definida", 'SUCCESS');
            } else {
                safe_log_activity("❌ Falha ao definir versão", 'ERROR');
            }
        } else {
            if (safe_update_option('timesheet_module_version', $version)) {
                safe_log_activity("✅ Versão atualizada para {$version}", 'SUCCESS');
            } else {
                safe_log_activity("❌ Falha ao atualizar versão", 'ERROR');
            }
        }
    } catch (Exception $e) {
        safe_log_activity("Erro ao configurar versão: " . $e->getMessage(), 'ERROR');
    }

    safe_log_activity('=== INSTALAÇÃO CONCLUÍDA COM SUCESSO! ===', 'SUCCESS');
    safe_log_activity('Módulo Timesheet v' . $version . ' instalado e configurado', 'INFO');

    return true;

} catch (Exception $e) {
    safe_log_activity('=== ERRO FATAL NA INSTALAÇÃO ===', 'FATAL');
    safe_log_activity('Mensagem: ' . $e->getMessage(), 'FATAL');
    safe_log_activity('Arquivo: ' . $e->getFile(), 'FATAL');
    safe_log_activity('Linha: ' . $e->getLine(), 'FATAL');
    safe_log_activity('Stack trace: ' . $e->getTraceAsString(), 'DEBUG');

    // Log no error_log do PHP como backup
    error_log('[Timesheet Install] FATAL ERROR: ' . $e->getMessage());

    return false;
} finally {
    safe_log_activity('=== FIM DO PROCESSO DE INSTALAÇÃO ===', 'INFO');
}
?>