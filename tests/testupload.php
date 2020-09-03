<?php

require_once(__DIR__ . '/../../../config.php');

use local_chunkupload\chunkupload_form_element;
use local_chunkupload\local\tests\testmform;

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/chunkupload/tests/testupload.php'));

// Ensure either PHPUNIT_TEST or BEHAT_SITE_RUNNING are defined or $CFG->istestenviroment = true
if (!((defined('PHPUNIT_TEST') && PHPUNIT_TEST) || defined('BEHAT_SITE_RUNNING') ||
        (object_property_exists($CFG, 'istestenvironment') && $CFG->istestenvironment))) {
    http_response_code(404);
    die();
}

require_login();

echo $OUTPUT->header();
$mform = new testmform($PAGE->url);
if($data = $mform->get_data()) {
    if (chunkupload_form_element::is_file_uploaded($data->test)) {
        $path = chunkupload_form_element::get_path_for_id($data->test);
        echo '<code>Hash=' . md5_file($path) . '</code>';
        echo '<br><br><br>';
    }
}
$mform->display();
echo $OUTPUT->footer();