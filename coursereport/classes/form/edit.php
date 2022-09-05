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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir."/formslib.php");

class edit extends moodleform {

    protected $data;
    /**
     * constructor.
     */
    public function __construct($actionurl = null, $data = null) {
        $this->data = $data;
        parent::__construct($actionurl);
    }

    /*Add elements to form*/
    public function definition() {
        global $DB;

        $sql = "SELECT id, fullname FROM {course} WHERE NOT id=1";
        $db = $DB->get_records_sql($sql);
        $choice = array();
        $choice[0] = get_string('selectcourse', 'local_coursereport');
        foreach ($db as $d) {
            $choice[$d->id] = $d->fullname;
        }

        $mform = $this->_form;
        $mform->addElement('select', 'selectcourses', get_string('selectcourselabel', 'local_coursereport'), $choice);

        $this->add_action_buttons(true, get_string('buttonlabel', 'local_coursereport'));
    }
}
