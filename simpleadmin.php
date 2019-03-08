<?php
require_once('config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title("Administration");
$PAGE->set_heading("Admin");
$PAGE->set_url($CFG->wwwroot . "/simpleadmin.php");

echo $OUTPUT->header();
$admins = get_admins();
$isadmin = false;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}
if($isadmin) {
?>
<h2>Courses</h2>
<a href="<?=$CFG->wwwroot;?>/course/management.php">Manage Courses</a><br />
<a href="<?=$CFG->wwwroot;?>/local/course_bundles/index.php">Manage Course Bundles</a><br />
<a href="<?=$CFG->wwwroot;?>/tag/manage.php?tc=3">Manage Course Categories</a><br />
<a href="<?=$CFG->wwwroot;?>/local/revise_courses/index.php">Revise All Courses</a><br />
<br />
<h2>States</h2>
<a href="<?=$CFG->wwwroot;?>/local/state_settings/index.php">State Settings</a><br />
<br />
<h2>Users</h2>
<a href="<?=$CFG->wwwroot;?>/admin/user.php">User List</a><br />
<a href="<?=$CFG->wwwroot;?>/user/editadvanced.php?id=-1">Add New User</a><br />
<a href="<?$CFG->wwwroot;?>/local/test_reset/index.php">Reset User Tests</a><br />
<a href="<?$CFG->wwwroot;?>/admin/user/user_bulk.php">Download User Information</a>
<br />
<h2>Help</h2>
<a href="<?=$CFG->wwwroot;?>/local/knowledge_base/index.php">Knowledge Base</a><br />
<br />
<h2>Reports</h2>
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=3">Full Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=4">Life Completions</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=5">PACE Completions</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=2">Total Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=57">Individual Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=58">Bundle Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=1">Student Enrollment Info</a><br />
<!--<a href="<?=$CFG->wwwroot;?>/report/courseenrollments/index.php">Enrollments</a><br />
<a href="<?=$CFG->wwwroot;?>/report/courseinfo/index.php">Course Information</a><br />
<a href="<?=$CFG->wwwroot;?>/report/studentinformation/index.php">Student Information</a><br />-->
<a href="<?=$CFG->wwwroot;?>/report/approvalnumstate/index.php">Approval Numbers By State</a><br />
<br />
<strong>State Course Completions</strong><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=59">Texas Completions</a><br />
<br />
<strong>State Course Registrations</strong><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=6">Alabama Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=7">Alaska Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=8">Arizona Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=9">Arkansas Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=10">California Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=11">Colorado Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=12">Connecticut Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=13">Delaware Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=14">District of Columbia Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=15">Florida Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=16">Georgia Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=17">Hawaii Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=18">Idaho Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=19">Illinois Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=20">Indiana Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=21">Iowa Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=22">Kansas Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=23">Kentucky Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=24">Louisiana Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=25">Maine Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=26">Maryland Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=27">Massachusetts Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=28">Michigan Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=29">Minnesota Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=30">Mississippi Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=31">Missouri Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=32">Montana Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=33">Nebraska Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=34">Nevada Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=35">New Hampshire Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=36">New Jersey Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=37">New Mexico Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=38">New York Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=39">North Carolina Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=40">North Dakota Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=41">Ohio Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=42">Oklahoma Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=43">Oregon Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=44">Pennsylvania Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=45">Rhode Island Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=46">South Carolina Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=47">South Dakota Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=48">Tennessee Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=49">Texas Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=50">Utah Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=51">Vermont Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=52">Virginia Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=53">Washington Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=54">West Virginia Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=55">Wisconsin Course Registrations</a><br />
<a href="<?=$CFG->wwwroot;?>/report/customsql/view.php?id=56">Wyoming Course Registrations</a><br />
<?php
} else {
    echo "You are not authorized to view this page.";
}
echo $OUTPUT->footer();
