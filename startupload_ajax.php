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

use local_chunkupload\chunkupload_form_element;

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../lib/filelib.php');

require_login(null, false, null, false, true);

$PAGE->set_context(context_system::instance());


$length = optional_param("length", 0, PARAM_INT);
$start = optional_param("start", null, PARAM_INT);
$end = optional_param("end", null, PARAM_INT);

echo $OUTPUT->header();

$err = new stdClass();
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

$id = chunkupload_form_element::get_unused_chunkupload_id();
$path = "$CFG->dataroot/chunkupload/" . $id;

$record = new stdClass();
$record->id = $id;
$record->currentpos = $end;
$record->length = $length;
$record->lastmodified = time();
$record->continuetoken = rand();
$record->finished = $end == $length ? 1 : 0;

$DB->insert_record_raw('local_chunkupload_files', $record, false, false, true);
file_put_contents($path, file_get_contents('php://input', false, null, 0, $end));
$response = new stdClass();
$response->fid = $id;
$response->continuetoken = $record->continuetoken;
die(json_encode($response));