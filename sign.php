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
$fullname = '';
$e = '';
$cid = '';
$uid = '';
$coach = false;
if(isset($_GET['uid']) && isset($_GET['cid']) && isset($_GET['e'])){
    $e = $_GET['e'];
    $uid = $_GET['uid'];
    $cid = $_GET['cid'];
    $coach = true;
    if(($e != 'a' && $e != 'c') || empty($e)){
        $errorTxt .= 'Invalid character for e provided.';
    } else {
        if(!preg_match("/^[0-9]*$/", $uid) || empty($uid)){
            $errorTxt .= 'Invalid user id provided.';
        } else {
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
                    $PAGE->set_url(new moodle_url("/local/trainingplan/sign.php?cid=$cid&uid=$uid&e=$e"));
                    $PAGE->set_title('Signature Creation');
                    $PAGE->set_heading('Signature Creation');
                    $PAGE->set_pagelayout('incourse');
                    $fullname = $lib->check_learner_enrolment($cid, $uid);
                    if($fullname == false){
                        $errorTxt .= 'The user selected is not enrolled as a learner in the course selected.';
                    } else {
                        //Check if a signature already exists for a setup or if a setup exists
                        if(!$lib->check_setup_exists_coach($uid, $cid)){
                            $errorTxt .= 'Setup for the user and course provided does not exist.';
                        } else {
                            if($lib->check_coach_sign_exists($uid, $cid)){
                                $errorTxt .= 'Coach signature already exists.';
                            } else {
                                $_SESSION['tp_sign_uid'] = $uid;
                                $_SESSION['tp_sign_cid'] = $cid;
                                $_SESSION['tp_sign_e'] = $cid;
                            }
                        }
                    }
                } else {
                    $errorTxt .= 'You are not enrolled as a coach in the course provided.';
                }
            }
        }
    }
} elseif(isset($_GET['cid'])){
    $cid = $_GET['cid'];
    if(!preg_match("/^[0-9]*$/", $cid) || empty($cid)){
        $errorTxt .= 'Invalid course id provided.';
    } else {
        $context = context_course::instance($cid);
        require_capability('local/trainingplan:student', $context);
        $PAGE->set_context($context);
        $PAGE->set_course($lib->get_course_record($cid));
        $PAGE->set_url(new moodle_url("/local/trainingplan/sign.php?cid=$cid"));
        $PAGE->set_title('Signature Creation');
        $PAGE->set_heading('Signature Creation');
        $PAGE->set_pagelayout('incourse');
        $fullname = $lib->check_learner_enrolment($cid, $lib->get_userid());
        if($fullname == false){
            $errorTxt .= 'You are not enrolled as a learner in the course provided.'; 
        } else {
            //Check if a setup exists and if a learner signature exists
            if(!$lib->check_setup_exists_learner($cid)){
                $errorTxt .= 'Your setup for the course provided does not exist.';
            } else {
                if($lib->check_learn_sign_exists($cid)){
                    $errorTxt .= 'Learner signature already exists.';
                } else{
                    $_SESSION['tp_sign_cid'] = $cid;
                }
            }
        }
    }
} else{
    $errorTxt .= 'No parameters provided.';
}

echo $OUTPUT->header();

if($errorTxt != ''){
    //Output error message
    echo("<h1 class='text-center text-error'><b>$errorTxt</b></h1>");
} else {
    $p = 'local_trainingplan';
    if($coach){
        if($e == 'a'){
            $e = '';
        } elseif($e == 'c'){
            $e = '?id='.$cid;
        }
        $template = (Object)[
            'btm' => get_string('btm', $p),
            'signature' => get_string('signature', $p),
            'clear' => get_string('clear', $p),
            'submit' => get_string('submit', $p),
            'btm_ext' => $e,
            'return_page' => 'teacher.php',
            'coach' => 'Coach',
            'fullname' => $fullname,
            'coursename' => $lib->get_course_fullname($cid),
            'role_type' => 'Coach'
        ];
        echo $OUTPUT->render_from_template('local_trainingplan/sign', $template);
    } else {
        $template = (Object)[
            'btm' => get_string('btm', $p),
            'signature' => get_string('signature', $p),
            'clear' => get_string('clear', $p),
            'submit' => get_string('submit', $p),
            'role_type' => 'Learner',
            'coursename' => $lib->get_course_fullname($cid),
            'fullname' => $fullname,
            'return_page' => '../../my/index.php'
        ];
        echo $OUTPUT->render_from_template('local_trainingplan/sign', $template);
    }
}

echo $OUTPUT->footer();