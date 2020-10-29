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
 * Defines available state_types.
 * @package    local_chunkupload
 * @copyright  2020 Laura Troost, Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_chunkupload;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines available state_types.
 * @package    local_chunkupload
 * @copyright  2020 Laura Troost, Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state_type {

    /** @var string Represents the type for a step subplugin. 0: token generated, not used; 1: file upload started;
     * 2: file upload completed
     */
    const UNUSED_TOKEN_GENERATED = 0;
    /** @var string Represents the type for a trigger subplugin. */
    const UPLOAD_STARTED = 1;
    /** @var string Represents the type for a trigger subplugin. */
    const UPLOAD_COMPLETED = 2;

}
