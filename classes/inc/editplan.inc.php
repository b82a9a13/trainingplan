<?php
require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
$lib = new lib();
$returnText = new stdClass();

//Validation regex
$textarea = "/^[a-zA-Z0-9 ,.!'():;\s\-#]*$/";
$date = "/^[0-9\-]*$/";
$number = "/^[0-9]*$/";
$textareaReplace = "/[a-zA-Z0-9 ,.!'():;\s\-#]/";
$numberReplace = "/[0-9]/";

//Validation and Error Checking
$errorarray = [];

$cldate = $_POST['cl_daterequired'];
if($cldate != null && !empty($cldate)){
    if(!preg_match($date, $cldate)){
        array_push($errorarray, ['cl_daterequired', 'Changes Log, Date of Change']);
    } else {
        $cldate = (new DateTime($cldate))->format('U');
    }
} else {
    array_push($errorarray, ['cl_daterequired', 'Changes Log, Date of Change']);
}
$cllog = $_POST['cl_logrequired'];
if(!preg_match($textarea, $cllog) && !empty($cllog)){
    array_push($errorarray, ['cl_logrequired', 'Changes Log, Log:'.preg_replace($textareaReplace, '', $cllog)]);
}

$total = $_POST['mod-total'];
$modArray = [];
if(!preg_match($number, $total) || empty($total)){
    array_push($errorarray, ['mod-total', 'Invalid Module Total']);
} else {
    for($i = 0; $i < $total; $i++){
        $tmp = [$_POST["mod-rsd-$i"], $_POST["mod-red-$i"], $_POST["mod-otjt-$i"]];
        if($tmp[0] != null && !empty($tmp[0])){
            if(!preg_match($date, $tmp[0])){
                array_push($errorarray, ['mod-rsd', 'Modules, Revised Start Date, Row '.($i+1), $i]);
            } else {
                $tmp[0] = (new DateTime($tmp[0]))->format('U');
            }
        }
        if($tmp[1] != null && !empty($tmp[1])){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, ['mod-red', 'Modules, Revised End Date, Row '.($i+1), $i]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        }
        if(!preg_match($textarea, $tmp[2]) || empty($tmp[2])){
            array_push($errorarray, ['mod-otjt', 'Modules, OTJ Tasks, Row '.($i+1).":".preg_replace($textareaReplace, '', $tmp[2]), $i]);
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
    foreach($fsArray as $fsArr){
        $tmp = [$_POST[$fsArr[0]], $_POST[$fsArr[1]], $_POST[$fsArr[2]], $_POST[$fsArr[3]]];
        if($tmp[0] != null && !empty($tmp[0])){
            if(!preg_match($date, $tmp[0])){
                array_push($errorarray, [$fsArr[0], 'Functional Skills, Actual End Date, Row '.$int]);
            } else {
                $tmp[0] = (new DateTime($tmp[0]))->format('U');
            }
        }
        if($tmp[1] != null && !empty($tmp[1])){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, [$fsArr[1], 'Functional Skills, Upskill Start Date, Row '.$int]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        }
        if($tmp[2] != null && !empty($tmp[2])){
            if(!preg_match($date, $tmp[2])){
                array_push($errorarray, [$fsArr[2], 'Functional Skills, Upskill Planned End Date, Row '.$int]);
            } else {
                $tmp[2] = (new DateTime($tmp[2]))->format('U');
            }
        }
        if($tmp[3] != null && !empty($tmp[3])){
            if(!preg_match($date, $tmp[3])){
                array_push($errorarray, [$fsArr[3], 'Functional Skills, Actual End/Achievment Date, Row '.$int]);
            } else {
                $tmp[3] = (new DateTime($tmp[3]))->format('U');
            }
        }
        array_push($fsValues, $tmp);
        $int++;
    }
}


$total = $_POST['pr-total'];
$prArray = [];
if(!preg_match($number, $total) || empty($total)){
    array_push($errorarray, ['pr-total', 'Invalid Progress Review Total']);
} else{
    $newtotal = $_POST['pr-total-new'];
    if(!preg_match($number, $newtotal) || (empty($newtotal) && $newtotal != 0)){
        array_push($errorarray, ['pr-total-new', 'Invalid Progress Review New Total']);
    } else {
        for($i = 0; $i < ($total - $newtotal); $i++){
            $tmp = [$_POST["pr-ar-$i"]];
            if($tmp[0] != null && !empty($tmp[0])){
                if(!preg_match($date, $tmp[0])){
                    array_push($errorarray, ['pr-ar', 'Progress Reviews, Actual Review, Row '.($i+1), $i]);
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
                    array_push($errorarray, ['pr-ar', 'Progress Reviews, Actual Review, Row '.($i+1), $i]);
                } else {
                    $tmp[0] = (new DateTime($tmp[0]))->format('U');
                }
            }
            if(($tmp[1] != 'Learner' && $tmp[1] != 'Employer') || empty($tmp[1])){
                array_push($errorarray, ['pr-type', 'Progress Reviews, Type, Row '.($i+1), $i]);
            }
            if($tmp[2] != null && !empty($tmp[2])){
                if(!preg_match($date, $tmp[2])){
                    array_push($errorarray, ['pr-pr', 'Progress Reviews, Planned Review, Row '.($i+1), $i]);
                } else {
                    $tmp[2] = (new DateTime($tmp[2]))->format('U');
                }
            } else {
                array_push($errorarray, ['pr-pr', 'Progress Reviews, Planned Review, Row '.($i+1), $i]);
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