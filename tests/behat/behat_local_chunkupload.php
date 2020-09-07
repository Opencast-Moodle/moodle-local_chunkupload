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
 * Steps definitions related with the local_chunkupload plugin.
 *
 * @package    local_chunkupload
 * @category   test
 * @copyright  2020 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

/**
 * Steps definitions related with the local_chunkupload plugin.
 *
 * @package    local_chunkupload
 * @category   test
 * @copyright  2020 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_chunkupload extends behat_base {

    /**
     * Behat step to upload a file to a chunkupload.
     * @When /^I upload the "(.*)" file to the "(.*)" chunkupload$/
     * @param string $file path to the file to upload
     * @param string $chunkupload name of the chunkupload
     */
    public function i_upload_the_file_to_the_chunkupload($file, $chunkupload) {
        global $CFG;
        $fileelement = $this->find_file('id_' . $chunkupload. '_file');
        $fileelement->attachFile($CFG->dirroot . '/' . $file);
    }


}