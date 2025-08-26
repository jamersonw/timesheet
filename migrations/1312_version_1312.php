<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_1312 extends App_Model_Migration
{
    public function up()
    {
        // Atualização para versão 1.3.12
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.3.12', 1);
        } else {
            update_option('timesheet_module_version', '1.3.12');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.3.11');
    }
}
