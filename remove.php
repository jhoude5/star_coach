<?php

require_once '../../config.php';
require_once('forms/remove_form.php');
global $DB, $USER, $SESSION, $OUTPUT, $PAGE;

$userid = $USER->id;

$PAGE->set_url('/local/star_coach/remove.php');
$PAGE->set_context(context_system::instance());

require_login();

$PAGE->set_heading('Student Achievement in Reading');
$PAGE->set_title('Student Achievement in Reading');

$starcourse = $DB->get_record('course', ['shortname'=>'STAR']);
$courseid = $starcourse->id;

$removeform = new local_remove_coach_form();

$starcourse = $DB->get_record('course', ['shortname'=>'STAR']);
$courseid = $starcourse->id;

if($removeform != '') {
    if($removeform->is_cancelled()) {
        redirect('/local/star_coach/view.php?userid='.$SESSION->starcoachuid);
    } else if($fromform = $removeform->get_data()) {
        $DB->delete_records('local_star_coach', array('userid' => $userid));
        redirect('/local/star_coach/view.php', 'Submission removed.');
    }
}
echo $OUTPUT->header();
$removeform->display();
echo $OUTPUT->footer();

