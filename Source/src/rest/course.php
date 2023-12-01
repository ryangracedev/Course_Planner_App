<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");
include_once("../validation.php");


header('Content-Type: application/json');

function getCourse($db, string $code){
    if($code == 'all') {
        $result = $db->query('select * from courses;');
        header('Content-Type: application/json');
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        return http_response_code(200);
    }
    else {
        header('Content-Type: application/json');
        if(strpos( $code, '*') === false) {
            echo "Invalid course code";
            return http_response_code(400);
        }

        $courseCode = explode("*", $code);
        $result = $db->query('select * from courses where subject_area = \'' . $courseCode[0] . '\' and number = '. $courseCode[1] . ';');
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        return http_response_code(200);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read request body
    $req_body = json_decode(file_get_contents('php://input'), true);

    if (!validateInput($req_body, $getCourseSchema)) {
        return http_response_code(400);
    }
    getCourse($db, $req_body['course']);

    return http_response_code(200);
}
