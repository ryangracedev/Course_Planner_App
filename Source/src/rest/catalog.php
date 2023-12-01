<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");

header('Content-Type: application/json');

function create_sql_filter($db, $json)
{

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read request body
    $req_body = json_decode(file_get_contents('php://input'), true);

    // Set default values

    // default to checking all courses
    $filter = array();
    if (isset($req_body['filter'])) {
        $filter = $req_body['filter'];
    }
    $filter_str = create_sql_filter($db, $filter);

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
    $data_format_array = array_map(function ($val) use ($db) {
        return is_string($val) ? $db->real_escape_string($val) : $val;
    }, $data_format);
    $fields_str = implode(", ", $data_format_array);

    // Get all courses with filter applied
    $result = null;
    if (isset($filter['semester']) || in_array('semesters', $data_format)) {
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

    $courses = [];

    // for each course
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if student is eligible given their taken courses
        $courses[] = $row;
    }

    echo json_encode(array(
        "status" => true,
        "courses" => $courses
    ));
}
?>