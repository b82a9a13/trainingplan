<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

namespace local_trainingplan\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class updated_coach_signature extends base {
    protected function init(){
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    public static function get_name(){
        return "Coach signature updated";
    }
    public function get_description(){
        return "The user width id '".$this->userid."' updated the coach signature for the user with id '".$this->relateduserid."' and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/trainingplan/sign.php?uid='.$this->relateduserid.'&cid='.$this->courseid.'&c='.$this->other);
    }
    public function get_id(){
        return $this->objectid;
    }
}