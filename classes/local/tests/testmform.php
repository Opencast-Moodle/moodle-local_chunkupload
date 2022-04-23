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
 * Test moodleform in the local_chunkupload behat tests.
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chunkupload\local\tests;

use MoodleQuickForm;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Test moodleform in the local_chunkupload behat tests.
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testmform extends \moodleform {

    /**
     * Definition of testmform.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        MoodleQuickForm::registerElementType('chunkupload',
                "$CFG->dirroot/local/chunkupload/classes/chunkupload_form_element.php",
                'local_chunkupload\chunkupload_form_element');

        $mform->addElement('chunkupload', 'test', get_string('file'), null,
                array('maxbytes' => 2 * 1024 * 1024, 'accepted_types' => array('.png')));

        $this->add_action_buttons(false, get_string('save'));
    }
}
