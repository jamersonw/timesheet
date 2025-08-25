<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        // Atualiza a opção da versão do módulo no banco de dados
        update_option('timesheet_module_version', '1.0.8');

        // Log da atividade
        log_activity('Módulo Timesheet atualizado para a versão 1.0.8 - Integração com tarefas do projeto');
    }
}