<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration for Timesheet Module v1.0.3
 * 
 * This migration handles the update from version 1.0.2 to 1.0.3
 * 
 * Changes in v1.0.3:
 * - Fixed critical PHP error that prevented module activation
 * - Corrected install.php file structure (missing closing PHP tag)
 * - Resolved "unknown status (0)" error during module installation
 * - Fixed blank screen issue when trying to activate module
 */
class Migration_Version_103 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // No database changes required for v1.0.3
        // This version fixes critical PHP errors that prevented module activation
        
        // Update module version option
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.3');
        } else {
            add_option('timesheet_module_version', '1.0.3', 1);
        }
        
        // Log the migration
        log_activity('Timesheet module updated to version 1.0.3');
        
        return true;
    }

    public function down()
    {
        // Rollback to previous version
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.2');
        }
        
        log_activity('Timesheet module rollback from version 1.0.3 to 1.0.2');
        
        return true;
    }
}

