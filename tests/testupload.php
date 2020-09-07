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
 * Test page used in the local_chunkupload behat tests.
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_chunkupload\chunkupload_form_element;
use local_chunkupload\local\tests\testmform;

global $PAGE, $CFG, $OUTPUT;

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/chunkupload/tests/testupload.php'));

// Ensure either PHPUNIT_TEST or BEHAT_SITE_RUNNING are defined or $CFG->istestenviroment = true.
if (!((defined('PHPUNIT_TEST') && PHPUNIT_TEST) || defined('BEHAT_SITE_RUNNING') ||
        (object_property_exists($CFG, 'istestenvironment') && $CFG->istestenvironment))) {
    http_response_code(404);
    die();
}

require_login();

echo $OUTPUT->header();
$mform = new testmform($PAGE->url);
if ($data = $mform->get_data()) {
    if (chunkupload_form_element::is_file_uploaded($data->test)) {
        $path = chunkupload_form_element::get_path_for_id($data->test);
        echo '<code>Hash=' . md5_file($path) . '</code>';
        echo '<br><br><br>';
    }
}
$mform->display();
echo $OUTPUT->footer();