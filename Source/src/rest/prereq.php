<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");
include_once("../validation.php");


header('Content-Type: application/json');

function getPrereqFromCourse($db, string $code) {
    header('Content-Type: application/json');

    $courseCode = explode("*", $code);
    $stmt = $db->prepare("WITH RECURSIVE prereqs AS (
        SELECT requirements.* FROM requirements
        JOIN courses ON requirements.id = courses.requirements_id
        WHERE courses.subject_area=? AND courses.number=?
        UNION
        SELECT requirements.* FROM requirements, prereqs WHERE prereqs.id = requirements.parent_id
    )
    SELECT prereqs.*, courses.subject_area, courses.number, courses.name FROM course_requirements
    RIGHT JOIN prereqs ON prereqs.id = course_requirements.requirement_id
    LEFT JOIN courses ON courses.id = course_requirements.course_id;");
    $courseSubject = $courseCode[0];
    $courseNumber = intval($courseCode[1]);
    $stmt->bind_param("si", $courseSubject, $courseNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));

    return http_response_code(200);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read request body
    $req_body = json_decode(file_get_contents('php://input'), true);

    if (array_key_exists('prereq', $req_body)) {
        if (!validateInput($req_body, $getPrereqsSchema)) {
            return http_response_code(400);
        }
        getPrereqFromCourse($db, $req_body['prereq']);
    }

    return http_response_code(200);
}
