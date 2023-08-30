<?php
require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
$lib = new lib();
$returnText = new stdClass();
$p = 'local_trainingplan';

//Validation regex
$textarea = "/^[a-zA-Z0-9 ,.!'():;\s\-#]*$/";
$date = "/^[0-9\-]*$/";
$number = "/^[0-9]*$/";
$textareaReplace = "/[a-zA-Z0-9 ,.!'():;\s\-#]/";
$numberReplace = "/[0-9]/";

//Validation and Error Checking
$errorarray = [];

$clString = get_string('change_lt_short', $p);
$cldate = $_POST['cl_daterequired'];
if($cldate != null && !empty($cldate)){
    if(!preg_match($date, $cldate)){
        array_push($errorarray, ['cl_daterequired', "$clString, ".get_string('date_oc', $p)]);
    } else {
        $cldate = (new DateTime($cldate))->format('U');
    }
} else {
    array_push($errorarray, ['cl_daterequired', "$clString, ".get_string('date_oc', $p)]);
}
$cllog = $_POST['cl_logrequired'];
if(!preg_match($textarea, $cllog) && !empty($cllog)){
    array_push($errorarray, ['cl_logrequired', "$clString, ".get_string('log', $p).":".preg_replace($textareaReplace, '', $cllog)]);
}

$total = $_POST['mod-total'];
$modArray = [];
if(!preg_match($number, $total) || empty($total)){
    array_push($errorarray, ['mod-total', get_string('invalid_mt', $p)]);
} else {
    $strings = [get_string('modules', $p), get_string('row', $p), get_string('revise_sd', $p), get_string('revise_ed', $p), get_string('otj_tasks', $p)];
    for($i = 0; $i < $total; $i++){
        $tmp = [$_POST["mod-rsd-$i"], $_POST["mod-red-$i"], $_POST["mod-otjt-$i"]];
        if($tmp[0] != null && !empty($tmp[0])){
            if(!preg_match($date, $tmp[0])){
                array_push($errorarray, ['mod-rsd', "$strings[0], $strings[2], $strings[1] ".($i+1), $i]);
            } else {
                $tmp[0] = (new DateTime($tmp[0]))->format('U');
            }
        }
        if($tmp[1] != null && !empty($tmp[1])){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, ['mod-red', "$strings[0], $strings[3], $strings[1] ".($i+1), $i]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        }
        if(!preg_match($textarea, $tmp[2]) || empty($tmp[2])){
            array_push($errorarray, ['mod-otjt', "$strings[0], $strings[4], $strings[1] ".($i+1).":".preg_replace($textareaReplace, '', $tmp[2]), $i]);
        }
        array_push($modArray, $tmp);
    }
}

$fsArray = [
    ['mathaed', 'mathaead'],
    ['engaed', 'engaead']
];
$int = 1;
$fsValues = [];
if(isset($_POST[$fsArray[0][0]]) && isset($_POST[$fsArray[1][0]])){
    $strings = [get_string('func_s', $p), get_string('row', $p), get_string('act_ed', $p), get_string('act_ead', $p)];
    foreach($fsArray as $fsArr){
        $tmp = [$_POST[$fsArr[0]], $_POST[$fsArr[1]]];
        if($tmp[0] != null && !empty($tmp[0])){
            if(!preg_match($date, $tmp[0])){
                array_push($errorarray, [$fsArr[0], "$strings[0], $strings[2], $strings[1] ".$int]);
            } else {
                $tmp[0] = (new DateTime($tmp[0]))->format('U');
            }
        }
        if($tmp[1] != null && !empty($tmp[1])){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, [$fsArr[1], "$strings[0], $strings[3], $strings[1] ".$int]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        }
        array_push($fsValues, $tmp);
        $int++;
    }
}


$total = $_POST['pr-total'];
$prArray = [];
if(!preg_match($number, $total) || empty($total)){
    array_push($errorarray, ['pr-total', get_string('invalid_prt', $p)]);
} else{
    $newtotal = $_POST['pr-total-new'];
    if(!preg_match($number, $newtotal) || (empty($newtotal) && $newtotal != 0)){
        array_push($errorarray, ['pr-total-new', get_string('invalid_prnt', $p)]);
    } else {
        $strings = [get_string('prog_review', $p), get_string('row', $p), get_string('act_review', $p), get_string('type_or', $p), get_string('plan_review', $p)];
        for($i = 0; $i < ($total - $newtotal); $i++){
            $tmp = [$_POST["pr-ar-$i"]];
            if($tmp[0] != null && !empty($tmp[0])){
                if(!preg_match($date, $tmp[0])){
                    array_push($errorarray, ['pr-ar', "$strings[0], $strings[2], $strings[1] ".($i+1), $i]);
                } else {
                    $tmp[0] = (new DateTime($tmp[0]))->format('U');
                }
            }
            array_push($prArray, $tmp);
        }
        for($i = ($total - $newtotal); $i < $total; $i++){
            $tmp = [$_POST["pr-ar-$i"], $_POST["pr-type-$i"], $_POST["pr-pr-$i"]];
            if($tmp[0] != null && !empty($tmp[0])){
                if(!preg_match($date, $tmp[0])){
                    array_push($errorarray, ['pr-ar', "$strings[0], $strings[2], $strings[1] ".($i+1), $i]);
                } else {
                    $tmp[0] = (new DateTime($tmp[0]))->format('U');
                }
            }
            if(($tmp[1] != 'Learner' && $tmp[1] != 'Employer') || empty($tmp[1])){
                array_push($errorarray, ['pr-type', "$strings[0], $strings[3], $strings[1] ".($i+1), $i]);
            }
            if($tmp[2] != null && !empty($tmp[2])){
                if(!preg_match($date, $tmp[2])){
                    array_push($errorarray, ['pr-pr', "$strings[0], $strings[4], $strings[1] ".($i+1), $i]);
                } else {
                    $tmp[2] = (new DateTime($tmp[2]))->format('U');
                }
            } else {
                array_push($errorarray, ['pr-pr', "$strings[0], $strings[4], $strings[1] ".($i+1), $i]);
            }
            array_push($prArray, $tmp);
        }
    }
}

if($errorarray != []){
    $returnText->error = $errorarray;
} else{
    $creation = $lib->update_tplan($modArray, $fsValues, $prArray, [$cldate, $cllog]);
    if($creation){
        $returnText->return = true;
    } else{
        $returnText->return = false;
    }
}
echo(json_encode($returnText));