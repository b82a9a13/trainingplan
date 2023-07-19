<?php
require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
$lib = new lib();
$returnText = new stdClass();

//Validation regex
$textarea = "/^[a-zA-Z0-9 ,.!'():;\s\-#]*$/";
$number = "/^[0-9]*$/";
$decimals = "/^[0-9.]*$/";
$date = "/^[0-9\-]*$/";
$numberReplace = "/[0-9]/";
$decimalsReplace = "/[0-9.]/";
$textareaReplace = "/[a-zA-Z0-9 ,.!'():;\s\-#]/";


//Validation and Error Checking
$errorarray = [];
$name = $_POST['name'];
if(!preg_match("/^[a-z A-Z'\-]*$/",$name) || empty($name)){
    array_push($errorarray, ['name', 'Name:'.preg_replace("/[a-z A-Z'\-]/",'',$name)]);
}
$employer = str_replace("($)","&",$_POST['employer']);
if(!preg_match("/^[a-z A-Z'\-&]*$/", $employer) || empty($employer)){
    array_push($errorarray, ['employer', 'Employer:'.preg_replace("/[a-z A-Z'\-&]/",'',$employer)]);
}
$startdate = $_POST['startdate'];
if($startdate != null && !empty($startdate)){
    if(!preg_match($date, $startdate)){
        array_push($errorarray, ['startdate', 'Start Date']);
    } else {
        $startdate = (new DateTime($startdate))->format('U');
    }
} else {
    array_push($errorarray, ['startdate', 'Start Date']);
}
$planenddate = $_POST['planenddate'];
if($planenddate != null && !empty($planenddate)){
    if(!preg_match($date, $planenddate)){
        array_push($errorarray, ['planenddate', 'Planned End Date']);
    } else {
        $planenddate = (new DateTime($planenddate))->format('U');
    }
} else {
    array_push($errorarray, ['planenddate', 'Planned End Date']);
}
$lengthofprog = $_POST['lengthofprog'];
if(!preg_match($number, $lengthofprog) || empty($lengthofprog)){
    array_push($errorarray, ['lengthofprog', 'Length of Programme:'.preg_replace($numberReplace, '', $lengthofprog)]);
}
$otjh = $_POST['otjh'];
if(!preg_match($number, $otjh) || empty($otjh)){
    array_push($errorarray, ['otjh', 'OTJH:'.preg_replace($numberReplace, '', $otjh)]);
}
$epao = $_POST['epao'];
if(!preg_match("/^[a-z A-Z]*$/", $epao) || empty($epao)){
    array_push($errorarray, ['epao', 'EPAO:'.preg_replace("/[a-z A-Z]/", '', $epao)]);
}
$fundsource = $_POST['fundsource'];
if(($fundsource != 'levy' && $fundsource != 'contrib') || empty($fundsource)){
    array_push($errorarray, ['fundsource', 'Funding Source']);
}
$bksbrm = $_POST['bksbrm'];
if(!preg_match($number, $bksbrm) || empty($bksbrm)){
    array_push($errorarray, ['bksbrm', 'BKSB Result Maths:'.preg_replace($numberReplace, '', $bksbrm)]);
}
$bksbre = $_POST['bksbre'];
if(!preg_match($number, $bksbre) || empty($bksbre)){
    array_push($errorarray, ['bksbre', 'BKSB Result English:'.preg_replace($numberReplace, '', $bksbre)]);
}
$learnstyle = $_POST['learnstyle'];
if(($learnstyle != 'visual' && $learnstyle != 'auditory' && $learnstyle != 'kinaesthetic') || empty($learnstyle)){
    array_push($errorarray, ['learnstyle', 'Learning Style']);
}
$skillscanlr = $_POST['skillscanlr'];
if(!preg_match("/^[0-9A-Za-z \/]*$/", $skillscanlr) || empty($skillscanlr)){
    array_push($errorarray, ['skillscanlr', 'Skill Scan Learner Result:'.preg_replace("/[0-9A-Za-z \/]/", '', $skillscanlr)]);
}
$skillscaner = $_POST['skillscaner'];
if(!preg_match("/^[0-9A-Za-z \/]*$/", $skillscaner) || empty($skillscaner)){
    array_push($errorarray, ['skillscaner', 'Skill Scan Employer Result:'.preg_replace("/[0-9A-Za-z \/]/", '', $skillscaner)]);
}
$apprenhpw = $_POST['apprenhpw'];
if(!preg_match($number, $apprenhpw) || empty($apprenhpw)){
    array_push($errorarray, ['apprenhpw', 'Apprentice Hours Per Week:'.preg_replace($numberReplace, '', $apprenhpw)]);
}
$weeksonprog = $_POST['weeksonprog'];
if(!preg_match($number, $weeksonprog) || empty($weeksonprog)){
    array_push($errorarray, ['weeksonprog', 'Weeks on Programme:'.preg_replace($numberReplace, '', $weeksonprog)]);
}
$annualleave = $_POST['annualleave'];
if(!preg_match($decimals, $annualleave) || empty($annualleave)){
    array_push($errorarray, ['annualleave', 'Less Annual Leave:'.preg_replace($decimalsReplace, '', $annualleave)]);
}
$hoursperweek = $_POST['hoursperweek'];
if(!preg_match($number, $hoursperweek) || empty($hoursperweek)){
    array_push($errorarray, ['hoursperweek', 'Hours Per Week:'.preg_replace($numberReplace, '', $hoursperweek)]);
}
$aostrength = $_POST['aostrength'];
if(!preg_match($textarea, $aostrength) || empty($aostrength)){
    array_push($errorarray, ['aostrength', 'Area of strength:'.preg_replace($textareaReplace, '', $aostrength)]);
}
$ltgoals = $_POST['ltgoals'];
if(!preg_match($textarea, $ltgoals) || empty($ltgoals)){
    array_push($errorarray, ['ltgoals', 'Long Term Goals:'.preg_replace($textareaReplace, '', $ltgoals)]);
}
$stgoals = $_POST['stgoals'];
if(!preg_match($textarea, $stgoals) || empty($stgoals)){
    array_push($errorarray, ['stgoals', 'Short Term Goals:'.preg_replace($textareaReplace, '', $stgoals)]);
}
$iaguide = $_POST['iaguide'];
if(!preg_match($textarea, $iaguide) || empty($iaguide)){
    array_push($errorarray, ['iaguide', 'IAG (Information, Advice, Guidance):'.preg_replace($textareaReplace, '', $iaguide)]);
}
$recopl = $_POST['recopl'];
if(!preg_match($textarea, $recopl) || empty($recopl)){
    array_push($errorarray, ['recopl', 'Recognition of Prior Learning:'.preg_replace($textareaReplace, '', $recopl)]);
}
$addsa = $_POST['addsa'];
if(!preg_match($textarea, $addsa)){
    array_push($errorarray, ['addsa', 'Additional Support Arrangements:'.preg_replace($textareaReplace, '', $addsa)]);
}

$fsArray = [
    ['mathfs', 'mathlevel', 'mathmod', 'mathsd', 'mathped'],
    ['engfs', 'englevel', 'engmod', 'engsd', 'engped']
];
$int = 1;
$fsValues = [];
if($_POST['fscheckbox'] === 'true'){
    foreach($fsArray as $fsArr){
        $tmp = [$_POST[$fsArr[0]], $_POST[$fsArr[1]], str_replace("($)","&",$_POST[$fsArr[2]]), $_POST[$fsArr[3]], $_POST[$fsArr[4]]];
        if(($tmp[0] != 'Maths' && $tmp[0] != 'English' && $tmp[0] != 'ICT')){
            array_push($errorarray, [$fsArr[0], 'Functional Skills, Functional Skills, Row '.$int]);
        }
        if(!preg_match($number, $tmp[1])){
            array_push($errorarray, [$fsArr[1], 'Functional Skills, Level, Row '.$int.':'.preg_replace($numberReplace, '', $tmp[1])]);
        }
        if(!preg_match("/^[a-z A-Z&,0-9]*$/", $tmp[2])){
            array_push($errorarray, [$fsArr[2], 'Functional Skills, Method of Delivery, Row '.$int.':'.preg_replace("/[a-z A-Z&,0-9]/", '', $tmp[2])]);
        }
        if($tmp[3] != null && !empty($tmp[3])){
            if(!preg_match($date, $tmp[3])){
                array_push($errorarray, [$fsArr[3], 'Functional Skills, Start Date, Row '.$int]);
            } else {
                $tmp[3] = (new DateTime($tmp[3]))->format('U');
            }
        } else {
            $tmp[3] = null;
        }
        if($tmp[4] != null && !empty($tmp[4])){
            if(!preg_match($date, $tmp[4])){
                array_push($errorarray, [$fsArr[4], 'Functional Skills, Planned End Date, Row '.$int]);
            } else {
                $tmp[4] = (new DateTime($tmp[4]))->format('U');
            }
        } else {
            $tmp[4] = null;
        }
        $int++;
        array_push($fsValues, $tmp);
    }
}

$modTotal = $_POST['mod-total'];
$modArray = [];
if(!preg_match($number, $modTotal) || empty($modTotal)){
    array_push($errorarray, ['mod-total', 'Invalid Module Total']);
} else {
    for($i = 0; $i < $modTotal; $i++){
        $tmp = [str_replace('($)','&',$_POST["mod-m-$i"]), $_POST["mod-psd-$i"], $_POST["mod-ped-$i"], $_POST["mod-mw-$i"], $_POST["mod-potjh-$i"], $_POST["mod-mod-$i"], str_replace('($)','&',$_POST["mod-otjt-$i"])];
        if(!preg_match("/^[a-z A-Z&,0-9\-]*$/", $tmp[0]) || empty($tmp[0])){
            array_push($errorarray, ["mod-m", "Modules, Modules, Row ".($i+1).':'.preg_replace("/[a-z A-Z&,0-9\-]/", '', $tmp[0]), $i]);
        }
        if($tmp[1] != null && !empty($tmp[1])){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, ["mod-psd", "Modules, Planned Start Date, Row ".($i+1), $i]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        } else {
            array_push($errorarray, ["mod-psd", "Modules, Planned Start Date, Row ".($i+1), $i]);
        }
        if($tmp[2] != null && !empty($tmp[2])){
            if(!preg_match($date, $tmp[2])){
                array_push($errorarray, ["mod-ped", "Modules, Planned End Date, Row ".($i+1), $i]);
            } else {
                $tmp[2] = (new DateTime($tmp[2]))->format('U');
            }
        } else {
            array_push($errorarray, ["mod-ped", "Modules, Planned End Date, Row ".($i+1), $i]);
        }
        if(!preg_match($number, $tmp[3]) || empty($tmp[3])){
            array_push($errorarray, ["mod-mw", "Modules, Module Weight, Row ".($i+1).":".preg_replace($numberReplace, '', $tmp[3]), $i]);
        }
        if(!preg_match($decimals, $tmp[4]) || empty($tmp[4])){
            array_push($errorarray, ["mod-potjh", "Modules, Planned OTJH, Row ".($i+1).":".preg_replace($decimalsReplace, '', $tmp[4]), $i]);
        }
        if(!preg_match("/^[a-z A-Z,0-9]*$/", $tmp[5]) || empty($tmp[5])){
            array_push($errorarray, ["mod-mod", "Modules, Method of Delivery, Row ".($i+1).":".preg_replace("/[a-z A-Z,0-9]/", '', $tmp[5]), $i]);
        }
        if(!preg_match($textarea, $tmp[6]) || empty($tmp[6])){
            array_push($errorarray, ["mod-otjt", "Modules, OTJ Tasks, Row ".($i+1).":".preg_replace($textareaReplace, '', $tmp[6]), $i]);
        }
        array_push($modArray, $tmp);
    }
}

$prTotal = $_POST['pr-total'];
$prArray = [];
if(!preg_match($number, $prTotal) || empty($prTotal)){
    array_push($errorarray, ['pr-total', 'Invalid Progress Review Total']);
} else {
    for($i = 0; $i < $prTotal; $i++){
        $tmp = [$_POST["pr-type-$i"], $_POST["pr-pr-$i"]];
        if(($tmp[0] != 'Learner' && $tmp[0] != 'Employer') || empty($tmp[0])){
            array_push($errorarray, ["pr-type", "Progress Review, Type, Row ".($i+1), $i]);
        }
        if(($tmp[1] != null && !empty($tmp[1]))){
            if(!preg_match($date, $tmp[1])){
                array_push($errorarray, ["pr-pr", "Progress Review, Planned Review, Row ".($i+1), $i]);
            } else {
                $tmp[1] = (new DateTime($tmp[1]))->format('U');
            }
        } else {
            array_push($errorarray, ["pr-pr", "Progress Review, Planned Review, Row ".($i+1), $i]);
        }
        array_push($prArray, $tmp);
    }
}

if($errorarray != []){
    $returnText->error = $errorarray;
} else{
    $creation = $lib->create_tplan([
        $name,
        $employer,
        $startdate,
        $planenddate,
        $lengthofprog,
        $otjh,
        $epao,
        $fundsource,
        $bksbrm,
        $bksbre,
        $learnstyle,
        $skillscanlr,
        $skillscaner,
        $apprenhpw,
        $weeksonprog,
        $annualleave,
        $hoursperweek,
        $aostrength,
        $ltgoals,
        $stgoals,
        $iaguide,
        $recopl,
        $addsa
    ],$fsValues, $modArray, $prArray);
    if($creation){
        $returnText->return = true;
    } else {
        $returnText->return = false;
    }
}
echo(json_encode($returnText));