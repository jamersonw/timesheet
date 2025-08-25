<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.0.9 - Correção crítica do auto-save
        // Não há alterações de banco de dados necessárias
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.0.9', 1);
        } else {
            update_option('timesheet_module_version', '1.0.9');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.0.8');
    }
}

