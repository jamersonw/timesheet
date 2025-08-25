<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration for Timesheet Module v1.0.6
 * 
 * This migration handles the update from version 1.0.5 to 1.0.6
 * 
 * Changes in v1.0.6:
 * - FIXED: Hours mask formatting issue with HTML number inputs
 * - Changed formatHours function to use dot (.) instead of comma (,)
 * - Moved formatHours and parseHours functions to global scope
 * - Now properly preserves values when leaving input fields
 */
class Migration_Version_106 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // No database changes required for v1.0.6
        // This version fixes the critical hours formatting issue
        
        // Update module version option
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.6');
        } else {
            add_option('timesheet_module_version', '1.0.6', 1);
        }
        
        // Log the migration
        log_activity('Timesheet module updated to version 1.0.6 - Hours mask formatting fixed');
        
        return true;
    }

    public function down()
    {
        // Rollback to previous version
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.5');
        }
        
        log_activity('Timesheet module rollback from version 1.0.6 to 1.0.5');
        
        return true;
    }
}

