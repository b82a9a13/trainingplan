<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
require_login();
$lib = new lib;
$p = 'local_trainingplan';

//Validate inputs and generate error text where required
$errorTxt = '';
$uid = $_GET['uid'];
$cid = $_GET['cid'];
$fullname = '';
if($_GET['uid']){
    if(!preg_match("/^[0-9]*$/", $uid) || empty($uid)){
        $errorTxt .= get_string('invalid_uid', $p);
    } else {
        if($_GET['cid']){
            if(!preg_match("/^[0-9]*$/", $cid) || empty($cid)){
                $errorTxt .= get_string('invalid_cip', $p);
            } else {
                //Successful input validation
                //Now check capabilities and validate user provided is a learner
                if($lib->check_coach_course($cid)){
                    $context = context_course::instance($cid);
                    require_capability('local/trainingplan:teacher', $context);
                    $fullname = $lib->check_learner_enrolment($cid, $uid);
                    if($fullname == false){
                        $errorTxt .= get_string('selected_nealic', $p);
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

if($errorTxt != ''){
    //Output error message
    echo("<h1 class='text-error'>$errorTxt</h1>");
} else {
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/tcpdf/tcpdf.php');
    class MYPDF extends TCPDF{
        public function Header(){
            $this->Image('./../img/ntalogo.png', $this->GetPageWidth() - 32, $this->GetPageHeight() - 22, 30, 20, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        }
        public function Footer(){
    
        }
    }
    $pdf = new MyPDF('P', 'mm', 'A4');
    $pdf->AddPage('P');
    $pdf->setPrintHeader(true);
    $pdf->setFont('Times', 'B', 26);
    $pdf->Cell(0, 0, get_string('trainingplan', $p).' - '.$fullname, 0, 0, 'C', 0, '', 0);
    $pdf->Ln();

    $pdf->setFont('Times', 'B', 11);
    $headStyle = ' bgcolor="#95287A" style="color: #fafafa;"';
    $data = $lib->get_tplan_data($uid, $cid, 'Y-m-d');

    //Table 1
    if($data[0][6] == 'frawards'){
        $data[0][6] = get_string('fr_awards', $p);
    } elseif($data[0][6] == 'candg'){
        $data[0][6] = get_string('c_and_g', $p);
    } elseif($data[0][6] == 'innovate'){
        $data[0][6] = get_string('innovate', $p);
    } elseif($data[0][6] == 'dsw'){
        $data[0][6] = get_string('dsw', $p);
    } elseif($data[0][6] == 'nocn'){
        $data[0][6] = get_string('nocn', $p);
    }
    if($data[0][7] == 'contrib'){
        $data[0][7] = get_string('contrib_five', $p);
    } elseif($data[0][7] == 'levy'){
        $data[0][7] = get_string('levy', $p);
    }
    $startdate = get_string('start_date', $p);
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('name', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('employer', $p).'</b></th>
        <th'.$headStyle.'><b>'.$startdate.'</b></th>
        <th'.$headStyle.'><b>'.get_string('plan_end_date', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('length_of_prog', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('otjh', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('epao', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('fund_source', $p).'</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][0].'</td>
        <td>'.$data[0][1].'</td>
        <td>'.date('d-m-Y',$data[0][2]).'</td>
        <td>'.date('d-m-Y',$data[0][3]).'</td>
        <td>'.$data[0][4].'</td>
        <td>'.$data[0][5].'</td>
        <td>'.$data[0][6].'</td>
        <td>'.$data[0][7].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    //Table 2
    $data[0][10] = ucfirst($data[0][10]);
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('bksb_rm', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('bksb_re', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('learn_style', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('skill_scan_lr', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('skill_scan_er', $p).'</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][8].'</td>
        <td>'.$data[0][9].'</td>
        <td>'.$data[0][10].'</td>
        <td>'.$data[0][11].'</td>
        <td>'.$data[0][12].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    //Table 3
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('appren_hpw', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('weeks_on_prog', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('less_al', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('hours_pw', $p).'</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][13].'</td>
        <td>'.$data[0][14].'</td>
        <td>'.$data[0][15].'</td>
        <td>'.$data[0][16].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    //Table 4
    $html = '<table border="1" cellpadding="2">
        <tr>
            <th width="98px"'.$headStyle.'><b>'.get_string('area_of_stren', $p).'</b></th>
            <td width="440px">'.$data[0][17].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>'.get_string('long_tg', $p).'</b></th>
            <td width="440px">'.$data[0][18].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>'.get_string('short_tg', $p).'</b></th>
            <td width="440px">'.$data[0][19].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>'.get_string('iag', $p).'</b></th>
            <td width="440px">'.$data[0][20].'</td>
        </tr>
    </table>';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    //Table 5
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('rec_of_pl', $p).'</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][21].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    if(!empty($data[2])){
        for($i = 0; $i < 2; $i++){
            for($y = 4; $y < 8; $y++){
                $data[2][$i][$y] = ($data[2][$i][$y]) ? date('d-m-Y', strtotime($data[2][$i][$y])) : $data[2][$i][$y];
            }
        }
        $html = '<table border="1" cellpadding="2"><thead><tr>
            <th'.$headStyle.'><b>'.get_string('func_s', $p).'</b></th>
            <th'.$headStyle.'><b>'.get_string('level', $p).'</b></th>
            <th'.$headStyle.'><b>'.get_string('method_od', $p).'</b></th>
            <th'.$headStyle.'><b>'.$startdate.'</b></th>
            <th'.$headStyle.'><b>'.get_string('plan_ed', $p).'</b></th>
            <th'.$headStyle.'><b>'.get_string('act_ed', $p).'</b></th>
            <th'.$headStyle.'><b>'.get_string('act_ead', $p).'</b></th>
        </tr></thead><tbody>
            <tr>
                <td>'.$data[2][0][1].'</td>
                <td>'.$data[2][0][2].'</td>
                <td>'.$data[2][0][3].'</td>
                <td>'.$data[2][0][4].'</td>
                <td>'.$data[2][0][5].'</td>
                <td>'.$data[2][0][6].'</td>
                <td>'.$data[2][0][7].'</td>
            </tr>
            <tr>
                <td>'.$data[2][1][1].'</td>
                <td>'.$data[2][1][2].'</td>
                <td>'.$data[2][1][3].'</td>
                <td>'.$data[2][1][4].'</td>
                <td>'.$data[2][1][5].'</td>
                <td>'.$data[2][1][6].'</td>
                <td>'.$data[2][1][7].'</td>
            </tr>
        </tbody></table>
        ';
        $pdf->writeHTML($html, true, false, false, false, false, '');
    }

    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('type_or', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('prog_rev', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('act_review', $p).'</b></th>
    </tr></thead><tbody>
    ';
    $learner = get_string('learner', $p);
    $employer = get_string('employer', $p);
    foreach($data[3] as $arr){
        $opt = '';
        if($arr[2] == 'selected'){
            $opt = $learner;
        } elseif($arr[3] == 'selected'){
            $opt = $employer;
        }
        for($i = 4; $i < 6; $i++){
            $arr[$i] = ($arr[$i]) ? date('d-m-Y',strtotime($arr[$i])) : $arr[$i];
        }
        $html .= "<tr>
            <td>$opt</td>
            <td>$arr[4]</td>
            <td>$arr[5]</td>
        </tr>
        ";
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('additional_sa', $p).'</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][22].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    $pdf->AddPage('L');
    //Table 6
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('modules', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('plan_sd', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('revise_sd', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('plan_ed', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('revise_ed', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('mod_weigh', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('plan_otjh', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('method_od', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('otj_tasks', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('act_otjh_comp', $p).'</b></th>
    </tr></thead><tbody>
    ';
    foreach($data[1][0] as $arr){
        for($i = 2; $i < 6; $i++){
            $arr[$i] = ($arr[$i]) ? date('d-m-Y',strtotime($arr[$i])) : $arr[$i];
        }
        $html .= "<tr>
            <td>$arr[1]</td>
            <td>$arr[2]</td>
            <td>$arr[3]</td>
            <td>$arr[4]</td>
            <td>$arr[5]</td>
            <td>$arr[6]</td>
            <td>$arr[7]</td>
            <td>$arr[8]</td>
            <td>$arr[9]</td>
            <td>$arr[10]</td>
        </tr>
        ";
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    $pdf->AddPage('P');
    //Table 7
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>'.get_string('date_oc', $p).'</b></th>
        <th'.$headStyle.'><b>'.get_string('log', $p).'</b></th>
    </tr></thead><tbody>
    ';
    foreach($data[4] as $arr){
        $arr[0] = ($arr[0]) ? date('d-m-Y', strtotime($arr[0])) : $arr[0];
        $html .= "<tr>
            <td>$arr[0]</td>
            <td>$arr[1]</td>
        </tr>
        ";
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    $pdf->Output("Training-Plan_".$lib->get_course_fullname($cid)."_$fullname");
    \local_trainingplan\event\viewed_plan_pdf::create(array('context' => \context_course::instance($cid), 'courseid' => $cid, 'relateduserid' => $uid))->trigger();
}