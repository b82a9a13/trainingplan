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

//Validate inputs and generate error text where required
$errorTxt = '';
$uid = $_GET['uid'];
$cid = $_GET['cid'];
$fullname = '';
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
                    $fullname = $lib->check_learner_enrolment($cid, $uid);
                    if($fullname == false){
                        $errorTxt .= 'The user selected is not enrolled as a learner in the course selected.';
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
    $pdf->Cell(0, 0, 'Training Plan - '.$fullname, 0, 0, 'C', 0, '', 0);
    $pdf->Ln();

    $pdf->setFont('Times', 'B', 11);
    $headStyle = ' bgcolor="#95287A" style="color: #fafafa;"';
    $data = $lib->get_tplan_data($uid, $cid, 'Y-m-d');

    //Table 1
    if($data[0][6] == 'frawards'){
        $data[0][6] = 'FR Awards';
    } elseif($data[0][6] == 'candg'){
        $data[0][6] = 'C & G';
    } elseif($data[0][6] == 'innovate'){
        $data[0][6] = 'Innovate';
    } elseif($data[0][6] == 'dsw'){
        $data[0][6] = 'DSW';
    } elseif($data[0][6] == 'nocn'){
        $data[0][6] = 'NOCN';
    }
    if($data[0][7] == 'contrib'){
        $data[0][7] = '5% Contribution';
    } elseif($data[0][7] == 'levy'){
        $data[0][7] = 'Levy';
    }
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>Name</b></th>
        <th'.$headStyle.'><b>Employer</b></th>
        <th'.$headStyle.'><b>Start Date</b></th>
        <th'.$headStyle.'><b>Planned End Date</b></th>
        <th'.$headStyle.'><b>Length of Programme</b></th>
        <th'.$headStyle.'><b>OTJH</b></th>
        <th'.$headStyle.'><b>EPAO</b></th>
        <th'.$headStyle.'><b>Funding Source</b></th>
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
        <th'.$headStyle.'><b>BKSB Result Maths</b></th>
        <th'.$headStyle.'><b>BKSB Result English</b></th>
        <th'.$headStyle.'><b>Learning Style</b></th>
        <th'.$headStyle.'><b>Skill Scan Learner Result</b></th>
        <th'.$headStyle.'><b>Skill Scan Employer Result</b></th>
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
        <th'.$headStyle.'><b>Apprentice Hours Per Week</b></th>
        <th'.$headStyle.'><b>Weeks on Programme</b></th>
        <th'.$headStyle.'><b>Less Annual Leave</b></th>
        <th'.$headStyle.'><b>Hours Per Week</b></th>
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
            <th width="98px"'.$headStyle.'><b>Area of Strength</b></th>
            <td width="440px">'.$data[0][17].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>Long Term Goals</b></th>
            <td width="440px">'.$data[0][18].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>Short Term Goals</b></th>
            <td width="440px">'.$data[0][19].'</td>
        </tr>
        <tr>
            <th width="98px"'.$headStyle.'><b>IAG (Information, Advice, Guidance)</b></th>
            <td width="440px">'.$data[0][20].'</td>
        </tr>
    </table>';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    //Table 5
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>Recognition of Prior Learning (use this section to describe and evidence any prior learning, qualifications, work experience etc).</b></th>
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
            <th'.$headStyle.'><b>Functional Skills</b></th>
            <th'.$headStyle.'><b>Level</b></th>
            <th'.$headStyle.'><b>Method of Delivery</b></th>
            <th'.$headStyle.'><b>Start Date</b></th>
            <th'.$headStyle.'><b>Planned End Date</b></th>
            <th'.$headStyle.'><b>Actual End Date</b></th>
            <th'.$headStyle.'><b>Actual End/Achievment Date</b></th>
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
        <th'.$headStyle.'><b>Type of Review</b></th>
        <th'.$headStyle.'><b>Planned Review</b></th>
        <th'.$headStyle.'><b>Actual Review</b></th>
    </tr></thead><tbody>
    ';
    foreach($data[3] as $arr){
        $opt = '';
        if($arr[2] == 'selected'){
            $opt = 'Learner';
        } elseif($arr[3] == 'selected'){
            $opt = 'Employer';
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
        <th'.$headStyle.'><b>Additional Support Arrangements</b></th>
    </tr></thead><tbody><tr>
        <td>'.$data[0][22].'</td>
    </tr></tbody></table>
    ';
    $pdf->writeHTML($html, true, false, false, false, false, '');

    $pdf->AddPage('L');
    //Table 6
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>Modules</b></th>
        <th'.$headStyle.'><b>Planned Start Date</b></th>
        <th'.$headStyle.'><b>Revised Start Date</b></th>
        <th'.$headStyle.'><b>Planned End Date</b></th>
        <th'.$headStyle.'><b>Revised End Date</b></th>
        <th'.$headStyle.'><b>Module Weighting %</b></th>
        <th'.$headStyle.'><b>Planned OTJH</b></th>
        <th'.$headStyle.'><b>Method of Delivery</b></th>
        <th'.$headStyle.'><b>OTJ Tasks</b></th>
        <th'.$headStyle.'><b>Actual OTJH Completed (as per log)</b></th>
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
    $html = '<table border="1" cellpadding="2"><thead><tr>
        <th'.$headStyle.'><b>Date of Change</b></th>
        <th'.$headStyle.'><b>Log</b></th>
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
}