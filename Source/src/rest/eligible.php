<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");

header('Content-Type: application/json');

function create_sql_filter($db, $json){

    $conditions = [];
    foreach ($json as $key => $value) {
        // skip null parameters
        if ($value == null) {
            continue;
        }
        if (is_array($value)) {
            // do or condition
            $or_cond = [];
            foreach ($value as $cond) {
                $or_cond[] = "{$db->real_escape_string($key)} = " . (is_string($cond) ? "'{$db->real_escape_string($cond)}'" : $cond);
            }
            $conditions[] = "(" . implode(" OR ", $or_cond) . ")";
        } else {
            $conditions[] = "{$db->real_escape_string($key)} = " . (is_string($value) ? "'{$db->real_escape_string($value)}'" : $value);
        }
    }

    if (count($conditions) == 0) {
        return "";
    }

    $filter_str = "WHERE " . implode(' AND ', $conditions);

    return $filter_str;
}

function is_requirement_satisfied($db, $requirement, $taken_courses){
    if ($requirement == null) {
        return true;
    }

    $num_satisfied = 0;

    // get required courses
    $required_courses = $db->query(
        "SELECT courses.subject_area, courses.number FROM course_requirements
        JOIN courses ON courses.id = course_requirements.course_id
        JOIN requirements ON requirements.id = course_requirements.requirement_id
        WHERE requirements.id={$requirement['id']}"
    );

    // check required courses in group
    while ($row = mysqli_fetch_assoc($required_courses)) {
        if (in_array($row['subject_area'] . '*' . $row['number'], $taken_courses, true)) {
            $num_satisfied = $num_satisfied + 1;
        }

        if ($num_satisfied >= $requirement['num_required']) {
            return true;
        }
    }

    // run check on sub groups
    $subgroups = $db->query(
        "SELECT * FROM requirements WHERE parent_id={$requirement['id']}"
    );
    while ($row = mysqli_fetch_assoc($subgroups)) {
        if (is_requirement_satisfied($db, $row, $taken_courses)) {
            $num_satisfied = $num_satisfied + 1;
        }

        if ($num_satisfied >= $requirement['num_required']) {
            return true;
        }
    }

    if ($num_satisfied >= $requirement['num_required']) {
        return true;
    }

    return false;
}

function is_eligible($db, $course, $taken_courses){
    if (!isset($course['requirements_id'])) {
        // course has no requirements, return true
        return true;
    }

    if(isset($course['credits_required'])) {
        if(sum_course_weights($db, $taken_courses) < $course['credits_required']) {
            return false;
        }
    }


    $result = $db->query("SELECT * FROM requirements WHERE id={$course['requirements_id']}");
    $requirement = mysqli_fetch_assoc($result);

    return is_requirement_satisfied($db, $requirement, $taken_courses);
}

function sum_course_weights($db, $taken_courses){
    $total_weight = 0.0;

    foreach ($taken_courses as $value) {

        $split = explode("*", $value);
        if(count($split) != 2) {
            continue;
        }

        $result = $db->query(
            "SELECT weight FROM courses WHERE subject_area='{$db->real_escape_string($split[0])}' AND number='{$db->real_escape_string($split[1])}'"
        );
        $course = mysqli_fetch_assoc($result);
        if(isset($course['weight'])) {
            $total_weight = $total_weight + floatval($course['weight']);
        }
    }

    return $total_weight;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read request body
    $req_body = json_decode(file_get_contents('php://input'), true);
    
    // default to checking all courses
    $course = array();
    if (isset($req_body['course'])) {
        $course = $req_body['course'];
    }
    $filter_str = create_sql_filter($db, $course);

    // default to no courses taken
    $taken_courses = [];
    if (isset($req_body['completed'])) {
        if (!is_array($req_body['completed'])) {
            http_response_code(400);
            echo json_encode(array(
                "status" => false,
                "message" => "Field 'completed' must be of type 'array'"
            ), JSON_FORCE_OBJECT);
            exit(1);
        }
        $taken_courses = $req_body['completed'];
    }

    // default data parameters
    $data_format = array("subject_area", "number", "name");
    if (isset($req_body['data'])) {
        if (!is_array($req_body['data'])) {
            http_response_code(400);
            echo json_encode(array(
                "status" => false,
                "message" => "Field 'data' must be of type 'array'"
            ), JSON_FORCE_OBJECT);
            exit(1);
        }
        $data_format = $req_body['data'];
    }
    $data_format[] = "requirements_id";
    if(!in_array('credits_required', $data_format)) {
        $data_format[] = "credits_required";
    }
    $data_format_array = array_map(function ($val) use ($db) {
        return is_string($val) ? $db->real_escape_string($val) : $val;
    }, $data_format);
    $fields_str = implode(", ", $data_format_array);

    // Get all courses with filter applied
    $result = null;
    if (isset($course['semester']) || in_array('semesters', $data_format)) {
        // join on offerings to filter by semester
        $result = $db->query(
            "WITH courses_table AS (
                SELECT courses.*, GROUP_CONCAT(DISTINCT(offerings.semester)) AS semesters 
                FROM courses 
                LEFT JOIN offerings ON courses.id=offerings.course_id 
                {$filter_str} 
                GROUP BY offerings.course_id
                ORDER BY subject_area, number
            )
            SELECT {$fields_str} FROM courses_table"
        );
    } else {
        $result = $db->query("SELECT {$fields_str} FROM courses {$filter_str} ORDER BY subject_area, number");
    }

    $eligible_courses = [];

    // for each course
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if student is eligible given their taken courses
        if (is_eligible($db, $row, $taken_courses)) {
            unset($row['requirements_id']);
            if(!in_array("credits_required", $data_format)) {
                unset($row['credits_required']);
            }
            $eligible_courses[] = $row;
        }
    }

    echo json_encode(array(
        "status" => true,
        "courses" => $eligible_courses
    ));
}
