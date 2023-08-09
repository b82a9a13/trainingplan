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
$p = 'local_trainingplan';

//Validate inputs and generate error text where required
$title = get_string('trainingplan', $p);
$errorTxt = '';
$e = null;
$uid = null;
$cid = null;
$fullname = '';
if(isset($_GET['e'])){
    $e = $_GET['e'];
    if(($e != 'a' && $e != 'c') || empty($e)){
        $errorTxt .= get_string('invalid_ep', $p);
    } else {
        if(isset($_GET['uid'])){
            $uid = $_GET['uid'];
            if(!preg_match("/^[0-9]*$/", $uid) || empty($uid)){
                $errorTxt .= get_string('invalid_uid', $p);
            } else {
                if(isset($_GET['cid'])){
                    $cid = $_GET['cid'];
                    if(!preg_match("/^[0-9]*$/", $cid) || empty($cid)){
                        $errorTxt .= get_string('invalid_cip', $p);
                    } else {
                        //Successful input validation
                        //Now check capabilities and validate user provided is a learner
                        if($lib->check_coach_course($cid)){
                            $context = context_course::instance($cid);
                            require_capability('local/trainingplan:teacher', $context);
                            $PAGE->set_context($context);
                            $PAGE->set_course($lib->get_course_record($cid));
                            $PAGE->set_url(new moodle_url("/local/trainingplan/plan.php?cid=$cid&uid=$uid"));
                            $PAGE->set_title($title);
                            $PAGE->set_heading($title);
                            $PAGE->set_pagelayout('incourse');
                            $fullname = $lib->check_learner_enrolment($cid, $uid);
                            if($fullname == false){
                                $errorTxt .= get_string('selected_nealic', $p);
                            } else {
                                $_SESSION['tp_trainingplan_uid'] = $uid;
                                $_SESSION['tp_trainingplan_cid'] = $cid;
                                $_SESSION['tp_trainingplan_e'] = $e;
                            }
                        } else {
                            $errorTxt .= get_string('not_eacicp', $p);
                        }
                    }
                } else {
                    $errorTxt .= get_string('no_cip', $p);
                }
            }
        } else {
            $errorTxt .= get_string('no_uip', $p);
        }        
    }
} else {
    $errorTxt .= get_string('no_evp', $p);
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
    echo('<div class="d-flex"><div class="w-50 text-left"><button onclick="window.location.href=`./teacher.php'.$e.'`" class="btn btn-primary" id="plan_btm">'.get_string('btm', 'local_trainingplan').'</button></div><div class="w-50 text-right"><button class="btn btn-primary" onclick="window.open(`./classes/pdf/plan.php?cid='.$cid.'&uid='.$uid.'`,`_blank`)">'.get_string('view_pdf', 'local_trainingplan').'</button></div></div>');
    //Check if a setup exists for the userid and courseid
    if(!$lib->check_learners_setup($uid, $cid)){
        echo("<h1 class='text-error'>".get_string('setup_dneflacp', $p)."</h1>");
    } else {
        $textTemplate = (Object)[
            'trainingplan' => $title,
            'name' => get_string('name', $p),
            'employer_txt' => get_string('employer', $p),
            'start_date' => get_string('start_date', $p),
            'plan_end_date' => get_string('plan_end_date', $p),
            'length_of_prog' => get_string('length_of_prog', $p),
            'otjh_txt' => get_string('otjh', $p),
            'epao_txt' => get_string('epao', $p),
            'fund_source' => get_string('fund_source', $p),
            'choose_epao' => get_string('choose_epao', $p),
            'fr_awards' => get_string('fr_awards', $p),
            'c_and_g' => get_string('c_and_g', $p),
            'innovate_txt' => get_string('innovate', $p),
            'dsw_txt' => get_string('dsw', $p),
            'nocn_txt' => get_string('nocn', $p),
            'choose_fund_source' => get_string('choose_fund_source', $p),
            'contrib_five' => get_string('contrib_five', $p),
            'levy_txt' => get_string('levy', $p),
            'initial_assess' => get_string('initial_assess', $p),
            'bksb_rm' => get_string('bksb_rm', $p),
            'bksb_re' => get_string('bksb_re', $p),
            'learn_style' => get_string('learn_style', $p),
            'skill_scan_lr' => get_string('skill_scan_lr', $p),
            'skill_scan_er' => get_string('skill_scan_er', $p),
            'choose_learn_style' => get_string('choose_learn_style', $p),
            'visual_txt' => get_string('visual', $p),
            'auditory_txt' => get_string('auditory', $p),
            'kinaesthetic_txt' => get_string('kinaesthetic', $p),
            'otj_calc' => get_string('otj_calc', $p),
            'appren_hpw' => get_string('appren_hpw', $p),
            'weeks_on_prog' => get_string('weeks_on_prog', $p),
            'less_al' => get_string('less_al', $p),
            'hours_pw' => get_string('hours_pw', $p),
            'asp_and_cg' => get_string('asp_and_cg', $p),
            'area_of_stren' => get_string('area_of_stren', $p),
            'long_tg' => get_string('long_tg', $p),
            'short_tg' => get_string('short_tg', $p),
            'iag' => get_string('iag', $p),
            'rec_of_pl' => get_string('rec_of_pl', $p),
            'modules' => get_string('modules', $p),
            'plan_sd' => get_string('plan_sd', $p),
            'revise_sd' => get_string('revise_sd', $p),
            'plan_ed' => get_string('plan_ed', $p),
            'revise_ed' => get_string('revise_ed', $p),
            'mod_weigh' => get_string('mod_weigh', $p),
            'plan_otjh' => get_string('plan_otjh', $p),
            'method_od' => get_string('method_od', $p),
            'otj_tasks' => get_string('otj_tasks', $p),
            'act_otjh_comp' => get_string('act_otjh_comp', $p),
            'totals' => get_string('totals', $p),
            'required_fs' => get_string('required_fs', $p),
            'func_sd' => get_string('func_sd', $p),
            'func_sd_desc' => get_string('func_sd_desc', $p),
            'func_s' => get_string('func_s', $p),
            'level' => get_string('level', $p),
            'act_ed' => get_string('act_ed', $p),
            'act_ead' => get_string('act_ead', $p),
            'prog_review' => get_string('prog_review', $p),
            'type_or' => get_string('type_or', $p),
            'plan_review' => get_string('plan_review', $p),
            'act_review' => get_string('act_review', $p),
            'learn_employ' => get_string('learn_employ', $p),
            'learner' => get_string('learner', $p),
            'add_new_rec' => get_string('add_new_rec', $p),
            'rem_rec' => get_string('rem_rec', $p),
            'additional_sa' => get_string('additional_sa', $p),
            'change_lt' => get_string('change_lt', $p),
            'date_oc' => get_string('date_oc', $p),
            'log' => get_string('log', $p),
            'submit' => get_string('submit', $p)
        ];
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
            $template = (object)array_merge((array)$template, (array)$textTemplate);
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
                'prarray' => $data[3],
                'addsa' => $data[0][22],
                'logarray' => array_values($data[4]),
                'jsfile' => 'editplan',
                'prbtns' => [[]]
            ];
            $template = (object)array_merge((array)$template, (array)$textTemplate);
            if(!empty($data[2])){
                $template->fsarray = [['block']];
                $tmpTemplate = (Object)[
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
                    'engaead' => $data[2][1][7]
                ];
                $template = (object)array_merge((array)$template, (array)$tmpTemplate);
            }
            if(isset($_SESSION['tp_update_success'])){
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