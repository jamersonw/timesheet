<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.1.0 - Sincronização bidirecional melhorada
        // Não há alterações de banco de dados necessárias
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.1.0', 1);
        } else {
            update_option('timesheet_module_version', '1.1.0');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.0.9');
    }
}

