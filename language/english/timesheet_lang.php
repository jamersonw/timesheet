<?php

# Version 1.0.7

$lang['timesheet'] = 'Timesheet';
$lang['timesheet_my_timesheet'] = 'My Timesheet';
$lang['timesheet_manage'] = 'Manage Timesheets';
$lang['timesheet_approvals'] = 'Timesheet Approvals';
$lang['timesheet_reports'] = 'Timesheet Reports';

// Days of week
$lang['timesheet_monday'] = 'Monday';
$lang['timesheet_tuesday'] = 'Tuesday';
$lang['timesheet_wednesday'] = 'Wednesday';
$lang['timesheet_thursday'] = 'Thursday';
$lang['timesheet_friday'] = 'Friday';
$lang['timesheet_saturday'] = 'Saturday';
$lang['timesheet_sunday'] = 'Sunday';

// Actions
$lang['timesheet_save'] = 'Save';
$lang['timesheet_submit'] = 'Submit for Approval';
$lang['timesheet_approve'] = 'Approve';
$lang['timesheet_reject'] = 'Reject';
$lang['timesheet_edit'] = 'Edit';
$lang['timesheet_delete'] = 'Delete';

// Status
$lang['timesheet_status_draft'] = 'Draft';
$lang['timesheet_status_submitted'] = 'Submitted';
$lang['timesheet_status_pending'] = 'Pending Approval';
$lang['timesheet_status_approved'] = 'Approved';
$lang['timesheet_status_rejected'] = 'Rejected';

// Messages
$lang['timesheet_saved_successfully'] = 'Timesheet saved successfully';
$lang['timesheet_submitted_successfully'] = 'Timesheet submitted for approval';
$lang['timesheet_approved_successfully'] = 'Timesheet approved successfully';
$lang['timesheet_rejected_successfully'] = 'Timesheet rejected successfully';
$lang['timesheet_no_projects_assigned'] = 'No projects assigned to you';
$lang['timesheet_select_project'] = 'Select Project';
$lang['timesheet_select_task'] = 'Select Task';
$lang['timesheet_hours'] = 'Hours';
$lang['timesheet_total'] = 'Total';
$lang['timesheet_week_of'] = 'Week of';
$lang['timesheet_previous_week'] = 'Previous Week';
$lang['timesheet_next_week'] = 'Next Week';
$lang['timesheet_this_week'] = 'This Week';
$lang['timesheet_current_week'] = 'Current Week';

// Validation
$lang['timesheet_invalid_hours'] = 'Invalid hours format';
$lang['timesheet_project_required'] = 'Project is required';
$lang['timesheet_task_required'] = 'Task is required. Please select a task.';
$lang['timesheet_already_submitted'] = 'This week has already been submitted';
$lang['timesheet_cannot_edit_approved'] = 'Cannot edit approved timesheets. Cancel approval first.';
$lang['timesheet_cannot_submit_approved'] = 'Cannot submit. Some tasks are already pending or approved.';

// Interface messages
$lang['timesheet_project_and_task_required'] = 'Please select a project AND a task.';
$lang['timesheet_selection_required'] = 'Selection Required';
$lang['timesheet_project_already_added'] = 'This project/task has already been added to your timesheet.';
$lang['timesheet_duplicate_project'] = 'Duplicate Project';
$lang['timesheet_add_activity_required'] = 'You must add at least one project/task before submitting the timesheet.';
$lang['timesheet_no_activity_selected'] = 'No Activity Selected';
$lang['timesheet_remove_row'] = 'Remove Row';
$lang['timesheet_confirm_remove_row'] = 'Are you sure you want to remove this row? All logged hours will be lost.';
$lang['timesheet_submit_for_approval'] = 'Submit for Approval';
$lang['timesheet_server_communication_error'] = 'Server communication error';
$lang['timesheet_connection_error_submit'] = 'Connection error when submitting for approval';
$lang['timesheet_save_before_submit_error'] = 'Failed to save hours before submission. Please try again.';
$lang['timesheet_cancel_submission'] = 'Cancel Submission';
$lang['timesheet_keep_as_is'] = 'Keep As Is';
$lang['timesheet_unsaved_changes_warning'] = 'Some changes may not have been saved. Do you want to continue anyway?';
$lang['timesheet_pending_changes'] = 'Pending Changes';
$lang['timesheet_unsaved_changes_exit_warning'] = 'You have unsaved changes. Are you sure you want to exit?';

// Controller messages
$lang['timesheet_no_task_selected_save'] = 'No task selected to save';
$lang['timesheet_cannot_edit_pending_approved'] = 'Cannot edit a task that is pending or already approved.';
$lang['timesheet_save_entry_failed'] = 'Failed to save entry.';
$lang['timesheet_submit_error'] = 'Error submitting timesheet';
$lang['timesheet_invalid_parameters'] = 'Invalid parameters';
$lang['timesheet_rejection_reason_required'] = 'Rejection reason is required';
$lang['timesheet_approval_processing_error'] = 'Error processing approval';
$lang['timesheet_approval_cancelled_success'] = 'Approval cancelled successfully. Timesheet returned to draft.';
$lang['timesheet_cancel_approval_error'] = 'Error cancelling approval';

// Permissions
$lang['timesheet_permission_view'] = 'View Timesheet (create own entries)';
$lang['timesheet_permission_approve'] = 'Approve Timesheet (access approval screens)';

// Timer blocking messages
$lang['timesheet_timer_disabled_message'] = 'Timer disabled for your role. Use: Timesheet → My Timesheet';
$lang['timesheet_board_disabled_message'] = 'Time board disabled. Use: Timesheet → Weekly Approvals';
$lang['timesheet_feature_restricted'] = 'This feature has been restricted by the Timesheet module';

// Weekly Timesheet Specific Translations
$lang['timesheet_weekly_total'] = 'Weekly Total';
$lang['timesheet_week_total_hours'] = 'Week Total Hours';
$lang['timesheet_approval_request'] = 'Approval Request';
$lang['timesheet_approval_pending'] = 'Approval Pending';
$lang['timesheet_submission_cancelled'] = 'Submission cancelled successfully';
$lang['timesheet_cannot_cancel_submission'] = 'Cannot cancel submission';
$lang['timesheet_weekly_approvals'] = 'Weekly Approvals';
$lang['timesheet_quick_approvals'] = 'Quick Approvals';

// Batch Selection
$lang['timesheet_batch_selection'] = 'Batch Selection';
$lang['timesheet_select_all_tasks'] = 'Select All Pending Tasks';
$lang['timesheet_tasks_selected'] = 'tasks selected';
$lang['timesheet_approve_selected'] = 'Approve Selected';
$lang['timesheet_reject_selected'] = 'Reject Selected';
$lang['timesheet_batch_approval'] = 'Batch Approval';
$lang['timesheet_batch_rejection'] = 'Batch Rejection';
$lang['timesheet_confirm_approve_selected'] = 'Are you sure you want to approve %d selected tasks?';
$lang['timesheet_confirm_reject_selected'] = 'Please provide a reason to reject %d selected tasks:';

// User specific actions
$lang['timesheet_approve_user_selected'] = 'Approve User Selected';
$lang['timesheet_reject_user_selected'] = 'Reject User Selected';
$lang['timesheet_confirm_approve_user_tasks'] = 'Are you sure you want to approve %d selected tasks for this user?';
$lang['timesheet_confirm_reject_user_tasks'] = 'Please provide a reason to reject %d selected tasks for this user:';

// Preview and entries
$lang['timesheet_no_entries_found'] = 'No entries found for the projects you manage.';
$lang['timesheet_loading_preview'] = 'Loading timesheet preview...';
$lang['timesheet_cancel_task_approval'] = 'Cancel Task Approval';
$lang['timesheet_confirm_cancel_task'] = 'Are you sure you want to cancel the approval of this specific task? The hours will be removed from the timesheet and the task will return to pending status.';
$lang['timesheet_yes_cancel'] = 'Yes, Cancel';
$lang['timesheet_task_approval_cancelled'] = 'Task approval cancelled successfully.';
$lang['timesheet_error_cancel_task'] = 'Error cancelling task approval.';

// General
$lang['timesheet_project_task'] = 'Project/Task';
$lang['timesheet_actions'] = 'Actions';
$lang['timesheet_no_pending_approvals'] = 'No pending timesheet approvals at this time.';
$lang['timesheet_select_user_tasks'] = 'Select all tasks for this user';

// Confirmation messages
$lang['timesheet_confirm_submit'] = 'Are you sure you want to submit this timesheet for approval? This action cannot be undone.';
$lang['timesheet_confirm_cancel_submission'] = 'Are you sure you want to cancel this submission? The timesheet will return to draft status.';

// Status messages
$lang['timesheet_pending_message'] = 'Your hours have been submitted and are awaiting manager approval.';
$lang['timesheet_approved_message'] = 'Your hours have been approved by the manager.';
$lang['timesheet_rejected_message'] = 'Your hours have been rejected. Please contact your manager for more details.';
$lang['timesheet_approved'] = 'Your hours have been approved by the manager.';
$lang['timesheet_rejected'] = 'Your hours have been rejected. Please contact your manager for more details.';
$lang['timesheet_pending'] = 'Your hours have been submitted and are awaiting manager approval.';

// Common UI elements
$lang['submitted_at'] = 'Submitted at';
$lang['staff'] = 'Staff';
$lang['options'] = 'Options';
$lang['view'] = 'View';
$lang['back'] = 'Back';
$lang['close'] = 'Close';
$lang['add'] = 'Add';
$lang['project'] = 'Project';
$lang['task'] = 'Task';

// Additional missing translations
$lang['timesheet_week_navigation'] = 'Week Navigation';
$lang['timesheet_previous_week'] = 'Previous Week';
$lang['timesheet_next_week'] = 'Next Week';
$lang['timesheet_current_week'] = 'Current Week';
$lang['timesheet_weekly_approvals'] = 'Weekly Approvals';
$lang['timesheet_quick_approvals'] = 'Quick Approvals';
$lang['timesheet_select_all'] = 'Select All';
$lang['timesheet_deselect_all'] = 'Deselect All';
$lang['timesheet_selected_tasks'] = 'selected tasks';
$lang['timesheet_approve_selected'] = 'Approve Selected';
$lang['timesheet_reject_selected'] = 'Reject Selected';
$lang['timesheet_no_tasks_selected'] = 'No tasks selected';
$lang['timesheet_confirm_bulk_approval'] = 'Confirm bulk approval';
$lang['timesheet_confirm_bulk_rejection'] = 'Confirm bulk rejection';
$lang['timesheet_bulk_approval_success'] = 'Tasks approved successfully';
$lang['timesheet_bulk_rejection_success'] = 'Tasks rejected successfully';
$lang['timesheet_processing'] = 'Processing...';
$lang['timesheet_saving'] = 'Saving...';
$lang['timesheet_saved'] = 'Saved';
$lang['timesheet_save_error'] = 'Save error';
$lang['timesheet_auto_save'] = 'Auto save';
$lang['timesheet_force_save'] = 'Force save';
$lang['timesheet_backup_save'] = 'Backup save';
$lang['timesheet_unsaved_changes'] = 'There are unsaved changes. Do you want to continue?';
$lang['timesheet_pending_operations'] = 'There are pending operations. Please wait...';
$lang['timesheet_debug_mode'] = 'Debug Mode';
$lang['timesheet_test_connection'] = 'Connection Test';
$lang['timesheet_database_ok'] = 'Database OK';
$lang['timesheet_permissions_ok'] = 'Permissions OK';
$lang['timesheet_module_status'] = 'Module Status';
$lang['timesheet_version_check'] = 'Version Check';
$lang['timesheet_table_exists'] = 'Table exists';
$lang['timesheet_table_missing'] = 'Table not found';
$lang['timesheet_installation_log'] = 'Installation Log';
$lang['timesheet_activation_debug'] = 'Activation Debug';
$lang['timesheet_sync_bidirectional'] = 'Bidirectional Sync';
$lang['timesheet_timer_reference'] = 'Timer Reference';
$lang['timesheet_unidirectional_mode'] = 'Unidirectional Mode';
$lang['timesheet_readonly_board'] = 'Read-only Board';
$lang['timesheet_pending'] = 'Pending';