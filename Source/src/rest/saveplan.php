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

#Validating courses array 
$getCourseSchema = array(
    'courses' => function($val) {
        if (!is_array($val)) {
            return false;
        }
        foreach ($val as $course)
        {
            $codeComponents = explode('*', $course);
            $subject_area = $codeComponents[0];
            $number = intval($codeComponents[1]);
            if (((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000)) == false){
                return false;
            }
        }
        return true;
    }
);

#Validating courses array 
$getPlanSchema = array(
    'courses' => function($val) {
        if (!is_array($val)) {
            return false;
        }
        foreach ($val as $course)
        {
            $codeComponents = explode('*', $course);
            $subject_area = $codeComponents[0];
            $number = intval($codeComponents[1]);
            if (((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000)) == false){
                return false;
            }
        }
        return true;
    }
);

function getCourse($db, $course_codes){

    $q_string = 'select id from courses where ';
    $idx = 0;
    // Constructing query string to get all course ids from courses table
    foreach ($course_codes as $val){
        $splitCourse = explode('*',$val);
        $q_string = $q_string . '(subject_area=\''.$splitCourse[0].'\' and number=' . strval($splitCourse[1]) . ') ';
        
        if ($idx + 1  < count($course_codes)){
            $q_string = $q_string. 'OR ';
        }
        $idx++;
    }
    $q_string = $q_string . ';';
    $result = $db->query($q_string);
    $course_ids = $result->fetch_all(MYSQLI_ASSOC);

    #get if there is an already planned cookie
    $padded_cookie = str_pad($_COOKIE['user_id'], 32, '0');
    $q_string = 'select last_edit from plans where id=UNHEX(\''. $padded_cookie . '\');';
    $planned_results = $db->query($q_string);
    $cookie_data = $planned_results->fetch_all(MYSQLI_ASSOC);

    if (count($cookie_data) == 0){
        #insert id into plans where id=planned courses
        $q_string = 'insert into plans(id,last_edit) values(UNHEX(\''. $padded_cookie . '\'), default);';
        $db->query($q_string);
    }
    else{
        #update last edit 
        $q_string = 'update plans set last_edit=now() where id=UNHEX(\'' . $padded_cookie . '\');';
        $db->query($q_string);
    }

    #delete previous planned for the given cookie(overriding)
    $q_string = 'delete from course_plan where plan_id=UNHEX(\'' . $padded_cookie . '\');';
    $db->query($q_string);


    #Adding entries for courses correlated to a cookie/plan_id
    $q_string = 'insert into course_plan(course_id,plan_id) values ';
    $idx = 0;

    foreach ($course_ids as $val){
        // $splitCourse = explode('*',$val);
        $q_string = $q_string . '(' . $val['id'] . ',UNHEX(\'' . $padded_cookie . '\'))';
        
        if ($idx + 1  < count($course_ids)){
            $q_string = $q_string. ', ';
        }
        $idx++;
    }
    $q_string = $q_string. ';';
    $db->query($q_string);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //json format->courses:[course_code,course_code]
    $req_body = json_decode(file_get_contents('php://input'), true);


    #validating the input
    if (!validateInput($req_body, $getCourseSchema)) {
        return http_response_code(400);
    }

    if (!isset($_COOKIE['user_id'])) {
        $q_string = "select generateUUID();";
        $result = $db->query($q_string);
        $uuid = $result->fetch_all(MYSQLI_ASSOC)[0]['generateUUID()'];

        $cookie_name = "user_id";
        $cookie_value = $uuid;
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
        $_COOKIE['user_id'] = $cookie_value;
    }
    // debugging code for removing cookie
    // else {
    //     echo 'cookie is set; removing cookie';

    //     print_r($_COOKIE);

    //     $cookie_name = "user_id";
    //     $cookie_value = 2001;
    //     setcookie($cookie_name, $cookie_value, time() - 3600, "/");
    // }

    // return http_response_code(200);

    getCourse($db, $req_body['courses']);

    echo '{}';

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
