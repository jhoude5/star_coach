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
 * This file contains the news item block class, based upon local_base.
 *
 * @package    local_star_coach
 * @copyright  2022 Jennifer Aube <jennifer.aube@civicactions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('forms/m29_coach_review.php');
require_once('../studentdata/locallib.php');
require_once('locallib.php');
require_once('../studentdata/locallib.php');

global $DB, $USER, $SESSION, $OUTPUT, $PAGE;

$action = optional_param('action', FALSE, PARAM_TEXT);
$formid = optional_param('formid', FALSE, PARAM_INT);
$participantid = optional_param('userid', FALSE, PARAM_INT);
$userid = $USER->id;

$student = $admin = $trainer = FALSE;
$roles = $DB->get_records('role_assignments', ['userid' => $USER->id]);
foreach ($roles as $r) {
  $role = $DB->get_record('role', ['id' => $r->roleid]);
  if ($role->name == 'Student') {
    $student = true;
  } else {
    $trainer = true;
  }
  if ($role->name == 'Manager') {
    $admin = true;
  }
}

$starcourse = $DB->get_record('course', ['shortname'=>'STAR']);
$courseid = $starcourse->id;

require_login();
$PAGE->set_url('/local/star_coach/form.php');
$PAGE->requires->js('/local/star_instructional/assets/accordion.js');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading('Student Achievement in Reading');
$PAGE->set_title('Coach feedback');
$PAGE->set_pagelayout('starcourse');

$coachform = new local_module29_coach_submission_form();
$formarr = [];
if ($coachform != '') {
  $date = new DateTime();
  //    $participantid = '';
  if ($coachform->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $url = new moodle_url('/local/star_coach/view.php', ['userid' =>  $SESSION->starcoachuid]);
    redirect($url);
  }
  else {
    if ($fromform = $coachform->get_data()) {
      $formid = '';
      foreach ($fromform as $key => $value) {
        $formarr[$key] = $value;
      }
      $formarr['userid'] = $userid;
      $participantid = $formarr['participant_reviewing'];
      $form = $DB->get_record('local_star_coach', ['participantid' => $participantid]);
      if (!$form) {
        $formarr['submission'] = 'Submitted';
        $formarr['participantid'] = $participantid;
        $formarr['date'] = $date->getTimestamp();
        $DB->insert_record('local_star_coach', $formarr);
        $form = $DB->get_record('local_star_coach', ['participantid' => $participantid]);
        $formid = $form->id;

      }
      else {
        $formid = $form->id;
        $participantid = $formarr['participant_reviewing'];
        $formarr['id'] = $formid;
        $DB->update_record('local_star_coach', $formarr);
      }
      $submissionform = new star_coach();
      $submissionform->submit_for_grading($form);
      $url = new moodle_url('/local/star_coach/view.php', [
        'userid' => $participantid,
        'formid' => $form->id,
      ]);
      redirect($url);
    }
    else {
      if ($participantid) {
        $toform = [];
        $form = $DB->get_record('local_star_coach', ['participantid' => $participantid]);
        if ($form) {
          foreach ($form as $key => $value) {
            if ($key == 'participantid') {
              $toform['participant_reviewer'] = $value;
            }
            else {
              $toform[$key] = $value;
            }
          }
          $coachform->set_data($toform);
        }
      }

    }
  }
}

$sectiontitle = 'Section 3';

$sectionurl = '/course/view.php?id='.$courseid.'#section-3';
$PAGE->navbar->add('My courses');
$PAGE->navbar->add('STAR', new moodle_url('/course/view.php', ['id' => $courseid]));
$PAGE->navbar->add($sectiontitle, new moodle_url($sectionurl));
$PAGE->navbar->add('29. Teaching with an Instructional Routine: Coach feedback');

// Student data
$getstudentdata = new studentdata();
$studentdata = $getstudentdata->get_student_data($userid);
$students = new stdClass();
$students->data = array_values($studentdata);
echo $OUTPUT->header();
// Floating student data chart
echo $OUTPUT->render_from_template('local_studentdata/modal_studenttable', $students);

// Do not display student form on initial review
if (isset($form)) {
  // Student data
  $getstudentdata = new studentdata();
  $studentdata = $getstudentdata->get_student_data($participantid);
  $students = new stdClass();
  $students->data = array_values($studentdata);
  if ($userid == $USER->id || $admin) {
    echo $OUTPUT->render_from_template('local_studentdata/instructional_priorities', $students);
  } else {
    echo $OUTPUT->render_from_template('local_studentdata/instructional_priorities_view', $students);
  }
}

$coachform->display();


echo $OUTPUT->footer();

