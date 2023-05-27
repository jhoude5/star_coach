<?php

require_once("$CFG->libdir/formslib.php");

class local_module29_coach_submission_form extends moodleform {

  //Add elements to form
  public function definition() {
    global $CFG, $USER, $DB;

    $userid = $USER->id;
    $mform = $this->_form;
    $participants = [];
    $participantid = optional_param('userid', FALSE, PARAM_INT);
    $groupmembers = $DB->get_records('groups_members', ['userid' => $userid]);
    foreach ($groupmembers as $groups) {
      $groupid = $groups->groupid;
      $members = $DB->get_records('groups_members', ['groupid' => $groups->groupid]);
      foreach ($members as $member) {
        //                $user = $DB->get_record('user', array('id' => $member->userid));
        $roles = $DB->get_records('role_assignments', ['userid' => $member->userid]);
        foreach ($roles as $r) {
          $currentrole = $DB->get_records('role', ['id' => $r->roleid]);
          foreach ($currentrole as $role) {
            if ($role->name === 'Student' && $member->userid != $USER->id) {
              $userinfo = $DB->get_record('user', ['id' => $member->userid]);
              // Load user profile fields and get starusername field.
              profile_load_custom_fields($userinfo);
              $starusername = $userinfo->username;
              if (isset($userinfo->profile['starusername']) && !empty($userinfo->profile['starusername'])) {
                $starusername = $userinfo->profile['starusername'];
              }
              $participants[$member->userid] = $starusername;
            }
          }
        }


      }
    }
    $form = $DB->get_record('local_star_coach', ['participantid' => $participantid]);
    $select = $mform->addElement('select', 'participant_reviewing', 'Which participant are you reviewing?', $participants, 'width:100');
    if ($form) {
      $select->setSelected($form->participantid);
    }
    $mform->addElement('html', '
            <div class="accordion-item" id="accordion">
                   <h3 class="usa-accordion__heading" id="headingone">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        First Coach Review
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h3>
                        <div id="collapseone" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingone"><div class="accordion-body">
                        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button" type="button" aria-expanded="false">
                        Focus Questions
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse show" aria-labelledby="headingtwo">
                        <div class="accordion-body">
                        <p>List the questions the teacher identified as the focus of this observation. Explain the evidence that you observed pertaining to each question and summarize the discussion you and the teacher had for each question.</p>
                        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                            Focus Question 1
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');
    $mform->addElement('text', 'r1_focus_question1', 'Focus question', 'width:100');
    $mform->setType('r1_focus_question1', PARAM_TEXT);

    $mform->addElement('text', 'r1_evidence1', 'Evidence from observation', 'width:100');
    $mform->setType('r1_evidence1', PARAM_TEXT);

    $mform->addElement('text', 'r1_summary1', 'Discussion Summary', 'width:100');
    $mform->setType('r1_summary1', PARAM_TEXT);

    $mform->addElement('html', '</div></div></div>
        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                            Focus Question 2
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');

    $mform->addElement('text', 'r1_focus_question2', 'Focus question', 'width:100');
    $mform->setType('r1_focus_question2', PARAM_TEXT);

    $mform->addElement('text', 'r1_evidence2', 'Evidence from observation', 'width:100');
    $mform->setType('r1_evidence2', PARAM_TEXT);

    $mform->addElement('text', 'r1_summary2', 'Discussion Summary', 'width:100');
    $mform->setType('r1_summary2', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div>
        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        Focus Question 3
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');

    $mform->addElement('text', 'r1_focus_question3', 'Focus question', 'width:100');
    $mform->setType('r1_focus_question3', PARAM_TEXT);

    $mform->addElement('text', 'r1_evidence3', 'Evidence from observation', 'width:100');
    $mform->setType('r1_evidence3', PARAM_TEXT);

    $mform->addElement('text', 'r1_summary3', 'Discussion Summary', 'width:100');
    $mform->setType('r1_summary3', PARAM_TEXT);

    $mform->addElement('html', '</div></div></div></div></div></div>');
    $mform->addElement('textarea', 'review1_strengths', 'What other strengths did you observe?', 'width:100');
    $mform->setType('review1_strengths', PARAM_TEXT);
    $mform->addElement('textarea', 'review1_suggestions', 'What other suggestions/considerations/questions do you have for this teacher?', 'width:100');
    $mform->setType('review1_suggestions', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div>
            <div class="accordion-item" id="accordion">
                   <h3 class="usa-accordion__heading" id="headingthree">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        Second Coach Review
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h3>
                        <div id="collapsethree" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingthree"><div class="accordion-body">
                        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingfour">
                        <button class="usa-accordion__button" type="button" aria-expanded="false">
                        Focus Questions
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsefour" class="accordion-collapse usa-accordion__content usa-prose collapse show" aria-labelledby="headingfour">
                        <div class="accordion-body">
                        <p>List the questions the teacher identified as the focus of this observation. Explain the evidence that you observed pertaining to each question and summarize the discussion you and the teacher had for each question.</p>
                        <div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        Focus Question 1
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');
    $mform->addElement('text', 'r2_focus_question1', 'Focus question', 'width:100');
    $mform->setType('r2_focus_question1', PARAM_TEXT);

    $mform->addElement('text', 'r2_evidence1', 'Evidence from observation', 'width:100');
    $mform->setType('r2_evidence1', PARAM_TEXT);

    $mform->addElement('text', 'r2_summary1', 'Discussion Summary', 'width:100');
    $mform->setType('r2_summary1', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div>
<div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        Focus Question 2
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');
    $mform->addElement('text', 'r2_focus_question2', 'Focus question', 'width:100');
    $mform->setType('r2_focus_question2', PARAM_TEXT);

    $mform->addElement('text', 'r2_evidence2', 'Evidence from observation', 'width:100');
    $mform->setType('r2_evidence2', PARAM_TEXT);

    $mform->addElement('text', 'r2_summary2', 'Discussion Summary', 'width:100');
    $mform->setType('r2_summary2', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div>
<div class="accordion-item" id="accordion">
                        <h4 class="usa-accordion__heading" id="headingtwo">
                        <button class="usa-accordion__button collapsed" type="button" aria-expanded="false">
                        Focus Question 3
                        <i class="fa fa-plus"></i><i class="fa fa-minus"></i></button></h4>
                        <div id="collapsetwo" class="accordion-collapse usa-accordion__content usa-prose collapse" aria-labelledby="headingtwo">
                        <div class="accordion-body">');
    $mform->addElement('text', 'r2_focus_question3', 'Focus question', 'width:100');
    $mform->setType('r2_focus_question3', PARAM_TEXT);

    $mform->addElement('text', 'r2_evidence3', 'Evidence from observation', 'width:100');
    $mform->setType('r2_evidence3', PARAM_TEXT);

    $mform->addElement('text', 'r2_summary3', 'Discussion Summary', 'width:100');
    $mform->setType('r2_summary3', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div></div></div></div>');
    $mform->addElement('textarea', 'review2_strengths', 'What other strengths did you observe?', 'width:100');
    $mform->setType('review2_strengths', PARAM_TEXT);
    $mform->addElement('textarea', 'review2_suggestions', 'What other suggestions/considerations/questions do you have for this teacher?', 'width:100');
    $mform->setType('review2_suggestions', PARAM_TEXT);
    $mform->addElement('html', '</div></div></div>');

    $this->add_action_buttons(TRUE, 'Save changes');

  }

  //Custom validation should be added here
  public function validation($data, $files) {
    return [];
  }
}

