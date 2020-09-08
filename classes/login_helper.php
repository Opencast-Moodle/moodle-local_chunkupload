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
 * Chunkupload Login Helper
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chunkupload;

use context;

defined('MOODLE_INTERNAL') || die();

/**
 * Chunkupload Login Helper
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class login_helper {

    /**
     * Requires a user to be logged in in that context.
     * Like /question/editlib.php:require_login_in_context(), but suitable for ajax.
     * @param context $context the context
     */
    public static function require_login_in_context_ajax($context) {
        global $DB;
        global $CFG;
        if ($context && ($context->contextlevel == CONTEXT_COURSE)) {
            require_login($context->instanceid, false, null, false, true);
        } else if ($context && ($context->contextlevel == CONTEXT_MODULE)) {
            if ($cm = $DB->get_record('course_modules', array('id' => $context->instanceid))) {
                if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
                    print_error('invalidcourseid');
                }
                require_course_login($course, false, $cm, false, true);

            } else {
                print_error('invalidcoursemodule');
            }
        } else if ($context && ($context->contextlevel == CONTEXT_SYSTEM)) {
            if (!empty($CFG->forcelogin)) {
                require_login(null, false, null, false, true);
            }

        } else {
            require_login(null, false, null, false, true);
        }
    }
}
