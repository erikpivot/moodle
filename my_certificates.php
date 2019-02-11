<?php
require_once('config.php');
require_once($CFG->libdir . '/completionlib.php');

use core_completion\progress;

global $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("My Certificates");
$PAGE->set_heading("My Certificates");
$PAGE->set_url($CFG->wwwroot . "/my_certificates.php");

// download a certificate?
if (!empty($_POST['action'])) {
    // download the certificate that was sent
    $template = $DB->get_record('customcert_templates', array('id' => $_POST['templid']), '*', MUST_EXIST);
    $template = new \mod_customcert\template($template);
    $template->generate_pdf(false, $USER->id);
}

echo $OUTPUT->header();
?>
<div class="user-course-list">
<?php

// get the user courses they are involved in
$sort = 'visible DESC, sortorder ASC';
$courses = enrol_get_my_courses('*', $sort);
$courseinfo = array();
foreach ($courses as $course) {

    //$completion = new \completion_info($course);
    // course completion is based on the percentage
    // if 100 then the course is considered complete (there's only 1 activity that has to be completed)
    //$percentage = progress::get_course_progress_percentage($course);
    
    // course completion is based off of whether the scorm module was marked completed
    $course_mod = $DB->get_record('course_modules', array('course' => $course->id, 'module' => 18));
    $completion_state = $DB->get_record('course_modules_completion', array('userid' => $USER->id, 'coursemoduleid' => $course_mod->id, 'completionstate' => 1));
    
    if (!empty($completion_state)) {
        // find the certificate
        $cert_info = $DB->get_record('customcert', array('course' => $course->id), 'id,templateid');
        
        // see if the certificate has been issued yet
        if (!$DB->record_exists('customcert_issues', array('userid' => $USER->id, 'customcertid' => $cert_info->id))) {
            $customcertissue = new stdClass();
            $customcertissue->customcertid = $cert_info->id;
            $customcertissue->userid = $USER->id;
            $customcertissue->code = \mod_customcert\certificate::generate_code();
            $customcertissue->timecreated = time();
            // Insert the record into the database.
            $DB->insert_record('customcert_issues', $customcertissue);
        }
        
        // get the scorm activity for grabbing the completion information
        $scorm_info = $DB->get_record('scorm', array('course' => $course->id), 'id');
        $activity = $DB->get_record('course_modules', array('instance' => $scorm_info->id, 'module' => 18), 'id');
        
        // get the completion date for the course
        $complete_info = $DB->get_record('course_modules_completion', array('userid' => $USER->id, 'coursemoduleid' => $activity->id), 'timemodified');
?>
<div class="user-course-item">
<div class="user-course-col">
<?=$course->fullname;?>
</div>
<div class="user-course-col">
<form id="coursecert<?=$course->id;?>" method="post">
<input type="hidden" name="templid" value="<?=$cert_info->templateid;?>">
<input type="hidden" name="action" value="download">
<input type="submit" value="Download Certificate" class="btn btn-primary">
</form>
</div>
<div class="user-course-col">
Course Completed: <?=date('m/d/Y', $complete_info->timemodified);?>
</div>
</div>
<?php
    }
}
?>
</div>
<?php 
echo $OUTPUT->footer();
?>