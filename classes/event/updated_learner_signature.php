<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

namespace local_trainingplan\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class updated_learner_signature extends base {
    protected function init(){
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'trainingplan_setup';
    }
    public static function get_name(){
        return "Learner signature updated";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' updated their signature for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/trainingplan/sign.php?cid='.$this->courseid);
    }
    public function get_id(){
        return $this->objectid;
    }
}