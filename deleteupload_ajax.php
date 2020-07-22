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
 * The Web service script that is called from the filepicker front end
 *
 * @package    local_bufferupload
 * @copyright  2020 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../lib/filelib.php');

$id = optional_param("id", null, PARAM_ALPHANUM);

$err = new stdClass();
if (!$id) {
    $PAGE->set_context(context_system::instance());
    echo $OUTPUT->header();
    $err->error = "Parameter id is missing.";
    die(json_encode($err));
}

$record = $DB->get_record('local_chunkupload_files', ['id' => $id]);
if (!$record) {
    $PAGE->set_context(context_system::instance());
    echo $OUTPUT->header();
    $err->error = get_string('tokenexpired', 'local_chunkupload');
    die(json_encode($err));
}

$context = context::instance_by_id($record->contextid, IGNORE_MISSING);
if (!$context) {
    $PAGE->set_context(context_system::instance());
    echo $OUTPUT->header();
    $err->error = "Context for that id not found.";
    die(json_encode($err));
}
$PAGE->set_context($context);
echo $OUTPUT->header();
\local_chunkupload\login_helper::require_login_in_context_ajax($context);

if ($USER->id != $record->userid) {
    $err->error = "Request was made by a different user!";
    die(json_encode($err));
}

$path = \local_chunkupload\chunkupload_form_element::get_path_for_id($id);

if (file_exists($path)) {

    unlink($path);

    $record->currentpos = 0;
    $record->length = 0;
    $record->lastmodified = time();
    $record->state = 0;
    $record->filename = "";
    $DB->update_record('local_chunkupload_files', $record);
}

$response = new stdClass();
die(json_encode($response));
