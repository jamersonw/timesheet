<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration for Timesheet Module v1.0.2
 * 
 * This migration handles the update from version 1.0.1 to 1.0.2
 * 
 * Changes in v1.0.2:
 * - Fixed hours mask clearing values when leaving field
 * - Fixed error message when submitting timesheet (still showing error on success)
 * - Added user-friendly status messages instead of technical codes
 * - Complete Portuguese translation for the entire module
 */
class Migration_Version_102 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // No database changes required for v1.0.2
        // This version includes JavaScript fixes, translations and interface improvements
        
        // Update module version option
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.2');
        } else {
            add_option('timesheet_module_version', '1.0.2', 1);
        }
        
        // Log the migration
        log_activity('Timesheet module updated to version 1.0.2');
        
        return true;
    }

    public function down()
    {
        // Rollback to previous version
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.1');
        }
        
        log_activity('Timesheet module rollback from version 1.0.2 to 1.0.1');
        
        return true;
    }
}

