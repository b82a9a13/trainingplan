<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_trainingplan;
use stdClass;

class lib{

    //Get the category id for apprenticeships
    private function get_category_id(){
        global $DB;
        return $DB->get_record_sql('SELECT id FROM {course_categories} WHERE name = ?',['Apprenticeships'])->id;
    }

    //Get current userid
    public function get_userid(){
        global $USER;
        return $USER->id;
    }

    //Get full course name from course id
    public function get_course_fullname($id){
        global $DB;
        return $DB->get_record_sql('SELECT fullname FROM {course} WHERE id = ?',[$id])->fullname;
    }

    //Check if the current user is enrolled as a coach in a apprenticeship course
    public function check_coach(){
        global $DB;
        $records = $DB->get_records_sql('SELECT {user_enrolments}.id as id, {enrol}.courseid as courseid, {course}.fullname as fullname FROM {user_enrolments}
            INNER JOIN {enrol} ON {enrol}.id = {user_enrolments}.enrolid
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE {role_assignments}.roleid IN (3,4) AND {user_enrolments}.userid = ? AND {course}.category = ? AND {user_enrolments}.status = 0 AND {role_assignments}.userid = {user_enrolments}.userid',
        [$this->get_userid(), $this->get_category_id()]);
        $array = [];
        foreach($records as $record){
            array_push($array, [$record->courseid, $record->fullname]);
        }
        return $array;
    }

    //Check if the current user is enrolled in the course provided as a coach
    public function check_coach_course($id){
        global $DB;
        $record = $DB->get_record_sql('SELECT DISTINCT {user_enrolments}.id as id, {enrol}.courseid as courseid FROM {user_enrolments}
            INNER JOIN {enrol} ON {enrol}.id = {user_enrolments}.enrolid
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE {role_assignments}.roleid IN (3,4) AND {user_enrolments}.userid = ? AND {course}.category = ? AND {user_enrolments}.status = 0 AND {role_assignments}.userid = {user_enrolments}.userid AND {course}.id = ?',
        [$this->get_userid(), $this->get_category_id(), $id]);
        if($record->courseid != null){
            return true;
        } else {
            return false;
        }
    }

    //Get the record for a specific course
    public function get_course_record($id){
        global $DB;
        return $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?',[$id]);
    }

    //Get learners for a specific course
    public function get_enrolled_learners($id){
        global $DB;
        if($this->check_coach_course($id)){
            $records = $DB->get_records_sql('SELECT {user_enrolments}.id as id, {user}.firstname as firstname, {user}.lastname as lastname, {user}.id as uid FROM {user_enrolments} 
                INNER JOIN {enrol} ON {enrol}.id = {user_enrolments}.enrolid
                INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
                INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
                INNER JOIN {course} ON {course}.id = {enrol}.courseid 
                INNER JOIN {user} ON {user}.id = {user_enrolments}.userid
                WHERE {enrol}.courseid = ? AND {user_enrolments}.status = 0 AND {role_assignments}.roleid = 5 AND {course}.category = ? AND {user_enrolments}.userid = {role_assignments}.userid',
            [$id, $this->get_category_id()]);
            if(count($records) > 0){
                $array = [];
                foreach($records as $record){
                    //Need to add data for if a setup is created or not
                    $tmpRecord = $DB->get_record_sql('SELECT coachsign FROM {trainingplan_setup} WHERE userid = ? and courseid = ?',[$record->uid, $id]);
                    if($tmpRecord != null){
                        if($tmpRecord->coachsign != '' && $tmpRecord->coachsign != null){
                            array_push($array, [$record->firstname.' '.$record->lastname, $id, $record->uid, true, true]);
                        } else {
                            array_push($array, [$record->firstname.' '.$record->lastname, $id, $record->uid, true, false]);
                        }
                    } else {
                        array_push($array, [$record->firstname.' '.$record->lastname, $id, $record->uid, false, false]);
                    }
                }
                asort($array);
                return $array;
            } else {
                return ['invalid'];
            }
        } else {
            return 'invalid';
        }
    }

    //Check if a userid is enrolled in a course as a learner
    public function check_learner_enrolment($cid, $uid){
        global $DB;
        $record = $DB->get_record_sql('SELECT {user_enrolments}.id as id, {user}.firstname as firstname, {user}.lastname as lastname FROM {user_enrolments} 
            INNER JOIN {enrol} ON {enrol}.id = {user_enrolments}.enrolid
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid 
            INNER JOIN {user} ON {user}.id = {user_enrolments}.userid
            WHERE {enrol}.courseid = ? AND {user_enrolments}.status = 0 AND {role_assignments}.roleid = 5 AND {course}.category = ? AND {user_enrolments}.userid = {role_assignments}.userid AND {user_enrolments}.userid = ?',
        [$cid, $this->get_category_id(), $uid]);
        if($record->firstname != null){
            return $record->firstname.' '.$record->lastname;
        } else {
            return false;
        }
    }

    //Create a new record for a setup, using already validates session variables (uid, cid)
    public function create_setup($data){
        global $DB;
        if(empty($_SESSION['tp_setup_uid']) || empty($_SESSION['tp_setup_cid'])){
            return false;
        }
        $record = new stdClass();
        $record->userid = $_SESSION['tp_setup_uid'];
        $record->courseid = $_SESSION['tp_setup_cid'];
        if($DB->record_exists('trainingplan_setup', [$DB->sql_compare_text('userid') => $record->userid, $DB->sql_compare_text('courseid') => $record->courseid])){
            return false;
        }
        $record->teachid = $this->get_userid();
        $record->totalmonths = $data[0];
        $record->otjhours = $data[1];
        $record->employerorstore = $data[2];
        $record->coach = $data[3];
        $record->managerormentor = $data[4];
        $record->startdate = $data[5];
        $record->hoursperweek = $data[6];
        $record->annuallw = $data[7];
        $record->planfilename = $data[8];
        $record->coachsign = $data[9];
        $record->option = $data[10];
        if($DB->insert_record('trainingplan_setup', $record, true)){
            unset($_SESSION['tp_setup_uid']);
            unset($_SESSION['tp_setup_cid']);
            \local_trainingplan\event\created_setup::create(array('context' => \context_course::instance($record->courseid), 'courseid' => $record->courseid, 'relateduserid' => $record->userid, 'other' => $_SESSION['tp_setup_e']))->trigger();
            unset($_SESSION['tp_setup_e']);
            return true;
        } else {
            return false;
        }
    }

    //Get training plan names and file name
    public function get_training_plans(){
        global $CFG;
        //Get files
        $files = scandir($CFG->dirroot.'/local/trainingplan/templates/json');
        //Remove first two elements
        unset($files[0]);
        unset($files[1]);
        //put relevant data into an array
        $files = array_values($files);
        $filesarray = [];
        foreach($files as $file){
            $json = file_get_contents($CFG->dirroot.'/local/trainingplan/templates/json/'.$file);
            $json = json_decode($json);
            $options = 0;
            foreach($json->modules as $mod){
                if(isset($mod->option1)){
                    $options++;
                } elseif(isset($mod->option2)){
                    $options++;
                }
            }
            array_push($filesarray, [$json->name, $file, $options]);
        }
        return $filesarray;
    }

    //Get training plan file names
    public function get_training_plans_names(){
        global $CFG;
        $files = scandir($CFG->dirroot.'/local/trainingplan/templates/json');
        unset($files[0]);
        unset($files[1]);
        $files = array_values($files);
        return $files;
    }

    //Check if specific userid and courseid have a setup created
    public function check_learners_setup($uid, $cid){
        global $DB;
        if($DB->record_exists('trainingplan_setup', [$DB->sql_compare_text('userid') => $uid, $DB->sql_compare_text('courseid') => $cid])){
            return true;
        } else {
            return false;
        }
    }

    //Check if a training plan already exists for a userid and courseid
    public function check_trainingplan_exists($uid, $cid){
        global $DB;
        if($DB->record_exists('trainingplan_plans', [$DB->sql_compare_text('userid') => $uid, $DB->sql_compare_text('courseid') => $cid])){
            return true;
        } else {
            return false;
        }
    }

    //Get the training plan data for a specific userid and courseid
    public function get_setup_tp_data($uid, $cid){
        global $DB;
        global $CFG;
        $record = $DB->get_record_sql('SELECT planfilename, otjhours, startdate, employerorstore, totalmonths, hoursperweek, option, annuallw FROM {trainingplan_setup} WHERE courseid = ? and userid = ?',[$cid, $uid]);
        $json = json_decode(file_get_contents($CFG->dirroot.'/local/trainingplan/templates/json/'.$record->planfilename))->modules;
        $array = [];
        $moduleWeight = 0;
        $totalOTJH = 0;
        foreach($json as $jso){
            if(isset($jso->option1)){
                if($record->option == 1){
                    foreach($jso->option1 as $opt){
                        $moduleWeight += $opt->mw;
                        $totalOTJH += $record->otjhours * ($opt->mw / 100);
                        array_push($array, [$opt->name, $opt->mw, $opt->mod, $record->otjhours * ($opt->mw / 100)]);
                    }
                }
            } elseif(isset($jso->option2)){
                if($record->option == 2){
                    foreach($jso->option2 as $opt){
                        $moduleWeight += $opt->mw;
                        $totalOTJH += $record->otjhours * ($opt->mw / 100);
                        array_push($array, [$opt->name, $opt->mw, $opt->mod, $record->otjhours * ($opt->mw / 100)]);
                    }
                }
            } else {
                $moduleWeight += $jso->mw;
                $totalOTJH += $record->otjhours * ($jso->mw / 100);
                array_push($array, [$jso->name, $jso->mw, $jso->mod, $record->otjhours * ($jso->mw / 100)]);
            }
        }
        return [$array, [$record->otjhours, $record->startdate, $record->employerorstore, $record->totalmonths, $moduleWeight, $totalOTJH, $record->hoursperweek, round($record->totalmonths * 4.34), $record->annuallw]];
    }

    //Get plan id for a specific userid and courseid
    private function get_planid($uid, $cid){
        global $DB;
        return $DB->get_record_sql('SELECT id FROM {trainingplan_plans} WHERE userid = ? AND courseid = ?',[$uid, $cid])->id;
    }

    //Get the current users full name
    private function get_fullname(){
        global $USER;
        return $USER->firstname .' '. $USER->lastname;
    }

    //Create a training plan for a spefic userid and courseid
    public function create_tplan($allArray, $fsArray, $modArray, $prArray){
        global $DB;
        $uid = $_SESSION['tp_trainingplan_uid'];
        $cid = $_SESSION['tp_trainingplan_cid'];
        if(!$this->check_trainingplan_exists($uid, $cid) && isset($_SESSION['tp_trainingplan_uid']) && isset($_SESSION['tp_trainingplan_cid'])){
            $record = new stdClass();
            $record->userid = $uid;
            $record->courseid = $cid;
            $record->name = $allArray[0];
            $record->employer = $allArray[1];
            $record->startdate = $allArray[2];
            $record->plannedendd = $allArray[3];
            $record->lengthoprog = $allArray[4];
            $record->otjh = $allArray[5];
            $record->epao = $allArray[6];
            $record->fundsource = $allArray[7];
            $record->bksbrm = $allArray[8];
            $record->bksbre = $allArray[9];
            $record->learnstyle = $allArray[10];
            $record->sslearnr = $allArray[11];
            $record->ssemployr = $allArray[12];
            $record->apprenhpw = $allArray[13];
            $record->weekop = $allArray[14];
            $record->annuall = $allArray[15];
            $record->pdhours = $allArray[16];
            $record->areaostren = $allArray[17];
            $record->longtgoal = $allArray[18];
            $record->shorttgoal = $allArray[19];
            $record->iag = $allArray[20];
            $record->recopl = $allArray[21];
            $record->addsa = $allArray[22];
            if($DB->insert_record('trainingplan_plans', $record, true)){
                $planid = $this->get_planid($uid, $cid);
                for($i = 0; $i < count($modArray); $i++){
                    $record = new stdClass();
                    $record->plansid = $planid;
                    $record->modpos = $i;
                    $record->modname = $modArray[$i][0];
                    $record->modpsd = $modArray[$i][1];
                    $record->modped = $modArray[$i][2];
                    $record->modw = $modArray[$i][3];
                    $record->modotjh = $modArray[$i][4];
                    $record->modmod = $modArray[$i][5];
                    $record->modotjt = $modArray[$i][6];
                    $DB->insert_record('trainingplan_plans_modules', $record, true);
                }
                for($i = 0; $i < count($fsArray); $i++){
                    $record = new stdClass();
                    $record->plansid = $planid;
                    $record->fspos = $i;
                    $record->fsname = $fsArray[$i][0];
                    $record->fslevel = $fsArray[$i][1];
                    $record->fsmod = $fsArray[$i][2];
                    $record->fssd = $fsArray[$i][3];
                    $record->fsped = $fsArray[$i][4];
                    $DB->insert_record('trainingplan_plans_fs', $record, true);
                }
                for($i = 0; $i < count($prArray); $i++){
                    $record = new stdClass();
                    $record->plansid = $planid;
                    $record->prpos = $i;
                    $record->prtor = $prArray[$i][0];
                    $record->prpr = $prArray[$i][1];
                    $DB->insert_record('trainingplan_plans_pr', $record, true);
                }
                $record = new stdClass();
                $record->plansid = $planid;
                $record->dateofc = time();
                $record->log = 'Training Plan created by '.$this->get_fullname();
                $DB->insert_record('trainingplan_plans_log', $record, true);
                \local_trainingplan\event\created_plan::create(array('context' => \context_course::instance($_SESSION['tp_trainingplan_cid']), 'courseid' => $_SESSION['tp_trainingplan_cid'], 'relateduserid' => $_SESSION['tp_trainingplan_uid'], 'other' => $_SESSION['tp_trainingplan_e']))->trigger();
                unset($_SESSION['tp_trainingplan_e']);
                unset($_SESSION['tp_trainingplan_uid']);
                unset($_SESSION['tp_trainingplan_cid']);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //Get training plan data
    public function get_tplan_data($uid, $cid, $datetype){
        global $DB;
        $record = $DB->get_record_sql('SELECT * FROM {trainingplan_plans} WHERE userid = ? and courseid = ?',[$uid, $cid]);
        $planid = $record->id;
        $plansArray = [
            $record->name, 
            $record->employer, 
            $record->startdate, 
            $record->plannedendd,
            $record->lengthoprog,
            $record->otjh,
            $record->epao,
            $record->fundsource,
            $record->bksbrm,
            $record->bksbre,
            $record->learnstyle,
            $record->sslearnr,
            $record->ssemployr,
            $record->apprenhpw,
            $record->weekop,
            $record->annuall,
            $record->pdhours,
            $record->areaostren,
            $record->longtgoal,
            $record->shorttgoal,
            $record->iag,
            $record->recopl,
            $record->addsa
        ];
        $records = $DB->get_records_sql('SELECT * FROM {trainingplan_plans_modules} WHERE plansid = ?',[$planid]);
        $modArray = [];
        $total = [0,0];
        foreach($records as $rec){
            $tmp = [];
            array_push($tmp, 
                $rec->modpos,
                $rec->modname,
                date($datetype, $rec->modpsd)
            );
            $tmpVal = ($rec->modrsd == 0) ? '' : date($datetype,$rec->modrsd);
            array_push($tmp, $tmpVal);
            array_push($tmp, date($datetype,$rec->modped));
            $tmpVal = ($rec->modred == 0) ? '' : date($datetype, $rec->modred);
            array_push($tmp, $tmpVal);
            array_push($tmp, 
                $rec->modw,
                $rec->modotjh,
                $rec->modmod,
                $rec->modotjt,
                $rec->modaotjhc
            );
            array_push($modArray, $tmp);
            $total[0] += $rec->modw;
            $total[1] += $rec->modotjh;
        }
        asort($modArray);
        $modArray = [$modArray, $total];
        $records = $DB->get_records_sql('SELECT * FROM {trainingplan_plans_fs} WHERE plansid = ?',[$planid]);
        $fsArray = [];
        foreach($records as $rec){
            $tmp = [];
            array_push($tmp, 
                $rec->fspos,
                $rec->fsname
            );
            $tmpVal = ($rec->fslevel == 0) ? '' : $rec->fslevel;
            array_push($tmp, $tmpVal);
            $tmpVal = ($rec->fssd == 0) ? '' : date($datetype, $rec->fssd);
            array_push($tmp, $rec->fsmod, $tmpVal);
            $tmpVal = ($rec->fsped == 0) ? '' : date($datetype, $rec->fsped);
            array_push($tmp, $tmpVal);
            $tmpVal = ($rec->fsaed == 0) ? '' : date($datetype, $rec->fsaed);
            array_push($tmp, $tmpVal);
            $tmpVal = ($rec->fsaead == 0) ? '' : date($datetype, $rec->fsaead);
            array_push($tmp, $tmpVal);
            array_push($fsArray, $tmp);
        }
        asort($fsArray);
        $records = $DB->get_records_sql('SELECT * FROM {trainingplan_plans_pr} WHERE plansid = ?',[$planid]);
        $prArray = [];
        foreach($records as $rec){
            $tmp = [];
            array_push($tmp, $rec->prpos);
            $tmpVal = $rec->prtor;
            if($tmpVal == 'Learner'){
                $tmp[2] = 'selected';
                $tmp[6] = 'readonly disabled';
            } elseif($tmpVal == 'Employer'){
                $tmp[3] = 'selected';
                $tmp[6] = 'readonly disabled';
            } else {
                $tmp[1] = 'selected';
            }
            $tmpVal = ($rec->prpr == 0) ? '' : date($datetype, $rec->prpr);
            $tmp[4] = $tmpVal;
            $tmpVal = ($rec->prar == 0) ? '' : date($datetype, $rec->prar);
            $tmp[5] = $tmpVal;
            array_push($prArray, $tmp);
        }
        asort($prArray);
        $records = $DB->get_records_sql('SELECT * FROM {trainingplan_plans_log} WHERE plansid = ?',[$planid]);
        $logArray = [];
        foreach($records as $rec){
            array_push($logArray, [
                date($datetype, $rec->dateofc),
                $rec->log,
                'disabled'
            ]);
        }
        asort($logArray);
        return [$plansArray, $modArray, $fsArray, $prArray, $logArray];
    }

    //Update a training plan for a specific userid and courseid
    public function update_tplan($modArray, $fsArray, $prArray, $logArray){
        global $DB;
        $uid = $_SESSION['tp_trainingplan_uid'];
        $cid = $_SESSION['tp_trainingplan_cid'];
        if($this->check_trainingplan_exists($uid, $cid) && isset($_SESSION['tp_trainingplan_uid']) && isset($_SESSION['tp_trainingplan_cid'])){
            $planid = $this->get_planid($uid, $cid);
            for($i = 0; $i < count($modArray); $i++){
                $record = new stdClass();
                $record->id = $DB->get_record_sql('SELECT id FROM {trainingplan_plans_modules} WHERE plansid = ? and modpos = ?',[$planid, $i])->id;
                $record->modrsd = $modArray[$i][0];
                $record->modred = $modArray[$i][1];
                $record->modotjt = $modArray[$i][2];
                $DB->update_record('trainingplan_plans_modules', $record, true);
            }
            for($i = 0; $i < count($fsArray); $i++){
                $record = new stdClass();
                $record->id = $DB->get_record_sql('SELECT id FROM {trainingplan_plans_fs} WHERE plansid = ? and fspos = ?',[$planid, $i])->id;
                $record->fsaed = $fsArray[$i][0];
                $record->fsaead = $fsArray[$i][1];
                $DB->update_record('trainingplan_plans_fs', $record, true);
            }
            for($i = 0; $i < count($prArray); $i++){
                $record = new stdClass();
                if(count($prArray[$i]) == 1){
                    $record->id = $DB->get_record_sql('SELECT id FROM {trainingplan_plans_pr} WHERE plansid = ? and prpos = ?',[$planid, $i])->id;
                    $record->prar = $prArray[$i][0];
                    $DB->update_record('trainingplan_plans_pr', $record, true);
                } elseif(count($prArray[$i]) == 3){
                    $record->plansid = $planid;
                    $record->prpos = $i;
                    $record->prar = $prArray[$i][0];
                    $record->prtor = $prArray[$i][1];
                    $record->prpr = $prArray[$i][2];
                    $DB->insert_record('trainingplan_plans_pr', $record, true);
                }
            }
            $record = new stdClass();
            $record->plansid = $planid;
            $record->dateofc = $logArray[0];
            $record->log = $logArray[1];
            $DB->insert_record('trainingplan_plans_log', $record, true);
            \local_trainingplan\event\updated_plan::create(array('context' => \context_course::instance($cid), 'courseid' => $cid, 'relateduserid' => $uid, 'other' => $_SESSION['tp_trainingplan_e']))->trigger();
            $_SESSION['tp_update_success'] = true;
            return true;
        } else {
            return false;
        }
    }

    //Check if a setup exists for a specific userid and courseid for a coach
    public function check_setup_exists_coach($uid, $cid){
        global $DB;
        if($DB->record_exists('trainingplan_setup', [$DB->sql_compare_text('userid') => $uid, $DB->sql_compare_text('courseid') => $cid, $DB->sql_compare_text('teachid') => $this->get_userid()])){
            return true;
        } else {
            return false;
        }
    }

    //Check if a coach signature has been created for a specific userid and courseid
    public function check_coach_sign_exists($uid, $cid){
        global $DB;
        $record = $DB->get_record_sql('SELECT coachsign FROM {trainingplan_setup} WHERE userid = ? and courseid = ?',[$uid, $cid]);
        if($record->coachsign != '' && $record->coachsign != null){
            return true;
        } else{
            return false;
        }
    }

    //Create coach signature for a specific userid and course id
    public function create_sign($data){
        global $DB;
        $record = new stdClass();
        if(isset($_SESSION['tp_sign_uid']) && isset($_SESSION['tp_sign_cid'])){
            //Update coach signature
            $record->id = $DB->get_record_sql('SELECT id FROM {trainingplan_setup} WHERE userid = ? and courseid = ?',[$_SESSION['tp_sign_uid'], $_SESSION['tp_sign_cid']])->id;
            $record->coachsign = $data;
            if($DB->update_record('trainingplan_setup', $record, true)){
                \local_trainingplan\event\updated_coach_signature::create(array('context' => \context_course::instance($_SESSION['tp_sign_cid']), 'courseid' => $_SESSION['tp_sign_cid'], 'relateduserid' => $_SESSION['tp_sign_uid'], 'other' => $_SESSION['tp_sign_e']))->trigger();
                unset($_SESSION['tp_sign_uid']);
                unset($_SESSION['tp_sign_cid']);
                unset($_SESSION['tp_sign_e']);
                return true;
            } else {
                return false;
            }
        } elseif(isset($_SESSION['tp_sign_cid'])){
            //update learner signature
            $record->id = $DB->get_record_sql('SELECT id FROM {trainingplan_setup} WHERE userid = ? and courseid = ?',[$this->get_userid(), $_SESSION['tp_sign_cid']])->id;
            $record->learnersign = $data;
            if($DB->update_record('trainingplan_setup', $record, true)){
                \local_trainingplan\event\updated_learner_signature::create(array('context' => \context_course::instance($_SESSION['tp_sign_cid']), 'courseid' => $_SESSION['tp_sign_cid']))->trigger();
                unset($_SESSION['tp_sign_cid']);
                return true;
            } else {
                return false;
            }
        }
    }

    //Check if a setup exists for a specific userid and course id for a learner
    public function check_setup_exists_learner($cid){
        global $DB;
        if($DB->record_exists('trainingplan_setup', [$DB->sql_compare_text('userid') => $this->get_userid(), $DB->sql_compare_text('courseid') => $cid])){
            return true;
        } else {
            return false;
        }
    }

    //Check if a learner signature exists for a specific course id
    public function check_learn_sign_exists($cid){
        global $DB;
        $record = $DB->get_record_sql('SELECT learnersign FROM {trainingplan_setup} WHERE userid = ? and courseid = ?',[$this->get_userid(), $cid]);
        if($record->learnersign != '' && $record->learnersign != null){
            return true;
        } else {
            return false;
        }
    }
}