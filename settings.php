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
 * Signup enrolments plugin settings and presets.
 *
 * @package    enrol
 * @subpackage signup
 * @copyright  2011 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {


    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_signup_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_signup/status',
        get_string('status', 'enrol_signup'), get_string('status_desc', 'enrol_signup'), ENROL_INSTANCE_DISABLED, $options));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(get_context_instance(CONTEXT_SYSTEM));
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_signup/roleid',
            get_string('defaultrole', 'enrol_signup'), get_string('defaultrole_desc', 'enrol_signup'), $student->id, $options));
    }

    $settings->add(new admin_setting_configtext('enrol_signup/enrolperiod',
        get_string('enrolperiod', 'enrol_signup'), get_string('enrolperiod_desc', 'enrol_signup'), 0, PARAM_INT));
}
