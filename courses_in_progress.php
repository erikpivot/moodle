<?php
require_once('config.php');
require_once($CFG->libdir . '/completionlib.php');

use core_completion\progress;

global $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("Courses in Progress");
$PAGE->set_heading("Courses in Progress");
$PAGE->set_url($CFG->wwwroot . "/courses_in_progress.php");

echo $OUTPUT->header();
?>
<div class="course-info-text">
<h3>Please be sure not to log out of the website or close the browser window while the course is running.</h3>
<h4><a href="COURSE INSTRUCTIONS.mp4" target="_blank">Click here to watch a short video on how to take your courses</a></h4<br />
</div>
<hr />
<div class="user-course-list">

<div class="user-course-item header">
<div class="user-course-col description">Course Title</div>
<div class="user-course-col credit-hours">Credit Hours</div>
<div class="user-course-col open-course">Play/Resume</div>
<div class="user-course-col purchased">Purchased</div>
<div class="user-course-col expires">Expires</div>
</div>
<?php
// get the user courses they are involved in
$sort = 'visible DESC, sortorder ASC';
$courses = enrol_get_my_courses('*', $sort);
$courseinfo = array();
foreach ($courses as $course) {

    //$completion = new \completion_info($course);
    // get completion percentage
    //$percentage = progress::get_course_progress_percentage($course);
    
     // course completion is based off of whether the scorm module was marked completed
    $course_mod = $DB->get_record('course_modules', array('course' => $course->id, 'module' => 18));
    $completion_state = $DB->get_record('course_modules_completion', array('userid' => $USER->id, 'coursemoduleid' => $course_mod->id, 'completionstate' => 1));
    
    if (empty($completion_state)) {
        // enrollment end date
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
        $enrollment_end = $start_time + 15724800; // add 26 weeks
        // get the scorm activity
        $scorm_info = $DB->get_record('scorm', array('course' => $course->id), 'id');
        $activity = $DB->get_record('course_modules', array('instance' => $scorm_info->id), 'id');
        // get the sco information
        $sco = $DB->get_record('scorm_scoes', array('scorm' => $scorm_info->id, 'scormtype' => 'sco'), 'id,organization');
        
        // do not display expired courses
        if ($enrollment_end > strtotime(date('Y-m-d'))) {

?>
<div class="user-course-item">
<!--<form id="scormviewform<?=$course->id;?>" method="post" action="http://moodledev.dchours.com/mod/scorm/player.php">-->
<div class="user-course-col description">
<?=$course->fullname;?>
</div>
<div class="user-course-col credit-hours">
<?=$course->credithrs;?>
</div>
<div class="user-course-col open-course">
<form id="scormviewform<?=$course->id;?>" method="post" action="http://moodledev.dchours.com/mod/scorm/player.php">
<input type="submit" value="Open Course" class="btn btn-primary">
</form>
</div>
<div class="user-course-col purchased">
<?=date('m/d/Y', $start_time);?>
</div>
<div class="user-course-col expires">
<?=date('m/d/Y', $enrollment_end);?>
</div>
<!--</form>-->
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
    var centerLeft = parseInt((screen.availWidth - 200) / 2);
    var centerTop = parseInt(((screen.availHeight - 150) / 2) - window.outerHeight);
    poptions = poptions + ',width=' + (screen.availWidth - 200) + ',height=' + (screen.availHeight - 150) + 'left=' + centerLeft + ',top=' + centerTop;
    winobj = window.open(launch_url, 'Popup', poptions);
    this.target = 'Popup';
    winobj.addEventListener("unload", function(event) {
        // Onunload is called multiple times in the SCORM window - we only want to handle when it is actually closed.
        setTimeout(function() {
            if (winobj.closed) {
                window.location.href = window.location.href;
            }
        }, 800);  
    });
});

</script>
<?php
    } // end if not expired
} // end if completed

}
?>
</div>
<?php
echo $OUTPUT->footer();
?>