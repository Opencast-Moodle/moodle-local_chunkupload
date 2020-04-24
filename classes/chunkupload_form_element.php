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

use context_user;
use html_writer;
use renderer_base;

require_once($CFG->dirroot . '/repository/lib.php');
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
        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;
        $id = $this->_attributes['id'];
        $elname = $this->_attributes['name'];

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        // need these three to filter repositories list
        $accepted_types = $this->_options['accepted_types'] ? $this->_options['accepted_types'] : '*';

        $html = '<input type="hidden" name="' . $elname . '" id="' . $id . '" value="" class=""/>';
        $html .= '<input type="file" id="' . $id . '_file" class="w-100"/>';//elementid, acceptedTypes, maxBytes, wwwroot, chunksize
        $PAGE->requires->js_call_amd('local_chunkupload/chunkupload', 'init', array(
                'elementid' => $id,
                'acceptedTypes' => $accepted_types,
                'maxBytes' => $this->_options['maxbytes'],
                'wwwroot' => $CFG->wwwroot,
                'chunksize' => 100000
        ));

        if (!empty($accepted_types) && $accepted_types != '*') {
            $html .= html_writer::tag('p', get_string('filesofthesetypes', 'form'));
            $util = new \core_form\filetypes_util();
            $filetypes = $accepted_types;
            $filetypedescriptions = $util->describe_file_types($filetypes);
            $html .= $OUTPUT->render_from_template('core_form/filetypes-descriptions', $filetypedescriptions);
        }

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
        global $USER;

        $draftitemid = $this->_findValue($submitValues);
        if (null === $draftitemid) {
            $draftitemid = $this->getValue();
        }

        // make sure max one file is present and it is not too big
        if (!is_null($draftitemid)) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id DESC', false)) {
                $file = array_shift($files);
                if ($this->_options['maxbytes']
                        and $this->_options['maxbytes'] !== USER_CAN_IGNORE_FILE_SIZE_LIMITS
                        and $file->get_filesize() > $this->_options['maxbytes']) {

                    // bad luck, somebody tries to sneak in oversized file
                    $file->delete();
                }
                foreach ($files as $file) {
                    // only one file expected
                    $file->delete();
                }
            }
        }

        return $this->_prepareValue($draftitemid, true);
    }

    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);
        $context['html'] = $this->toHtml();
        return $context;
    }

    public static function get_unused_chunkupload_id() {
        global $CFG;
        do {
            $id = rand(0, 1000000);
            $path = "$CFG->dataroot/chunkupload/" . $id;
        } while (file_exists($path));
        return $id;
    }
}
