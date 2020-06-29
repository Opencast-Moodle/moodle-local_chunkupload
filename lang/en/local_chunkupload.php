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
 * Strings for plugin 'local_chunkupload'
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Chunkupload';
$string['setting:chunksize'] = 'Chunksize (MB)';
$string['setting:state0duration'] = 'Duration until unused token is deleted';
$string['setting:state1duration'] = 'Duration until uncompleted fileupload is deleted';
$string['setting:state2duration'] = 'Duration until completed fileupload is deleted';
$string['cleanup_task'] = 'Task to cleanup old tokens and files';

$string['uploadnotfinished'] = 'Upload did not finish!';
$string['tokenexpired'] = 'The Upload-Token has expired. Try refreshing the page to recieve a new one.';