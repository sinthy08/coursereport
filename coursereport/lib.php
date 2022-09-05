<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_plugin (coursereport)
 * @author     2022 Nawaz, Solin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function course_pass_rate($courseid, $businessunit, $customfieldid) {
    global $DB;
    $sql = 'SELECT cc.id,u.id userid, uid.data, cc.*
        FROM mdl_user u
        LEFT JOIN {user_info_data} uid ON uid.userid=u.id
        LEFT JOIN {course_completions} cc ON cc.userid = u.id AND uid.fieldid = :customfieldid
        INNER JOIN {role_assignments} ra ON ra.userid = u.id
        INNER JOIN {context} ct ON ct.id = ra.contextid
        INNER JOIN {course} c ON c.id = ct.instanceid
        INNER JOIN {role} r ON r.id = ra.roleid
    WHERE r.id =5 AND c.id = :courseid AND uid.data = :business_unit';

    $params = array(
        'customfieldid' => $customfieldid,
        'courseid' => $courseid,
        'business_unit' => $businessunit
    );

    $data = $DB->get_records_sql($sql, $params);
    $total = count($data);

    $param = [
        'id' => $courseid
    ];
    $course = $DB->get_record('course', $param);
    $pass = 0;
    $continue = 0;
    $progress = 0;
    $duration = 0;
    foreach ($data as $item) {
        if ($item->timecompleted != null) {
            $date1 = new DateTime(date('Y-m-d H:i:s', $item->timestarted));
            $date2 = new DateTime(date('Y-m-d H:i:s', $item->timecompleted));
            $interval = $date1->diff($date2);
            $duration += $interval->days;
            $pass++;
        }
        if (isset($item->userid)) {
            $progress += get_course_progress_percentage($course, $item->userid);
        }
        $continue++;

    }

    $data = [
        'total' => $total,
        'business_unit' => $businessunit,
        'passing_rate' => $pass > 0 ? ($pass * 100) / $total . '%' : '0%',
        'process_rate' => $progress > 0 ? ($progress / $total) . '%' : '0%',
        'completetion_duration' => ($pass > 0) ? ($duration > 0) ? $duration / $pass . ' ' .
            get_string('days', 'local_coursereport') :
            get_string('nodata', 'local_coursereport') : get_string('nodata', 'local_coursereport')
    ];
    return $data;
}

function get_course_progress_percentage($course, $userid = 0) {
    global $USER, $DB;

    $completion = new \completion_info($course);

    // Before we check how many modules have been completed see if the course has.
    if ($completion->is_course_complete($userid)) {
        return 100;
    }

    // Get the number of modules that support completion.
    $modules = $completion->get_activities();
    $count = count($modules);
    if (!$count) {
        return null;
    }

    // Get the number of modules that have been completed.
    $completed = 0;
    foreach ($modules as $module) {
        $data = $completion->get_data($module, true, $userid);
        $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
    }

    return ($completed / $count) * 100;
}


function local_coursereport_extend_navigation_course(navigation_node $navigation) {
    global $PAGE;

    $node = navigation_node::create(get_string('pluginname', 'local_coursereport'),
        new moodle_url('/local/coursereport/index.php', array('courseid' => $PAGE->course->id)),
        navigation_node::TYPE_SETTING,
        null,
        null,
        new pix_icon('i/competencies', ''));
    // Add the competencies node.
    $navigation->add_node($node);
}
