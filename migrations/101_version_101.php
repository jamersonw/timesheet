<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration for Timesheet Module v1.0.1
 * 
 * This migration handles the update from version 1.0.0 to 1.0.1
 * 
 * Changes in v1.0.1:
 * - Fixed error message when submitting timesheet for approval
 * - Implemented numeric mask for hours input fields
 * - Added changelog system for version tracking
 */
class Migration_Version_101 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // No database changes required for v1.0.1
        // This version only includes JavaScript and interface improvements
        
        // Update module version option
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.1');
        } else {
            add_option('timesheet_module_version', '1.0.1', 1);
        }
        
        // Log the migration
        log_activity('Timesheet module updated to version 1.0.1');
        
        return true;
    }

    public function down()
    {
        // Rollback to previous version
        if (get_option('timesheet_module_version')) {
            update_option('timesheet_module_version', '1.0.0');
        }
        
        log_activity('Timesheet module rollback from version 1.0.1 to 1.0.0');
        
        return true;
    }
}

