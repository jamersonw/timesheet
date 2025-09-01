<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_152 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.5.2
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.5.2', 1);
        } else {
            update_option('timesheet_module_version', '1.5.2');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.5.1');
    }
}
