<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

namespace local_trainingplan\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class created_plan extends base {
    protected function init(){
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    public static function get_name(){
        return "Plan created";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' created a plan for the user with id '".$this->relateduserid."' and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/trainingplan/plan.php?uid='.$this->relateduserid.'&cid='.$this->courseid.'&c='.$this->other);
    }
    public function get_id(){
        return $this->objectid;
    }
}