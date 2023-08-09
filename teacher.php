<?php
/**
 * @package     local_trainingplan
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

require_once(__DIR__.'/../../config.php');
use local_trainingplan\lib;
require_login();
$lib = new lib;
$p = 'local_trainingplan';

$title = get_string('trainingplans', $p);
$type = '';
$enrolments = [];
$id = null;
$errorText = '';
if(isset($_GET['id'])){
    //get id for the course and check for the capability after validating the input
    $id = $_GET['id'];
    if(!preg_match("/^[0-9]*$/", $id) || empty($id)){
        $errorText = get_string('invalid_cip', $p);
    } else {
        if($lib->check_coach_course($id)){
            $context = context_course::instance($id);
            require_capability('local/trainingplan:teacher', $context);
            $PAGE->set_context($context);
            $PAGE->set_course($lib->get_course_record($id));
        } else {
            $errorText = get_string('not_eacicp', $p);
        }
    }
    $type = 'one';
} else {
    $type = 'all';
    $enrolments = $lib->check_coach();
    if(count($enrolments) > 0){
        $context = context_course::instance($enrolments[0][0]);
        require_capability('local/trainingplan:teacher', $context);
        $PAGE->set_context($context);
    } else {
        $errorText = get_string('no_ca', $p);
    }
}

$PAGE->set_url(new moodle_url('/local/trainingplan/teacher.php'));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();

if($errorText != ''){
    echo("<h1 class='text-error'>$errorText</h1>");
} elseif($type == 'all'){
    $_SESSION['tp_setup_type'] = 'all';
    $template = (Object)[
        'title' => get_string('trainingplans', $p),
        'enrolments' => array_values($enrolments)
    ];
    echo $OUTPUT->render_from_template('local_trainingplan/teacher_all_courses', $template);
    echo("<script src='./amd/min/teacher_course.min.js'></script>");
    \local_trainingplan\event\viewed_menu::create(array('context' => \context_system::instance()))->trigger();
} elseif($type == 'one'){
    $_SESSION['tp_setup_type'] = 'one';
    $template = (Object)[
        'title' => get_string('trainingplans', $p),
        'coursename' => $lib->get_course_fullname($id)
    ];
    echo $OUTPUT->render_from_template('local_trainingplan/teacher_one_course', $template);
    echo("<script src='./amd/min/teacher_course.min.js'></script>");
    echo("<script defer>course_clicked($id)</script>");
    \local_trainingplan\event\viewed_menu::create(array('context' => \context_course::instance($id)))->trigger();
}

echo $OUTPUT->footer();