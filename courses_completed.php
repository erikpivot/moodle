<?php
require_once('config.php');
require_once($CFG->libdir . '/completionlib.php');

use core_completion\progress;

global $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("Courses Completed");
$PAGE->set_heading("Courses Completed");
$PAGE->set_url($CFG->wwwroot . "/courses_completed.php");

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

<div class="user-course-list">
<div class="user-course-item header">
<div class="user-course-col description">Course Title</div>
<div class="user-course-col open-course">Play/Resume</div>
<div class="user-course-col download-certificate">Download Certificate</div>
<div class="user-course-col purchased">Purchased</div>
<div class="user-course-col completed-date">Completed</div>
<div class="user-course-col expires">Expires</div>
</div>
<?php

// get the user courses they are involved in
$sort = 'visible DESC, sortorder ASC';
$courses = enrol_get_my_courses('*', $sort);
$courseinfo = array();
foreach ($courses as $course) {

    $completion = new \completion_info($course);
    // course completion is based on the percentage
    // if 100 then the course is considered complete (there's only 1 activity that has to be completed)
    $percentage = progress::get_course_progress_percentage($course);
    if (100 == $percentage) {
        // enrollment start date
        $sql = "SELECT ue.timestart
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
              JOIN {user} u ON u.id = ue.userid
             WHERE ue.userid = :userid AND u.deleted = 0";
            $params = array('userid'=>$USER->id, 'courseid'=>$course->id);
        
        $enrollments = $DB->get_records_sql($sql, $params, 0, 1);
        $start_time = 0;
        foreach($enrollments as $enroll) {
            $start_time = $enroll->timestart;
            break;
        }
        // enrollment expiration date
        $enrollment_end = $start_time + 15724800; // add 26 weeks
        
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
        
        // get the scorm activity
        $scorm_info = $DB->get_record('scorm', array('course' => $course->id), 'id');
        $activity = $DB->get_record('course_modules', array('instance' => $scorm_info->id, 'module' => 18), 'id');
        // get the sco information
        $sco = $DB->get_record('scorm_scoes', array('scorm' => $scorm_info->id, 'scormtype' => 'sco'), 'id,organization'); 
        
        // get the completion date for the course
        $complete_info = $DB->get_record('course_modules_completion', array('userid' => $USER->id, 'coursemoduleid' => $activity->id), 'timemodified');
?>
<div class="user-course-item">
<div class="user-course-col description">
<?=$course->fullname;?>
</div>
<div class="user-course-col open-course">
<?php
// check to see if the course can still be viewed
if ($enrollment_end > strtotime(date('Y-m-d'))) {
    // can still view
?>
<form id="scormviewform<?=$course->id;?>" method="post" action="http://moodledev.dchours.com/mod/scorm/player.php">
<input type="submit" value="Open Course" class="btn btn-primary">
</form>
<?php
} else {
    // course expired
    echo "EXPIRED";
}
?>
</div>
<div class="user-course-col download-certificate">
<form id="coursecert<?=$course->id;?>" method="post">
<input type="hidden" name="templid" value="<?=$cert_info->templateid;?>">
<input type="hidden" name="action" value="download">
<input type="submit" value="Download Certificate" class="btn btn-primary">
</form>
</div>
<div class="user-course-col purchased">
<?=date('m/d/Y', $start_time);?>
</div>
<div class="user-course-col completed-date">
<?=date('m/d/Y', $complete_info->timemodified);?>
</div>
<div class="user-course-col expires">
<?=date('m/d/Y', $enrollment_end);?>
</div>
</div>
<script>
jQuery('#scormviewform<?=$course->id;?>').on('submit', function(e) {
    e.preventDefault();
    var scorm = <?=$scorm_info->id;?>;
    var currentorg = '<?=$sco->organization;?>';
    var sco = <?=$sco->id;?>;
    var launch_url = M.cfg.wwwroot + "/mod/scorm/player.php?a=" + scorm + "&currentorg=" + currentorg + "&scoid=" + sco + "&sesskey=" + M.cfg.sesskey + "&display=popup";
    launch_url += '&mode=normal';
    poptions = 'resizable=yes,location=no';
    poptions = poptions + ',width=' + screen.availWidth + ',height=' + screen.availHeight + ',left=0,top=0';
    winobj = window.open(launch_url, 'Popup', poptions);
    this.target = 'Popup';
});
</script>
<?php
    }
}
?>
</div>
<?php 
echo $OUTPUT->footer();
?>