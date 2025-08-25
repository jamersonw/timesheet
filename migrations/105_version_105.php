<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration for Timesheet Module v1.0.5
 * 
 * This migration handles the update from version 1.0.4 to 1.0.5
 * 
 * Changes in v1.0.5:
 * - Fixed hours mask clearing values when leaving field (formatHours function)
 * - Fixed Portuguese translation system (language/portuguese folder)
 * - Restored migrations folder as requested by user feedback
 * - Restored module version configuration in install.php
 */
class Migration_Version_105 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // No database changes required for v1.0.5
        // This version includes JavaScript fixes and translation improvements
        
        // Update module version option
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.5');
        } else {
            add_option('timesheet_module_version', '1.0.5', 1);
        }
        
        // Log the migration
        log_activity('Timesheet module updated to version 1.0.5');
        
        return true;
    }

    public function down()
    {
        // Rollback to previous version
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.4');
        }
        
        log_activity('Timesheet module rollback from version 1.0.5 to 1.0.4');
        
        return true;
    }
}

