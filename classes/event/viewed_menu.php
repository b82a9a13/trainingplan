<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

namespace local_trainingplan\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class viewed_menu extends base {
    protected function init(){
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    public static function get_name(){
        return "Menu viewed";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' the menu for training plans.";
    }
    public function get_url(){
        return new \moodle_url('/local/trainingplan/teacher.php');
    }
    public function get_id(){
        return $this->objectid;
    }
}