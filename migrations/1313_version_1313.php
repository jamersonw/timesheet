<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_1313 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.3.13
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.3.13', 1);
        } else {
            update_option('timesheet_module_version', '1.3.13');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.3.12');
    }
}
