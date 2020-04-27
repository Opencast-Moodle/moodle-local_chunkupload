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
 * Chunkupload Cleanup Task
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chunkupload\task;

use local_chunkupload\chunkupload_form_element;

/**
 * Chunkupload Cleanup Task
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_files extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('cleanup_task', 'local_chunkupload');
    }

    public function execute() {
        global $DB;
        $config = get_config('local_chunkupload');

        var_dump($config);

        // State 0
        $DB->delete_records_select('local_chunkupload_files', 'state = 0 AND lastmodified < :time',
                array('time' => time() - $config->state0duration));

        // State 1
        $ids = $DB->get_fieldset_select('local_chunkupload_files', 'id',
                'lastmodified < :time AND state = 1', array('time' => time() - $config->state1duration));
        $DB->delete_records_list('local_chunkupload_files', 'id', $ids);
        echo "State 1:";//#############
        var_dump($ids);//#############
        foreach ($ids as $id) {
            $path = chunkupload_form_element::get_path_for_id($id);
            if (file_exists($path)) {
                unlink($path);
            } else {
                var_dump($path);//#############
            }
        }

        // State 2
        $ids = $DB->get_fieldset_select('local_chunkupload_files', 'id',
                'lastmodified < :time AND state = 2', array('time' => time() - $config->state2duration));
        $DB->delete_records_list('local_chunkupload_files', 'id', $ids);
        echo "State 2:";//#############
        var_dump($ids);//#############
        foreach ($ids as $id) {
            $path = chunkupload_form_element::get_path_for_id($id);
            if (file_exists($path)) {
                unlink($path);
            } else {
                var_dump($path);//#############
            }
        }
    }
}
