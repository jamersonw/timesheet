<?php

# Version 1.0.2

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
$lang['timesheet_select_task'] = 'Select Task *';
$lang['timesheet_hours'] = 'Hours';
$lang['timesheet_total'] = 'Total';
$lang['timesheet_week_of'] = 'Week of';
$lang['timesheet_previous_week'] = 'Previous Week';
$lang['timesheet_next_week'] = 'Next Week';
$lang['timesheet_this_week'] = 'This Week';

// Validation
$lang['timesheet_invalid_hours'] = 'Invalid hours format';
$lang['timesheet_project_required'] = 'Project is required';
$lang['timesheet_task_required'] = 'Task is required. Please select a task.';
$lang['timesheet_already_submitted'] = 'This week has already been submitted';
$lang['timesheet_cannot_edit_approved'] = 'Cannot edit approved timesheet';

// Permissions
$lang['timesheet_permission_view'] = 'View Timesheet (create own entries)';
$lang['timesheet_permission_approve'] = 'Approve Timesheet (access approval screens)';

// User-friendly messages
$lang['timesheet_pending_message'] = 'Your hours have been submitted and are awaiting manager approval.';
$lang['timesheet_approved_message'] = 'Your hours have been approved by the manager.';
$lang['timesheet_rejected_message'] = 'Your hours have been rejected. Please contact your manager for more details.';

// Weekly Timesheet Specific Translations
$lang['timesheet_weekly_total'] = 'Weekly Total';
$lang['timesheet_week_total_hours'] = 'Week Total Hours';
$lang['timesheet_approval_request'] = 'Approval Request';
$lang['timesheet_approval_pending'] = 'Approval Pending';
$lang['timesheet_submission_cancelled'] = 'Submission cancelled successfully';
$lang['timesheet_cannot_cancel_submission'] = 'Cannot cancel submission';
$lang['timesheet_approved_successfully'] = 'Timesheet approved successfully';
$lang['timesheet_rejected_successfully'] = 'Timesheet rejected successfully';
$lang['timesheet_weekly_approvals'] = 'Weekly Approvals';
$lang['timesheet_quick_approvals'] = 'Quick Approvals';

// Error messages
$lang['access_denied'] = 'Access denied';
$lang['timesheet_invalid_parameters'] = 'Invalid parameters';
$lang['timesheet_error_processing_approval'] = 'Error processing approval';
$lang['timesheet_error_submitting'] = 'Error submitting timesheet';
$lang['timesheet_error_saving_entry'] = 'Failed to save entry';

// Additional messages
$lang['timesheet_no_pending_approvals'] = 'No pending timesheet approvals at this time';
$lang['timesheet_no_pending_approvals_week'] = 'No pending approvals for the selected week';
$lang['timesheet_week_period'] = 'Week from %s to %s';
$lang['timesheet_pending_approvals_count'] = '%d approval(s) pending for the selected week';
$lang['timesheet_select_all_pending_tasks'] = 'Select All Pending Tasks';
$lang['timesheet_approve_selected'] = 'Approve Selected';
$lang['timesheet_reject_selected'] = 'Reject Selected';
$lang['timesheet_tasks_selected'] = 'tasks selected';
$lang['timesheet_view_quick_approvals'] = 'View Quick Approvals';
$lang['timesheet_no_entries_found_week'] = 'No timesheet entries found for this week';

// Batch actions
$lang['timesheet_batch_selection'] = 'Batch Selection';
$lang['timesheet_submitted_at'] = 'Submitted at';
$lang['timesheet_total_hours'] = 'Total hours';

// JavaScript messages
$lang['timesheet_no_activities_warning'] = 'You must add at least one project/task before submitting the timesheet.';
$lang['timesheet_no_activities_title'] = 'No Activities Selected';
$lang['timesheet_confirm_submit_default'] = 'Are you sure you want to submit this timesheet for approval? This action cannot be undone.';
$lang['timesheet_attention'] = 'Attention';
$lang['timesheet_submitting_zero_hours'] = 'You are submitting a timesheet with no hours logged (all days are zero)';
$lang['timesheet_submit_for_approval'] = 'Submit for Approval';
$lang['timesheet_confirm_cancel_submission_default'] = 'Are you sure you want to cancel the submission of this timesheet? It will return to draft status.';
$lang['timesheet_keep_as_is'] = 'Keep As Is';
$lang['timesheet_select_project_task_required'] = 'Please select a project AND a task.';
$lang['timesheet_required_selection'] = 'Required Selection';
$lang['timesheet_project_already_added'] = 'This project/task has already been added to your timesheet.';
$lang['timesheet_duplicate_project'] = 'Duplicate Project';
$lang['timesheet_remove_row'] = 'Remove Row';
$lang['timesheet_confirm_remove_row'] = 'Are you sure you want to remove this row? All hours logged in it will be lost.';

// Common labels
$lang['remove'] = 'Remove';
$lang['cancel'] = 'Cancel';