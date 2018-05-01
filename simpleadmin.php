<?php
require_once('config.php');

$PAGE->set_context(get_system_context());
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
<a href="<?=$CFG->wwwroot;?>/tag/manage.php?tc=3">Manage Course Categories</a><br /><br />
<h2>States</h2>
<a href="<?=$CFG->wwwroot;?>/local/state_settings/index.php">State Settings</a><br /><br />
<h2>Users</h2>
<a href="<?=$CFG->wwwroot;?>/admin/user.php">User List</a><br />
<a href="<?=$CFG->wwwroot;?>/user/editadvanced.php?id=-1">Add New User</a><br /><br />
<h2>Reports</h2>
<h2>Configuration</h2>
<?php
} else {
    echo "You are not authorized to view this page.";
}
echo $OUTPUT->footer();
