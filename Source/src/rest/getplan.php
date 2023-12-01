<?php
// if(!isset($_COOKIE['user_id'])) {
//     echo "Cookie is not set!";
//   } else {
//     echo "Cookie is set" . $_COOKIE['user_id'];
//   }
?>
<?php
// TODO: Add number of credits check
$db_name = "course_data";
$db = include_once("../database.php");
include_once("../validation.php");


header('Content-Type: application/json');

function getPlan($db){
    if (isset($_COOKIE['user_id'])) {
        $padded_cookie = str_pad($_COOKIE['user_id'], 32, '0');
        $q_string = 'select course_id from course_plan where plan_id=UNHEX(\'' . $padded_cookie . '\');';
        $result = $db->query($q_string);
        $planned_course_ids = $result->fetch_all(MYSQLI_ASSOC);
        
        $planned_courses = [];
        foreach ($planned_course_ids as $course_id) {
            $q_string = 'select * from courses where id=' . $course_id['course_id'] . ';';
            $result = $db->query($q_string);
            $planned_course = $result->fetch_all(MYSQLI_ASSOC);
            array_push($planned_courses, $planned_course[0]);
        }

        return $planned_courses;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //json format->courses:[course_code,course_code]
    // $req_body = json_decode(file_get_contents('php://input'), true);


    // #validating the input
    // if (!validateInput($req_body, $getCourseSchema)) {
    //     return http_response_code(400);
    // }

    // getPlan($db);

    echo json_encode(getPlan($db));

    return http_response_code(200);
}

// demo insert of new record and select
//
// $db->query("INSERT INTO plans(id, last_edit) VALUES (DEFAULT, DEFAULT)");
// $res = $db->query("SELECT HEX(id) AS id FROM plans WHERE id = UNHEX('8050059C8A0B11EE8A2E7CB27DD08837')");
// while ($row = mysqli_fetch_assoc($res)) {
//     // Check if student is eligible given their taken courses
//     echo $row['id'] . '\n';
// }
