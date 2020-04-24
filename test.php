<?php

use local_chunkupload\testmform;

require_once(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/chunkupload/test.php'));

echo $OUTPUT->header();
$mform = new testmform();
$mform->display();
echo $OUTPUT->footer();