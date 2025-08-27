<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Timesheet
Description: Sistema de apontamento de horas com aprovação para profissionais e gerentes de projeto - Versão Simplificada
Version: 1.5.1
Requires at least: 2.3.*
Author: Perfex CRM Module Developer
*/

define('TIMESHEET_MODULE_NAME', 'timesheet');
define('TIMESHEET_MODULE_VERSION', '1.5.1');

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
 * Hook para inicializar menus e permissões.
 * VERSÃO SIMPLIFICADA - SEM HOOKS BIDIRECIONAIS
 */
hooks()->add_action('admin_init', 'timesheet_init_menu_and_permissions');

function timesheet_init_menu_and_permissions()
{
    $CI = &get_instance();

    // 1. REGISTRO DOS ITENS DE MENU
    // Atualizado para refletir a Opção B - Menus Separados

    // Menu principal Timesheet
    if (has_permission('timesheet', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('timesheet_main', [
            'name'     => _l('timesheet'),
            'href'     => admin_url('timesheet'),
            'icon'     => 'fa fa-clock-o',
            'position' => 30,
        ]);
    }

    // Submenu para Meu Timesheet
    if (has_permission('timesheet', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('timesheet_main', [
            'slug'     => 'timesheet_my_timesheet',
            'name'     => _l('timesheet_my_timesheet'),
            'href'     => admin_url('timesheet'),
            'icon'     => 'fa fa-user-clock',
        ]);
    }

    // Submenu para Aprovações Rápidas
    if (has_permission('timesheet', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('timesheet_main', [
            'slug'     => 'timesheet_quick_approvals',
            'name'     => _l('timesheet_quick_approvals'),
            'href'     => admin_url('timesheet/manage'),
            'icon'     => 'fa fa-flash',
        ]);
    }

    // Submenu para Aprovações Semanais
    if (is_admin() || timesheet_can_manage_any_project(get_staff_user_id())) {
        $CI->app_menu->add_sidebar_children_item('timesheet_main', [
            'slug'     => 'timesheet_weekly_approvals', 
            'name'     => _l('timesheet_weekly_approvals'),
            'href'     => admin_url('timesheet/manage_weekly'),
            'icon'     => 'fa fa-calendar-check-o',
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

    log_activity('[Timesheet v1.5.1] Módulo inicializado - MODO UNIDIRECIONAL (sem hooks bidirecionais)');
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
        // Adicionar script para gerenciar CSRF token
        echo '<script>
                var csrfData = {
                    "ci_csrf_token": "' . get_instance()->security->get_csrf_hash() . '"
                };
                $.ajaxSetup({
                    data: csrfData
                });
            </script>';
    }
}

/**
 * Carrega o helper do módulo.
 */
$CI = &get_instance();
$CI->load->helper(TIMESHEET_MODULE_NAME . '/timesheet');