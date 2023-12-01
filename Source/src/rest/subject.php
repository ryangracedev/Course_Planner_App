<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");
include_once("../validation.php");


header('Content-Type: application/json');

function getAllFromSubject($db, string $subject) {
    $result = $db->query('select * from courses where subject_area = \'' . $subject . '\';');
    header('Content-Type: application/json');
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    return http_response_code(200);
}

function getAllSubjects($db) {
    $result = $db->query('select distinct subject_area from courses;');
    header('Content-Type: application/txt');
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    return http_response_code(200);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read request body
    $req_body = json_decode(file_get_contents('php://input'), true);

    if (!validateInput($req_body, $getSubjectSchema)) {
        return http_response_code(400);
    }

    if($req_body['subject'] == 'all') {
        getAllSubjects($db);
    }
    else {
        getAllFromSubject($db, $req_body['subject']);
    }

    return http_response_code(200);
}
