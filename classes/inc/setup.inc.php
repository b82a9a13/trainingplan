<?php
require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
$lib = new lib();
$returnText = new stdClass();

//Get posted values
$totalmonths = $_POST['totalmonths'];
$totalhours = $_POST['totalhours'];
$eors = $_POST['eors'];
$coach = $_POST['coach'];
$morm = $_POST['morm'];
$startdate = new DateTime($_POST['startdate']);
$startdate = $startdate->format('U');
$hpw = $_POST['hpw'];
$alw = $_POST['alw'];
$trainplan = $_POST['trainplan'];
$sign = $_POST['sign'];
$option = $_POST['option'];

$errorarray = [];
//Validate posted values
if(!preg_match("/^[0-9]*$/", $totalmonths) || empty($totalmonths)){
    array_push($errorarray, 'Total Months:'.preg_replace('/[0-9]/','',$totalmonths));
}
if(!preg_match("/^[0-9]*$/", $totalhours) || empty($totalhours)){
    array_push($errorarray, 'Total Hours:'.preg_replace('/[0-9]/','',$totalhours));
}
if(!preg_match("/^[a-z A-Z'\-()0-9]*$/", $eors) || empty($eors)){
    array_push($errorarray, 'Employer or Store:'.preg_replace("/[a-z A-Z'\-()0-9]/","",$eors));
}
if(!preg_match("/^[A-Za-z '\-]*$/", $coach) || empty($coach)){
    array_push($errorarray, 'Coach:'.preg_replace("/[a-zA-Z '\-]/","",$coach));
}
if(!preg_match("/^[a-z A-Z'\-]*$/", $morm) || empty($morm)){
    array_push($errorarray, 'Manager or Mentor:'.preg_replace("/[a-z A-Z'\-]/","",$morm));
}
if(!preg_match("/^[0-9]*$/", $startdate) || empty($startdate)){
    array_push($errorarray, 'Start Date:'.preg_replace('/[0-9]/','',$startdate));
}
if(!preg_match("/^[0-9.]*$/", $hpw) || empty($hpw)){
    array_push($errorarray, 'Hours Per Week:'.preg_replace("/[0-9.]/","",$hpw));
}
if(!preg_match("/^[0-9.]*$/", $alw) || empty($alw)){
    array_push($errorarray, 'Annual Leave Weeks:'.preg_replace("/[0-9.]/","",$alw));
}
$plans = $lib->get_training_plans_names();
if(!in_array($trainplan, $plans) || empty($trainplan)){
    array_push($errorarray, 'Training Plan');
}
if(!preg_match("/^[a-zA-Z0-9+:;\/, =]*$/", $sign) || empty($sign)){
    array_push($errorarray, 'Signature');
}
if(!preg_match("/^[0-9]*$/", $option)){
    array_push($errorarray, 'Option');
}

//Check for an error and if no error occurs proceed to inputing data into the database
if($errorarray != []){
    $returnText->error = $errorarray;
} else{
    $creation = $lib->create_setup([$totalmonths, $totalhours, $eors, $coach, $morm, $startdate, $hpw, $alw, $trainplan, $sign, $option]);
    if($creation){
        $returnText->return = true;
    } else {
        $returnText->error = 'Creation error.';
    }
}
echo(json_encode($returnText));