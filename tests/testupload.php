<?php

require_once(__DIR__ . '/../../../config.php');

use local_chunkupload\local\tests\testmform;

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/chunkupload/test.php'));

// Ensure either PHPUNIT_TEST or BEHAT_SITE_RUNNING are defined or $CFG->istestenviroment = true
if (!((defined('PHPUNIT_TEST') && PHPUNIT_TEST) || defined('BEHAT_SITE_RUNNING') ||
        (object_property_exists($CFG, 'istestenvironment') && $CFG->istestenvironment))) {
    http_response_code(404);
    die();
}

require_login();

echo $OUTPUT->header();
$mform = new testmform(new moodle_url('/local/chunkupload/test.php'));
var_dump($mform->get_data());
$mform->display();
echo $OUTPUT->footer();