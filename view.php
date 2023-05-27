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
require_once('forms/file_attachment_form.php');
require_once($CFG->dirroot . '/comment/lib.php');
require_once('../studentdata/locallib.php');
require_once('locallib.php');

global $DB, $USER, $SESSION, $OUTPUT, $PAGE;

$formid = optional_param('formid', FALSE, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$action = optional_param('action', FALSE, PARAM_TEXT);
$title = 'Coach feedback';
$course = $DB->get_record('course', ['shortname'=>'STAR']);
$courseid = $course->id;

if(!isset($SESSION->starcoachuid)) {
    $SESSION->starcoachuid = $userid;
}

if($formid) {
    $SESSION->starcoachformid = $formid;
}
require_login();
$context = context_module::instance($courseid);

$PAGE->set_url('/local/star_coach/view.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/star_coach/assets/accordion.js');
$PAGE->set_heading('Student Achievement in Reading');
$PAGE->set_title($title);
$PAGE->set_pagelayout('starcourse');

// Student data
$getstudentdata = new studentdata();
$studentdata = $getstudentdata->get_student_data($userid);
$students = new stdClass();
$students->data = array_values($studentdata);

$participant = FALSE;
$roles = $DB->get_records('role_assignments', ['userid' => $USER->id]);
foreach ($roles as $r) {
  $role = $DB->get_record('role', ['id' => $r->roleid]);
  if ($role->name === 'Student') {
    $participant = TRUE;
  }
}

// Breadcrumbs
$PAGE->navbar->add('My courses');
$PAGE->navbar->add('STAR', new moodle_url('/course/view.php', ['id' => $courseid]));
$groupname = $groupid = '';
$groupmemlist = $DB->get_record_select('groups_members', 'userid = ?', [$userid]);
if (!empty($groupmemlist)) {
  $grouplist = $DB->get_records_select('groups', 'id = ?', [$groupmemlist->groupid]);
  if (!empty($grouplist)) {
    foreach ($grouplist as $group) {
      $groupid = $group->id;
      $groupname = $group->name;
    }
  }
}
$program_team_url = '/report/stardashboard/groups.php?userid=' . $userid . '&groupid=' . $groupid . '&courseid=' . $courseid;
if (!$participant) {
  $PAGE->navbar->add($groupname, new moodle_url($program_team_url));
}
$sectiontitle = 'Section 3';
$sectionurl = $program_team_url . '#section-3';
$PAGE->navbar->add($sectiontitle, new moodle_url($sectionurl));
$PAGE->navbar->add('29. Teaching with an Instructional Routine: Coach feedback');

// Grading form
$updatearr = $data = [];
$date = new DateTime();
$results = new stdClass();
$header = new stdClass();
$submissionform = new star_coach();
$gradeform = new local_module29_coach_submission_form();

// Participant submits portfolio
if ($action == 'submit') {
  $subform = $DB->get_record('local_star_coach', [
    'participantid' => $userid,
    'id' => $formid,
  ]);
  if ($subform) {
    $updatearr['id'] = $subform->id;
    $updatearr['userid'] = $userid;
    $updatearr['date'] = $date->getTimestamp();
    $updatearr['submission'] = 'Submitted';
    $updatearr['status'] = 'Awaiting feedback';
    $DB->update_record('local_star_coach', $updatearr);
    $msg = 'Your coach review has been submitted.';
    $submissionform->submit_for_grading($subform);
  }
  else {
    $msg = 'Something went wrong. Coach review was not updated.';
  }
  $url = new moodle_url('/local/star_coach/view.php', [
    'formid' => $formid,
    'userid' => $userid,
  ]);
  redirect($url, $msg);
}

// Display form values
$subform = $DB->get_record('local_star_coach', ['participantid' => $userid]);
if($subform) {
    $formid = $subform->id;
    foreach ($subform as $key => $value) {
        if ($key === 'submission' && $value == NULL) {
            $updatearr['status'] = 'Not submitted';
        }
        else {
            if ($key === 'date') {
                $date = date('l, F j, Y, g:i A', $value);
                $updatearr['date'] = $date;
            } else if($key == 'userid') {
                $userinfo = $DB->get_record('user', array('id' => $value));
                // Load user profile fields and get starusername field.
                profile_load_custom_fields($userinfo);
                $starusername = $userinfo->username;
                if (isset($userinfo->profile['starusername']) && !empty($userinfo->profile['starusername'])) {
                    $starusername = $userinfo->profile['starusername'];
                }

                $updatearr['creator'] = $starusername;
            }
            else {
                $updatearr[$key] = $value;
            }
        }
    }
}
array_push($data, $updatearr);
// File attachment form
$fileattach = new local_file_attachment_form();
if($fileattach != '') {
    $subform = $DB->get_record('local_star_coach', ['participantid' => $userid]);
    if ($formdata = $fileattach->get_data()) {
        // ... store or update $entry

        file_save_draft_area_files($formdata->attachments, $context->id, 'local_star_coach', 'attachment',
            $subform->id, array('subdirs' => 0, 'maxbytes' => 2097152, 'maxfiles' => 50));
    }else {
        if (empty($entry->id)) {
            $entry = new stdClass;
            $entry->id = $subform->id;
        }

        $draftitemid = file_get_submitted_draft_itemid('attachments');

        file_prepare_draft_area($draftitemid, $context->id, 'local_star_coach', 'attachment', $subform->id,
            array('subdirs' => 0, 'maxbytes' => 2097152, 'maxfiles' => 50));

        $entry->attachments = $draftitemid;

        $fileattach->set_data($entry);
    }

}
// Student data
$getstudentdata = new studentdata();
$studentdata = $getstudentdata->get_student_data($userid);
$students = new stdClass();
$students->data = array_values($studentdata);

echo $OUTPUT->header();
// Floating student data chart
echo $OUTPUT->render_from_template('local_studentdata/modal_studenttable', $students);

// Get participant username details for submission view
$userinfo = $DB->get_record('user', ['id' => $userid]);
// Load user profile fields and get starusername field.
profile_load_custom_fields($userinfo);
$starusername = $userinfo->username;
if (isset($userinfo->profile['starusername']) && !empty($userinfo->profile['starusername'])) {
  $starusername = $userinfo->profile['starusername'];
}
$username = $userdata = [];
$username['username'] = $starusername;
$username['title'] = $title;
$username['formid'] = $formid;
$username['userid'] = $userid;
array_push($userdata, $username);
$header->data = array_values($userdata);

$results->firstreview = array_values($data);
$results->secondreview = array_values($data);
$results->data = array_values($data);

// Comments
comment::init();
$coachform = $DB->get_record('local_star_coach', ['userid' => $userid, 'id' => $formid]);
$options = new stdClass();
$options->area    = 'submission_comments';
$options->course    = $course;
$options->context = $context;
$options->itemid  = $formid;
$options->showcount = true;
$options->component = 'local_star_coach';
$options->displaycancel = true;

$comment = new comment($options);

echo $OUTPUT->render_from_template('local_star_coach/gradingheader', $header);

if($participant) {
    // Participant view
    if ($userid == $USER->id) {
      echo $OUTPUT->render_from_template('local_studentdata/instructional_priorities', $students);
    } else {
      echo $OUTPUT->render_from_template('local_studentdata/instructional_priorities_view', $students);
    }

    if(!empty($updatearr)) {
        echo $OUTPUT->render_from_template('local_star_coach/submissionview', $results);
        echo '<tr><th>Submission Comments</th><td>';
        $comment->output(false);
        $fileattach->display();
        echo '</td></tr>';

        echo $OUTPUT->render_from_template('local_star_coach/coach_review', $results);
        if($userid != $USER->id) {
            echo $OUTPUT->render_from_template('local_star_coach/actionbuttons', $results);
        }
    } else {
        echo '<h2>' . $title . '</h2>
            <p>Submission not created</p>
            <a href="/local/star_coach/form.php" class="btn btn-primary">Add a submission</a>
            ';
    }

} else {
    // Trainer view
    echo $OUTPUT->render_from_template('local_star_coach/trainerview', $results);
    echo $OUTPUT->render_from_template('local_star_coach/coach_review_grade', $results);
    echo '<div class="mt-5">';
    $comment->output(false);
    $fileattach->display();
    echo '</div>';

}
echo $OUTPUT->footer();
