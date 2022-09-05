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

require_once(__DIR__ . '/../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_once($CFG->dirroot . '/local/coursereport/classes/form/edit.php');
require_once($CFG->dirroot . '/local/coursereport/lib.php');


require_login();

if (!has_capability('local/coursereport:view', context_system::instance())) {
    return redirect($CFG->wwwroot . '/my',
        get_string('permission_deny', 'local_coursereport'),
        null, \core\output\notification::NOTIFY_SUCCESS);
}
$id = optional_param('id', 0, PARAM_INT);
$PAGE->set_url(new moodle_url('/local/coursereport/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Report Page');

// To be passed in the constructor of edit form.
$actionurl = new moodle_url('/local/coursereport/index.php');
if ($id) {
    $selectcourses = $DB->get_record('course', array('id' => $id));
    $mform = new edit($actionurl, $selectcourses);
} else {
    $mform = new edit($actionurl);
}

$record = [];
$sql = "SELECT id, fullname FROM {course} ";
$db = $DB->get_records_sql($sql);
$choice = array();
foreach ($db as $d) {
    $choice[$d->id] = $d->fullname;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'local_coursereport'));
$mform->display();
echo "<div class='content'><br><br> </div>";

if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    return redirect($CFG->wwwroot . '/local/coursereport/index.php',
        get_string('cancel', 'local_coursereport'));
} else if ($fromform = $mform->get_data()) {

    if (! ($customfield = $DB->get_record('user_info_field', ['shortname' =>
        get_config('local_coursereport', 'customfield')])) ) {
        moodle_exception('error_no_custom_profile_field_found', 'local_coursereport');
    }
    $array = explode("\n", $customfield->param1);
    $data = [];
    $courseid = $mform->get_data()->selectcourses;
    if (!$DB->get_record('course', ['id' => $courseid])) {
        redirect($CFG->wwwroot . '/local/coursereport/index.php',
            get_string('no_course_select', 'local_coursereport'));
    }
    // In this case you process validated data.
    // $mform->get_data() returns data posted in form.
    $flag = 0;

    foreach ($array as $value) {
        $record = course_pass_rate($courseid, $value, $customfield->id);
        $record['process_rate'] = number_format((float)$record['process_rate'], 2, '.', '');
        $record['passing_rate'] = number_format((float)$record['passing_rate'], 2, '.', '');

        $data[] = $record;
        if ($record['total'] > 0) {
            $flag = 1;
        }
    }

    $config = get_config('local_coursereport');
    $headercolor = $config->formheadercolor;
    $oddtuple = $config->oddtuple;
    $border = $config->border;

    $temp = (object)[
        'guest_data' => array_values($data),
        'course_name' => $choice[$fromform->selectcourses],
        'business_unit_label' => get_string('business_unit_label', 'local_coursereport'),
        'passing_rate_label' => get_string('passing_rate_label', 'local_coursereport'),
        'progress_rate_label' => get_string('progress_rate_label', 'local_coursereport'),
        'completion_duration_label' => get_string('completion_duration_label', 'local_coursereport'),
        'next' => get_string('next', 'local_coursereport'),
        'previous' => get_string('previous', 'local_coursereport'),
        'headercolor' => $headercolor,
        'oddtuple' => $oddtuple,
        'border' => $border
    ];

    if ($data && $flag == 1) {
        $items = ['Stackoverflow', 'StackExchange', 'Webmaster', 'Programmers'];
        echo $OUTPUT->render_from_template('local_coursereport/table', (object)$temp);
    } else {
        echo $OUTPUT->heading(get_string('nothing', 'local_coursereport'));
    }
}

echo $OUTPUT->footer();