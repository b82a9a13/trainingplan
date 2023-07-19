<?php
require_once(__DIR__.'/../../../../config.php');
use local_trainingplan\lib;
$lib = new lib();
$returnText = new stdClass();

//Validation and Error Checking
$errorarray = [];
$sign = $_POST['sign'];
if(!preg_match("/^[a-zA-Z0-9+:;\/, =]*$/", $sign) || empty($sign)){
    array_push($errorarray, 'Signature');
}

//Check for an error and if no error occurs proceed to inputing data into the database
if($errorarray != []){
    $returnText->error = $errorarray;
} else{
    $creation = $lib->create_sign($sign);
    if($creation){
        $returnText->return = true;
    } else {
        $returnText->error = [$creation];
    }
}
echo(json_encode($returnText));