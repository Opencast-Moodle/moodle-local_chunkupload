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

require_login(null, false, null, false, true);

$PAGE->set_context(context_system::instance());

$fid = optional_param("fileid", 0, PARAM_INT);
$continue = optional_param("continue", 0, PARAM_INT);
$start = optional_param("start", null, PARAM_INT);
$end = optional_param("end", null, PARAM_INT);

echo $OUTPUT->header();

$err = new stdClass();

if ($start === null) {
    $err->error = "Param start is missing";
    die(json_encode($err));
}

if ($end === null) {
    $err->error = "Param end is missing";
    die(json_encode($err));
}

$record = $DB->get_record('local_chunkupload_files', ['id' => $fid]);

if (!$record) {
    die("...");
}

if ($record->continuetoken != $continue) {
    die("...");
}

if ($record->currentpos != $start) {
    $err->error = "Some bytes missing";
    die(json_encode($err));
}

if ($record->end > $record->length) {
    die("...");
}

$path = "$CFG->dataroot/chunkupload/" . $fid;

if (!file_exists($path)) {
    die("...");
}

$content = file_get_contents('php://input', false, null, 0, $end - $start);

if (!strlen($content) != $end - $start) {
    die("...");
}

file_put_contents($path, $content, FILE_APPEND);

$record->continuetoken = rand();
// $record->finished = $end == $length;

$DB->update_record('local_chunkupload_files', $record);

$response = new stdClass();
$response->continue = $record->continuetoken;
die(json_encode($response));