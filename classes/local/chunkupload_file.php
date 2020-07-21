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

namespace local_chunkupload\local;

use local_chunkupload\chunkupload_form_element;

defined('MOODLE_INTERNAL') || die();

class chunkupload_file {

    /** @var Token of the chunkupload_file */
    private $token;

    private $filename;

    public function __construct($token) {
        global $DB;
        $record = $DB->get_record('local_chunkupload_files', array('id' => $token));
        if (!$record) {
            throw new \moodle_exception("Chunkupload file does not exist");
        }
        $this->token = $record->id;
        $this->filename = $record->filename;
    }

    public function add_to_curl_request(&$curlrequest, $key) {
        $curlrequest->_tmp_file_post_params[$key] = \curl_file_create($this->get_fullpath(), null, $this->get_filename());
    }

    /**
     * Returns the filename of the file
     * @return string
     */
    public function get_filename() {
        return $this->filename;
    }

    /**
     * Returns the token of the file
     * @return string
     */
    public function get_token() {
        return $this->token;
    }

    /**
     * Returns the token of the file
     * @return string
     */
    public function get_fullpath() {
        return chunkupload_form_element::get_path_for_id($this->get_token());
    }

    /**
     * Dumps file content to page.
     */
    public function readfile() {
        return file_get_contents($this->get_fullpath());
    }

}