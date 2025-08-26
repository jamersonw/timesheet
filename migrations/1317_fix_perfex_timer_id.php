
<old_str><?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Fix_perfex_timer_id extends App_module_migration
{
    public function up()
    {
        // Verificar e adicionar campo perfex_timer_id se não existir
        if (!$this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) NULL AFTER `task_id`');
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD INDEX `idx_perfex_timer_id` (`perfex_timer_id`)');
            log_activity('[Timesheet Migration 1317] Campo perfex_timer_id adicionado à tabela timesheet_entries');
        } else {
            log_activity('[Timesheet Migration 1317] Campo perfex_timer_id já existe na tabela timesheet_entries');
        }
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.3.17', 1);
        } else {
            update_option('timesheet_module_version', '1.3.17');
        }
    }

    public function down()
    {
        // Remover campo se necessário
        if ($this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` DROP INDEX `idx_perfex_timer_id`');
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` DROP COLUMN `perfex_timer_id`');
        }
        
        // Rollback para versão anterior
        update_option('timesheet_module_version', '1.3.16');
    }
}
?></old_str>
<new_str><?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Fix_perfex_timer_id extends App_module_migration
{
    public function up()
    {
        try {
            log_activity('[Timesheet Migration 1317] Iniciando verificação do campo perfex_timer_id');
            
            // Verificar estrutura da tabela primeiro
            $table_exists = $this->db->table_exists(db_prefix() . 'timesheet_entries');
            if (!$table_exists) {
                log_activity('[Timesheet Migration 1317] ERRO: Tabela timesheet_entries não existe');
                return false;
            }
            
            // Verificar e adicionar campo perfex_timer_id se não existir
            if (!$this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
                // Criar o campo com verificação de posição
                $sql = 'ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) NULL';
                
                // Tentar posicionar após task_id, mas se falhar, adicionar no final
                if ($this->db->field_exists('task_id', db_prefix() . 'timesheet_entries')) {
                    $sql .= ' AFTER `task_id`';
                }
                
                $this->db->query($sql);
                log_activity('[Timesheet Migration 1317] Campo perfex_timer_id adicionado com sucesso');
                
                // Criar índice com verificação
                $index_sql = 'ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD INDEX `idx_perfex_timer_id` (`perfex_timer_id`)';
                $this->db->query($index_sql);
                log_activity('[Timesheet Migration 1317] Índice idx_perfex_timer_id criado com sucesso');
                
            } else {
                log_activity('[Timesheet Migration 1317] Campo perfex_timer_id já existe na tabela timesheet_entries');
            }
            
            // Atualizar versão do módulo
            if (!get_option('timesheet_module_version')) {
                add_option('timesheet_module_version', '1.3.17', 1);
                log_activity('[Timesheet Migration 1317] Versão do módulo definida como 1.3.17');
            } else {
                update_option('timesheet_module_version', '1.3.17');
                log_activity('[Timesheet Migration 1317] Versão do módulo atualizada para 1.3.17');
            }
            
            return true;
            
        } catch (Exception $e) {
            log_activity('[Timesheet Migration 1317] ERRO: ' . $e->getMessage());
            return false;
        }
    }

    public function down()
    {
        try {
            // Remover campo se necessário
            if ($this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
                // Tentar remover índice primeiro (pode não existir)
                try {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` DROP INDEX `idx_perfex_timer_id`');
                } catch (Exception $e) {
                    // Índice pode não existir, continuar
                }
                
                // Remover campo
                $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` DROP COLUMN `perfex_timer_id`');
                log_activity('[Timesheet Migration 1317] Campo perfex_timer_id removido');
            }
            
            // Rollback para versão anterior
            update_option('timesheet_module_version', '1.3.16');
            log_activity('[Timesheet Migration 1317] Rollback para versão 1.3.16');
            
        } catch (Exception $e) {
            log_activity('[Timesheet Migration 1317] ERRO no rollback: ' . $e->getMessage());
        }
    }
}
?></new_str>
