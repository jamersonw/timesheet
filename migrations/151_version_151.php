
<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        // Versão 1.5.1 - Apenas atualização de versão
        // Correções de tradução e lógica de submissão implementadas
        log_activity('[Timesheet Migration 151] Migração para versão 1.5.1 executada - Correções de submissão e tradução');
    }

    public function down()
    {
        // Não há alterações estruturais para reverter
    }
}
