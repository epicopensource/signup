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
 * Signup enrolment plugin installation.
 *
 * @package    enrol
 * @subpackage signup
 * @author     Antonio Duran Terres
 * @copyright  2011 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_signup_install() {
    global $CFG, $DB;

	// Add event handler
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

}
