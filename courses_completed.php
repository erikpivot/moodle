<?php
require_once('config.php');
require_once($CFG->libdir . '/completionlib.php');

use core_completion\progress;

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("Courses Completed");
$PAGE->set_heading("Courses Completed");
$PAGE->set_url($CFG->wwwroot . "/courses_completed.php");

echo $OUTPUT->header();

// get the user courses they are involved in
$sort = 'visible DESC, sortorder ASC';
$courses = enrol_get_my_courses('*', $sort);
$courseinfo = array();
foreach ($courses as $course) {

    $completion = new \completion_info($course);
    if (!empty($completion->is_course_complete($USER->id))) {
?>
       <a href="<?=$CFG->wwwroot;?>/course/view.php?id=<?=$course->id;?>"><?=$course->fullname;?></a><br />
<?php
    }
} 
echo $OUTPUT->footer();
?>