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
 * Signup enrolment plugin.
 *
 * This plugin allows you to set courses to enrol users to when they sign up to Moodle
 *
 * @package    enrol
 * @subpackage signup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/locallib.php");

class enrol_signup_plugin extends enrol_plugin {
    
    public function enrol_page_hook() {
        global $USER;

        signup_user_created($USER);
    }
    
    public function roles_protected() {
        
        // users with role assign cap may tweak the roles later
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    public function allow_manage(stdClass $instance) {
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'signup') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);
        if (has_capability('enrol/signup:config', $context)) {
            $managelink = new moodle_url('/enrol/signup/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'signup') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);

        $icons = array();

        if (has_capability('enrol/signup:config', $context)) {
            $editlink = new moodle_url("/enrol/signup/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('i/edit', get_string('edit'), 'core', array('class'=>'icon')));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/signup:config', $context)) {
            return NULL;
        }

        return new moodle_url('/enrol/signup/edit.php', array('courseid'=>$courseid));
    }
}



function signup_enrol_user ($username, $course_id, $roleid = 5)
{  
 
    global $CFG, $DB, $PAGE;
    $conditions = array ('username' => $username);
    $user = $DB->get_record('user',$conditions);
    $conditions = array ('id' => $course_id);
    $course = $DB->get_record('course', $conditions);


    // First, check if user is already enroled but suspended, so we just need to enable it

    $conditions = array ('courseid' => $course_id, 'enrol' => 'manual');
    $enrol = $DB->get_record('enrol', $conditions);

    $conditions = array ('username' => $username);
    $user = $DB->get_record('user', $conditions);

    $conditions = array ('enrolid' => $enrol->id, 'userid' => $user->id);
    $ue = $DB->get_record('user_enrolments', $conditions);

    if ($ue)
    {
        // User already enroled but suspended. Just activate enrolment and return
        $ue->status = 0; //active
        $DB->update_record('user_enrolments', $ue);
        return 1;
    }
    if ($CFG->version >= 2011061700)
        $manager = new course_enrolment_manager($PAGE, $course);
    else
        $manager = new course_enrolment_manager($course);

    $instances = $manager->get_enrolment_instances();
    $plugins = $manager->get_enrolment_plugins();
    //$enrolid = 1; //manual

    $today = time();
    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
    $timestart = $today;
    $timeend = 0;

    //$instance = $instances[$enrolid];

    foreach ($instances as $instance)
    {
        if ($instance->enrol == 'signup')
            break;
    }

    $plugin = $plugins['signup'];

	if ($instance->enrolperiod)
        $timeend   = $timestart + $instance->enrolperiod;
        $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);

        return 1;
}

    function signup_user_created ($user)
    {
        global $DB;

        $signup_instances = $DB->get_records('enrol', array('enrol'=> 'signup'));
		foreach ($signup_instances as $si)
		{
			$course_id = $si->courseid;
			signup_enrol_user ($user->username, $course_id);
		}

    }
