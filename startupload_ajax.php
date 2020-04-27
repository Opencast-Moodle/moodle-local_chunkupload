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
$start = optional_param("start", null, PARAM_INT);
$length = optional_param("length", 0, PARAM_INT);
$end = optional_param("end", null, PARAM_INT);
$filename = optional_param("filename", null, PARAM_FILE);

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
    $err->error = "No record with that id found.";
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
    $err->debug = var_export($record);
    die(json_encode($err));
}

if ($length == 0) {
    $err->error = "Must not be emtpy!";
    die(json_encode($err));
}

if ($start === null) {
    $err->error = "Param start is missing";
    die(json_encode($err));
}

if ($end === null) {
    $err->error = "Param end is missing";
    die(json_encode($err));
}

if ($length > $record->maxlength) {
    $err->error = "File is too long";
    die(json_encode($err));
}

if ($end > $length) {
    $err->error = "Chunk is longer than specified length";
    die(json_encode($err));
}

$path = \local_chunkupload\chunkupload_form_element::get_path_for_id($id);

$record->currentpos = $end;
$record->length = $length;
$record->lastmodified = time();
$record->continuetoken = rand();
$record->state = $end == $length ? 2 : 1;
$record->filename = $filename;
$DB->update_record('local_chunkupload_files', $record);
file_put_contents($path, file_get_contents('php://input', false, null, 0, $end));
$response = new stdClass();
$response->continuetoken = $record->continuetoken;
die(json_encode($response));