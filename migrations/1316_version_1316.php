
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_1316 extends App_module_migration
{
    public function up()
    {
        // Adicionar campo perfex_timer_id para vincular timesheet entries aos timers do Perfex CRM
        if (!$this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) NULL AFTER `task_id`');
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD INDEX `idx_perfex_timer_id` (`perfex_timer_id`)');
            log_activity('[Timesheet Migration] Campo perfex_timer_id adicionado à tabela timesheet_entries');
        }
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.3.16', 1);
        } else {
            update_option('timesheet_module_version', '1.3.16');
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
        update_option('timesheet_module_version', '1.3.15');
    }
}
