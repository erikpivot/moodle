<?php
require_once('config.php');
require_once($CFG->libdir . '/completionlib.php');

use core_completion\progress;

global $DB;

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("Courses in Progress");
$PAGE->set_heading("Courses in Progress");
$PAGE->set_url($CFG->wwwroot . "/courses_in_progress.php");

echo $OUTPUT->header();

// get the user courses they are involved in
$sort = 'visible DESC, sortorder ASC';
$courses = enrol_get_my_courses('*', $sort);
$courseinfo = array();
foreach ($courses as $course) {

    $completion = new \completion_info($course);
    if (empty($completion->is_course_complete($USER->id))) {
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
?>
<div class="user-course-list">
<form id="scormviewform<?=$course->id;?>" method="post" action="http://moodledev.dchours.com/mod/scorm/player.php">
<?=$course->fullname;?>&nbsp;&nbsp;<input type="submit" value="Open Course" class="btn btn-primary">
&nbsp;&nbsp;
Course Expires: <?=date('m/d/Y', $enrollment_end);?>
<br />
</form>
</div>
<script>
jQuery('#scormviewform<?=$course->id;?>').on('submit', function(e) {
    e.preventDefault();
    var scorm = <?=$scorm_info->id;?>;
    var currentorg = '<?=$sco->organization;?>';
    var sco = <?=$sco->id;?>;
    var launch_url = M.cfg.wwwroot + "/mod/scorm/player.php?a=" + scorm + "&currentorg=" + currentorg + "&scoid=" + sco + "&sesskey=" + M.cfg.sesskey + "&display=popup";
    launch_url += '&mode=normal';
    poptions = 'resizable=yes';
    poptions = poptions + ',width=' + screen.availWidth + ',height=' + screen.availHeight + ',left=0,top=0';
    winobj = window.open(launch_url, 'Popup', poptions);
    this.target = 'Popup';
});
</script>
<?php
    }
} 
echo $OUTPUT->footer();
?>