<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Timesheet
Description: Sistema de apontamento de horas com aprovação para profissionais e gerentes de projeto
Version: 1.3.12
Requires at least: 2.3.*
Author: Perfex CRM Module Developer
*/

define('TIMESHEET_MODULE_NAME', 'timesheet');
define('TIMESHEET_MODULE_VERSION', '1.3.2');

/**
 * Register activation hook
 */
register_activation_hook(TIMESHEET_MODULE_NAME, 'timesheet_activation_hook');

function timesheet_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation hook
 */
register_deactivation_hook(TIMESHEET_MODULE_NAME, 'timesheet_deactivation_hook');

function timesheet_deactivation_hook()
{
    // Ações de limpeza na desativação (geralmente não usado para apagar dados)
}

/**
 * Register uninstall hook
 */
register_uninstall_hook(TIMESHEET_MODULE_NAME, 'timesheet_uninstall_hook');

function timesheet_uninstall_hook()
{
    $CI = &get_instance();

    // Apaga as tabelas do módulo
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'timesheet_entries`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'timesheet_approvals`');

    // Remove as opções do módulo
    $CI->db->where('name LIKE', 'timesheet_%');
    $CI->db->delete(db_prefix() . 'options');
}

/**
 * Register language files
 */
register_language_files(TIMESHEET_MODULE_NAME, [TIMESHEET_MODULE_NAME]);

/**
 * Hook para inicializar menus, permissões e os hooks de sincronização.
 * Usamos 'admin_init' para garantir que tudo seja carregado no ambiente administrativo.
 */
hooks()->add_action('admin_init', 'timesheet_init_all');

function timesheet_init_all()
{
    $CI = &get_instance();

    // 1. REGISTRO DOS ITENS DE MENU
    if (has_permission('timesheet', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('timesheet', [
            'name'     => _l('timesheet_my_timesheet'),
            'href'     => admin_url('timesheet'),
            'icon'     => 'fa fa-clock-o',
            'position' => 30,
        ]);
    }

    if (is_admin() || timesheet_can_manage_any_project(get_staff_user_id())) {
        $CI->app_menu->add_sidebar_menu_item('timesheet_manage', [
            'name'     => _l('timesheet_approvals'),
            'href'     => admin_url('timesheet/manage'),
            'icon'     => 'fa fa-check-circle',
            'position' => 31,
        ]);
    }

    // 2. REGISTRO DAS PERMISSÕES
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('timesheet', $capabilities, _l('timesheet'));

    // Hooks de sincronização com o sistema de timers do Perfex
    hooks()->add_action('task_timer_started', 'timesheet_sync_from_core_timer_started');
    hooks()->add_action('task_timer_deleted', 'timesheet_sync_from_core_timer_deleted');
}

/**
 * Função de callback para o hook de INÍCIO de timer.
 */
function timesheet_sync_from_core_timer_started($data)
{
    $CI = &get_instance();
    $task_id = $data['task_id'] ?? null;

    if ($task_id) {
        $CI->load->model('timesheet/timesheet_model');
        // A sincronização é feita quando o timer é finalizado
    }
}

/**
 * Função de callback para o hook de EXCLUSÃO de timer.
 * É chamada pelo Perfex quando um timer é deletado.
 * @param object $timesheet Objeto do timer que foi deletado
 */
function timesheet_sync_from_core_timer_deleted($timesheet)
{
    // LOG DE EXECUÇÃO: Confirma que o hook foi disparado.
    log_activity('[Timesheet Sync] Hook "task_timer_deleted" DISPARADO. Timer ID: ' . ($timesheet->id ?? 'N/A'));

    $CI = &get_instance();

    if (isset($timesheet->id)) {
        $timer_id = $timesheet->id;

        // Buscar entrada do timesheet que referencia este timer
        $CI->db->where('perfex_timer_id', $timer_id);
        $entry = $CI->db->get(db_prefix() . 'timesheet_entries')->row();

        if ($entry) {
            // Remover referência do timer (não deletar a entrada, apenas limpar a referência)
            $CI->db->where('id', $entry->id);
            $CI->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => null]);

            log_activity('[Timesheet Sync] Referência do timer ' . $timer_id . ' removida da entrada ' . $entry->id);
        } else {
            log_activity('[Timesheet Sync] Timer ' . $timer_id . ' deletado, mas não encontrada entrada correspondente no timesheet');
        }
    } else {
        log_activity('[Timesheet Sync ERROR] Dados incompletos no hook task_timer_deleted');
    }
}

/**
 * Função auxiliar para verificar se o usuário pode gerenciar projetos.
 */
function timesheet_can_manage_any_project($staff_id)
{
    if (is_admin($staff_id)) {
        return true;
    }

    $CI = &get_instance();
    $CI->db->select('COUNT(*) as count');
    $CI->db->from(db_prefix() . 'projects');
    $CI->db->where('addedfrom', $staff_id);
    $result = $CI->db->get()->row();

    return $result && $result->count > 0;
}

/**
 * Carrega os assets (CSS/JS) do módulo.
 */
hooks()->add_action('app_admin_head', 'timesheet_load_admin_assets');

function timesheet_load_admin_assets()
{
    $CI = &get_instance();
    if (strpos($CI->uri->uri_string(), 'timesheet') !== false) {
        echo '<link rel="stylesheet" href="' . module_dir_url('timesheet', 'assets/css/timesheet.css') . '">';
    }
}

/**
 * Carrega o helper do módulo.
 */
$CI = &get_instance();
$CI->load->helper('timesheet_helper');