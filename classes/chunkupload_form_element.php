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
 * Chunkupload form element
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chunkupload;

global $CFG;

use html_writer;
use renderer_base;

require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->libdir . "/pear/HTML/QuickForm/button.php");
require_once($CFG->libdir . '/form/templatable_form_element.php');

/**
 * Chunkupload form element
 *
 * @package   local_chunkupload
 * @copyright 2020 Justus Dieckmann WWU
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chunkupload_form_element extends \HTML_QuickForm_input implements \templatable {
    use \templatable_form_element {
        export_for_template as export_for_template_base;
    }

    /** @var string html for help button, if empty then no help will icon will be dispalyed. */
    public $_helpbutton = '';

    /** @var array options provided to initalize filemanager */
    // PHP doesn't support 'key' => $value1 | $value2 in class definition
    // We cannot do $_options = array('return_types'=> FILE_INTERNAL | FILE_REFERENCE);
    // So I have to set null here, and do it in constructor
    protected $_options = array('maxbytes' => 0, 'accepted_types' => '*');

    /**
     * Constructor
     *
     * @param string $elementName (optional) name of the filepicker
     * @param string $elementLabel (optional) filepicker label
     * @param array $attributes (optional) Either a typical HTML attribute string
     *              or an associative array
     * @param array $options set of options to initalize filepicker
     */
    public function __construct($elementName = null, $elementLabel = null, $attributes = null, $options = null) {
        global $CFG, $PAGE;
        $options = (array) $options;
        foreach ($options as $name => $value) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }
        $this->_type = 'filepicker';
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Returns html for help button.
     *
     * @return string html for help button
     */
    function getHelpButton() {
        return $this->_helpbutton;
    }

    /**
     * Returns type of filepicker element
     *
     * @return string
     */
    function getElementTemplateType() {
        if ($this->_flagFrozen) {
            return 'nodisplay';
        } else {
            return 'default';
        }
    }

    /**
     * Returns HTML for filepicker form element.
     *
     * @return string
     */
    function toHtml() {
        global $CFG, $PAGE, $OUTPUT;
        $id = $this->_attributes['id'];
        $elname = $this->_attributes['name'];
        $showfinishedicon = false;
        $filenamestring = null;

        if ($value = $this->getValue()) {
            global $DB;
            if ($record = $DB->get_record('local_chunkupload_files', ['id' => $value])) {
                if ($record->state == 2) {
                    $filenamestring = $record->filename;
                    $showfinishedicon = true;
                }
            }
        } else {
            $value = $this->create_token();
        }
        if (!$filenamestring) {
            $filenamestring = get_string('choosefile', 'mod_feedback');
        }

        $context = [
                'elid' => $id,
                'elname' => $elname,
                'value' => $value,
                'filenamestring' => $filenamestring,
                'showicon' => $showfinishedicon
        ];

        $html = $OUTPUT->render_from_template('local_chunkupload/filepicker', $context);

        // need these three to filter repositories list
        $accepted_types = $this->_options['accepted_types'] ? $this->_options['accepted_types'] : '*';
        if (!empty($accepted_types) && $accepted_types != '*') {
            $html .= html_writer::tag('p', get_string('filesofthesetypes', 'form'));
            $util = new \core_form\filetypes_util();
            $filetypes = $accepted_types;
            $filetypedescriptions = $util->describe_file_types($filetypes);
            $html .= $OUTPUT->render_from_template('core_form/filetypes-descriptions', $filetypedescriptions);
        }

        $PAGE->requires->js_call_amd('local_chunkupload/chunkupload', 'init', array(
                'elementid' => $id,
                'acceptedTypes' => $accepted_types,
                'maxBytes' => $this->_options['maxbytes'],
                'wwwroot' => $CFG->wwwroot,
                'chunksize' => get_config('local_chunkupload', 'chunksize') * 1024 * 1024
        ));
        return $html;
    }

    /**
     * export uploaded file
     *
     * @param array $submitValues values submitted.
     * @param bool $assoc specifies if returned array is associative
     * @return array
     */
    function exportValue(&$submitValues, $assoc = false) {
        $fileid = $this->_findValue($submitValues);
        if (null === $fileid) {
            $fileid = $this->getValue();
        }

        return $this->_prepareValue($fileid, true);
    }

    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);
        $context['html'] = $this->toHtml();
        return $context;
    }


    /**
     * Check that the file has the allowed type.
     *
     * @param array $value Draft item id with the uploaded files.
     * @return string|null Validation error message or null.
     */
    public function validateSubmitValue($value) {
        global $DB;
        if (is_null($value)) {
            return get_string('nofile', 'error');
        }
        $record = $DB->get_record('local_chunkupload_files', ['id' => $value]);
        if (!$record) {
            return get_string('nofile', 'error');
        }
        if ($record->state != 2) {
            return get_string('uploadnotfinished','local_chunkupload');
        }
        $path = self::get_path_for_id($value);
        if ($path == null || !file_exists($path)) {
            return get_string('nofile', 'error');
        }
        if (filesize($path) > $this->_options['maxbytes']) {
            unlink($path);
            $DB->delete_records('local_chunkupload_files', ['id' => $value]);
            return get_string('errorfiletoobig', $this->_options['maxbytes']);
        }

        $accepted_types = $this->_options['accepted_types'];

        if (!((is_array($accepted_types) and in_array('*', $accepted_types)) or $accepted_types == '*')) {
            $mimetypes = array();
            foreach ($accepted_types as $type) {
                $mimetypes[] = mimeinfo('type', $type);
            }
            $filetype = mime_content_type($path);
            if (!in_array($filetype, $mimetypes)) {
                unlink($path);
                $DB->delete_records('local_chunkupload_files', ['id' => $value]);
                return get_string('invalidfiletype', 'core_repository', $filetype);
            }
        }
        return null;
    }

    public function create_token() {
        global $DB, $PAGE, $USER;

        if ($USER->id == 0) {
            // Ensure guests can't upload.
            return null;
        }

        do {
            $id = random_string(15);
        } while ($DB->record_exists('local_chunkupload_files', ['id' => $id]));

        $record = new \stdClass();
        $record->id = $id;
        $record->userid = $USER->id;
        $record->contextid = $PAGE->context->id;
        $record->maxlength = $this->_options['maxbytes'];
        $record->lastmodified = time();
        $DB->insert_record_raw('local_chunkupload_files', $record, false, false, true);
        return $id;
    }

    public static function get_path_for_id($id) {
        global $CFG;
        if ($id) {
            return "$CFG->dataroot/chunkupload/" . $id;
        } else {
            return null;
        }
    }
}
