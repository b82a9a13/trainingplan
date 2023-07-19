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
        $errorTxt .= 'Invalid e character provided.';
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
                            $PAGE->set_url(new moodle_url("/local/trainingplan/trainingplan.php?cid=$cid&uid=$uid"));
                            $PAGE->set_title('Training Plan');
                            $PAGE->set_heading('Training Plan');
                            $PAGE->set_pagelayout('incourse');
                            $fullname = $lib->check_learner_enrolment($cid, $uid);
                            if($fullname == false){
                                $errorTxt .= 'The user selected is not enrolled as a learner in the course selected.';
                            } else {
                                $_SESSION['tp_trainingplan_uid'] = $uid;
                                $_SESSION['tp_trainingplan_cid'] = $cid;
                                $_SESSION['tp_trainingplan_e'] = $e;
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
    echo("<h1 class='text-error'>$errorTxt</h1>");
} else {
    //Create extension for previous page dependant on $e
    if($e == 'a'){
        $e = '';
    } elseif($e == 'c'){
        $e = '?id='.$cid;
    }
    echo('<button onclick="window.location.href=`./teacher.php'.$e.'`" class="btn btn-primary" id="plan_btm">Back to menu</button>');
    //Check if a setup exists for the userid and courseid
    if(!$lib->check_learners_setup($uid, $cid)){
        echo("<h1 class='text-error'>Setup does not exists for the learner and course provided.</h1>");
    } else {
        if(!$lib->check_trainingplan_exists($uid, $cid)){
            //Training Plan does not exist
            $data = $lib->get_setup_tp_data($uid, $cid);
            $template = (Object)[
                'fullname' => $fullname, 
                'learnername' => $fullname,
                'coursename' => $lib->get_course_fullname($cid),
                'epao_default' => 'selected',
                'fund_default' => 'selected',
                'new_plan' => 'required',
                'learnstyle_default' => 'selected',
                'new_disabled' => 'disabled',
                'mathfs' => 'Maths',
                'engfs' => 'English',
                'progreview_default' => 'selected',
                'jsfile' => 'newplan',
                'defualt_fs_tor' => 'BKSB & F2F & Remote',
                'modulesarray' => $data[0],
                'otjh' => $data[1][0],
                'startdate' => date('Y-m-d',$data[1][1]),
                'employer' => $data[1][2],
                'lengthofprog' => $data[1][3],
                'total_otjh' => $data[1][5],
                'total_mw' => $data[1][4],
                'hpw' => $data[1][6],
                'wop' => $data[1][7],
                'annuallw' => $data[1][8],
                'jsprogreview' => 'newprogreview',
                'default_readonly' => 'readonly disabled',
                'prarray' => [[]],
                'logarray' => [[]],
                'fsarray' => [['none']],
                'fsrequired' => [[]]
            ];
            echo $OUTPUT->render_from_template('local_trainingplan/plan', $template);
            \local_trainingplan\event\viewed_plan::create(array('context' => \context_course::instance($cid), 'courseid' => $cid, 'relateduserid' => $uid, 'other' => $e))->trigger();
        } else {
            //Training Plan exists
            $data = $lib->get_tplan_data($uid, $cid, 'Y-m-d');
            array_push($data[4], ['','','required']);
            $template = (Object)[
                'fullname' => $fullname,
                'learnername' => $data[0][0],
                'coursename' => $lib->get_course_fullname($cid),
                'employer' => $data[0][1],
                'startdate' => date('Y-m-d',$data[0][2]),
                'edit_readonly' => 'readonly disabled',
                'default_readonly' => 'readonly disabled',
                'plannedendd' => date('Y-m-d',$data[0][3]),
                'lengthofprog' => $data[0][4],
                'otjh' => $data[0][5],
                $data[0][6] => 'selected',
                $data[0][7] => 'selected',
                'bksbrm' => $data[0][8],
                'bksbre' => $data[0][9],
                $data[0][10] => 'selected',
                'skillscanlr' => $data[0][11],
                'skillscaner' => $data[0][12],
                'hpw' => $data[0][13],
                'wop' => $data[0][14],
                'annuallw' => $data[0][15],
                'hoursperweek' => $data[0][16],
                'aostrength' => $data[0][17],
                'ltgoals' => $data[0][18],
                'stgoals' => $data[0][19],
                'iaguide' => $data[0][20],
                'recopl' => $data[0][21],
                'modarray' => $data[1][0],
                'total_mw' => $data[1][1][0],
                'total_otjh' => $data[1][1][1],
                'mathfs' => $data[2][0][1],
                'mathlevel' => $data[2][0][2],
                'mathmod' => $data[2][0][3],
                'mathsd' => $data[2][0][4],
                'mathped' => $data[2][0][5],
                'mathaed' => $data[2][0][6],
                'mathaead' => $data[2][0][7],
                'engfs' => $data[2][1][1],
                'englevel' => $data[2][1][2],
                'engmod' => $data[2][1][3],
                'engsd' => $data[2][1][4],
                'engped' => $data[2][1][5],
                'engaed' => $data[2][1][6],
                'engaead' => $data[2][1][7],
                'prarray' => $data[3],
                'addsa' => $data[0][22],
                'logarray' => array_values($data[4]),
                'jsfile' => 'editplan',
                'prbtns' => [[]]
            ];
            if(!empty($data[2])){
                $template->fsarray = [['block']];
            }
            if($_SESSION['tp_update_success']){
                $template->successUpdate = [['Successful Update']];
                unset($_SESSION['tp_update_success']);
            } else {
                \local_trainingplan\event\viewed_plan::create(array('context' => \context_course::instance($cid), 'courseid' => $cid, 'relateduserid' => $uid, 'other' => $e))->trigger();
            }
            echo $OUTPUT->render_from_template('local_trainingplan/plan', $template);
        }
    }
}

echo $OUTPUT->footer();