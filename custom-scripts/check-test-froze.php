<?php
/**
 * Checks to see if any users had their test freeze up.
 * A frozen test is determined when the lesson status is marked as
 * 'failed' which does not happen when a user completes a test.
 */
require_once('connect.php');

$conn = connectDB();

if (!$conn) {
    echo "Bad Connection!\n";
}

// find any frozen tests
$sql = "SELECT CONCAT(usr.firstname, \" \", usr.lastname, \" (\", usr.username, \")\") AS username, st.userid, st.scormid, 
            st.value AS coursestarttime, crs.fullname AS course 
            FROM mdl_scorm_scoes_track st
            JOIN mdl_user usr ON st.userid = usr.id
            JOIN mdl_scorm srm ON st.scormid = srm.id
            JOIN mdl_course crs ON srm.course = crs.id
            WHERE st.element = 'x.start.time'
            AND st.userid IN (SELECT userid FROM mdl_scorm_scoes_track WHERE element = 'cmi.core.lesson_status' AND value = 'failed')
            AND st.scormid IN (SELECT scormid FROM mdl_scorm_scoes_track WHERE element = 'cmi.core.lesson_status' AND value = 'failed')";
$result = $conn->query($sql);
echo $result->num_rows . "\n";
while ($row = $result->fetch_object()) {
    // reset the test and log the result
    $sql = "DELETE FROM mdl_scorm_scoes_track WHERE element LIKE 'cmi.interactions_%' 
            AND userid = " . $row->userid . " AND scormid = " . $row->scormid;
    echo $sql . "\n";
    $del_res = $conn->query($sql);
    if ($del_res) {
        // update the test status
        $sql = "UPDATE mdl_scorm_scoes_track SET value = 'incomplete' WHERE element = 'cmi.core.lesson_status' AND 
                userid = " . $row->userid . " AND scormid = " . $row->scormid;
        echo $sql . "\n";
        $up_res = $conn->query($sql);
        // add to the history
        $sql = "INSERT INTO mdl_local_test_reset (removedresult, executeddate) 
                VALUES('" . $conn->real_escape_string($row->username . " Course: " . $row->course) . "', " . time() . ")";
        echo $sql . "\n";
        $ins_res = $conn->query($sql);
    }
}

$conn->close();