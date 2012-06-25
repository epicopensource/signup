<?php

/**
 * @package    enrol
 * @subpackage signup
 * @copyright  2011 Antonio Duran Terres  {@link http://www.joomdle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


/*
global $DB;
        $signup_instances = $DB->get_records('enrol', array('enrol'=> 'signup'));
foreach ($signup_instances as $si)
{
	$course_id = $si->courseid;
echo $course_id;
}
*/

/*
global $DB;
    $conditions = array ('eventname' => 'user_created', 'component' => 'signup');
    if (!$DB->record_exists ('events_handlers', $conditions))
    {
        $event = 'user_created';
        $handler->eventname = $event;
        $handler->component = 'signup';
        $handler->handlerfile = '/enrol/signup/enrol.php';
        $handler->handlerfunction = serialize ('signup_'.$event);
        $handler->schedule = 'instant';
        $handler->status = 0;

        $DB->insert_record ('events_handlers', $handler);
    }
*/

class enrol_signup_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_signup'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_signup'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_signup'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));


        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_signup'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));


        $mform->addElement('date_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_signup'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);


        $mform->addElement('date_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_signup'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_signup');
            }

        }

        return $errors;
    }
}
