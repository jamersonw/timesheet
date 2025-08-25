<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        // Adicionar campo perfex_timer_id para armazenar referência do timer do Perfex CRM
        if (!$this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) NULL AFTER `task_id`');
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD KEY `perfex_timer_id` (`perfex_timer_id`)');
        }
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.2.0', 1);
        } else {
            update_option('timesheet_module_version', '1.2.0');
        }
    }

    public function down()
    {
        // Remover campo se necessário
        if ($this->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` DROP COLUMN `perfex_timer_id`');
        }
        
        // Rollback para versão anterior
        update_option('timesheet_module_version', '1.1.1');
    }
}

