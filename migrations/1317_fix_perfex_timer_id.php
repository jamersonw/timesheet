
<?php
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
?>
