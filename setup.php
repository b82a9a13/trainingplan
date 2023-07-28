<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

require_once(__DIR__.'/../../config.php');
use local_trainingplan\lib;
require_login();
$lib = new lib;

//Validate inputs and generate error text where required
$errorTxt = '';
$e = $_GET['e'];
$uid = $_GET['uid'];
$cid = $_GET['cid'];
$fullname = '';
if($_GET['e']){
    if(($e != 'a' && $e != 'c') || empty($e)){
        $errorTxt .= 'Invalid character for e provided.';
    } else {
        if($_GET['uid']){
            if(!preg_match("/^[0-9]*$/", $uid) || empty($uid)){
                $errorTxt .= 'Invalid user id provided.';
            } else {
                if($_GET['cid']){
                    if(!preg_match("/^[0-9]*$/", $cid) || empty($cid)){
                        $errorTxt .= 'Invalid course id provided.';
                    } else {
                        //Successful input validation
                        //Now check capabilities and validate user provided is a learner
                        if($lib->check_coach_course($cid)){
                            $context = context_course::instance($cid);
                            require_capability('local/trainingplan:teacher', $context);
                            $PAGE->set_context($context);
                            $PAGE->set_course($lib->get_course_record($cid));
                            $PAGE->set_url(new moodle_url("/local/trainingplan/setup.php?cid=$cid&uid=$uid&e=$e"));
                            $PAGE->set_title('Initial Setup');
                            $PAGE->set_heading('Initial Setup');
                            $PAGE->set_pagelayout('incourse');
                            $fullname = $lib->check_learner_enrolment($cid, $uid);
                            if($fullname == false){
                                $errorTxt .= 'The user selected is not enrolled as a learner in the course selected.';
                            } else {
                                $_SESSION['tp_setup_uid'] = $uid;
                                $_SESSION['tp_setup_cid'] = $cid;
                                $_SESSION['tp_setup_e'] = $e;
                            }
                        } else {
                            $errorTxt .= 'You are not enrolled as a coach in the course provided.';
                        }
                    }
                } else {
                    $errorTxt .= 'No course id provided.';
                }
            }
        } else {
            $errorTxt .= 'No user id provided.';
        }
    }
} else {
    $errorTxt .= 'No e value provided.';
}

echo $OUTPUT->header();

if($errorTxt != ''){
    //Output error message
    echo("<h1 class='text-center text-error'><b>$errorTxt</b></h1>");
} else {
    //Create extension for previous page dependant on $e
    if($e == 'a'){
        $e = '';
    } elseif($e == 'c'){
        $e = '?id='.$cid;
    }
    //Create template data and output template
    $p = 'local_trainingplan';
    $template = (Object)[
        'setup' => get_string('setup', $p),
        'btm' => get_string('btm', $p),
        'total_months' => get_string('total_months', $p),
        'total_otjh' => get_string('total_otjh', $p),
        'employ_or_store' => get_string('employ_or_store', $p),
        'coach' => get_string('coach', $p),
        'man_or_men' => get_string('man_or_men', $p),
        'start_date' => get_string('start_date', $p),
        'contract_hpw' => get_string('contract_hpw', $p),
        'annual_lw' => get_string('annual_lw', $p),
        'trainingplan' => get_string('trainingplan', $p),
        'option' => get_string('option', $p),
        'choose_a_plan' => get_string('choose_a_plan', $p),
        'choose_a_option' => get_string('choose_a_option', $p),
        'coach_sign' => get_string('coach_sign', $p),
        'clear' => get_string('clear', $p),
        'submit' => get_string('submit', $p),
        'btm_ext' => $e,
        'fullname' => $fullname,
        'coursename' => $lib->get_course_fullname($cid),
        'filesarray' => array_values($lib->get_training_plans())
    ];
    echo $OUTPUT->render_from_template('local_trainingplan/setup', $template);
}

echo $OUTPUT->footer();