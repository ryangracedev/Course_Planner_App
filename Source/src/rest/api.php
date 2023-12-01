<?php
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    $getSubjectSchema = array(
       'subject' => function($val) {
            return is_string($val) && ($val == 'all' || (strlen($val) >= 2 && strlen($val) <= 4));
       }
    );

    $getCourseSchema = array(
        'course' => function($val) {
            if (!is_string($val)) {
                return false;
            }

            $codeComponents = explode('*', $val);

            if (count($codeComponents) != 2) {
                return $val == 'all';
            }

            $subject_area = $codeComponents[0];
            $number = intval($codeComponents[1]);
            return ((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000));
        }
    );
    // $getEligibleSchema = array(
    //     'c1' => function($val) {
    //         if (!is_string($val)) {
    //             return false;
    //         }
    //         $codeComponents = explode('*', $val);

    //         if (count($codeComponents) != 2) {
    //             return false;
    //         }

    //         $subject_area = $codeComponents[0];
    //         $number = intval($codeComponents[1]);
    //         return ((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000));
    //     }
    // );
    $getPrereqsSchema = array(
        'prereq' => function($val) {
            if (!is_string($val)) {
                return false;
            }

            $codeComponents = explode('*', $val);

            if (count($codeComponents) != 2) {
                return false;
            }

            $subject_area = $codeComponents[0];
            $number = intval($codeComponents[1]);

            return ((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000));
        }
    );

    function connect(){
        $db = new mysqli(
            'localhost', $_SERVER['DB_USER'], $_SERVER['DB_PASS'], 'course_data', 3306
        );
    
        if ($db->connect_errno) {
            echo "Failed to connect to MySQL: " . $db->connect_error;
            exit(0);
        }
    
        return $db;
    }

    /*
    $body = {
        ['key1'] = value1,
        ['key2'] = value2
    }

    $schema = {
        ['key1'] => function($value) {
            --check if value is valid
        }
    }
    $schema['key1']($body['key1'])
    */
    function validateInput($body, $schema){
        if (!is_array($body) || !is_array($schema)) {
            return http_response_code(400);
        }

        foreach (array_keys($schema) as $key) {
            if (!array_key_exists($key, $body) || !$schema[$key]($body[$key])) {
                return false;
            }
        }

        return true;
    }

    function simplePrereq($json, $courses_taken){

        $id_to_num_passed = array();//Keeps track of the taken prereqs
        $id_to_num_required = array();//Keeps track of the required prereqs

        $json_array = json_decode($json,true);

        foreach ($json_array as $json_object) {
            $id = $json_object['id'];

            $num_required = $json_object['num_required'];
            $subject_area = $json_object['subject_area'];
            $number = $json_object['number'];

            $course = $subject_area . '*'. $number;
            if (array_key_exists($id, $id_to_num_passed)) {
                if (in_array($course, $courses_taken)){
                    $id_to_num_passed[$id]+=1;
                }
            }
            // If the array key doesn't exist in num_passed, set it equal to num_required, for comparison
            else{
                $id_to_num_required[$id] = $num_required;
                $id_to_num_passed[$id]= 0;
                if (in_array($course, $courses_taken)){
                    $id_to_num_passed[$id]=1;
                }
            }
        }
        //Met all required prereqs
        if ($id_to_num_passed[$id] >= $id_to_num_required[$id])
        {
            return 1; 
        }
        else{
            return 0; 
        }
    }
// example of complex requirements -> 1292, 1294
// +------+-----------+--------------+--------------+--------+----------------------------------------+
// | id   | parent_id | num_required | subject_area | number | name                                   |
// +------+-----------+--------------+--------------+--------+----------------------------------------+
// | 1292 |      NULL |            2 | NULL         |   NULL | NULL                                   |
// | 1293 |      1292 |            1 | CHEM         |   1050 | General Chemistry II                   |
// | 1294 |      1292 |            2 | NULL         |   NULL | NULL                                   |
// | 1295 |      1294 |            1 | IPS          |   1510 | Integrated Mathematics and Physics II  |
// | 1296 |      1294 |            2 | MATH         |   1210 | Calculus II                            |
// | 1296 |      1294 |            2 | PHYS         |   1010 | Introductory Electricity and Magnetism |
// +------+-----------+--------------+--------------+--------+----------------------------------------+


    function complexPrereq($json, $courses_taken) {
        $recursive_json = array(); // stores json to be used recursively
        $json_array = json_decode($json,true); // decode json into the rows
        $num_required = $json_array[0]['num_required']; //num of required pre-reqs
        $root_id = $json_array[0]['id']; // the id of the root complex pre-req
        $id = $json_array[1]['parent_id']; // parent id of first row that isnt the root row
        $loopCounter = 1; // start at 1 instead of 0
        $result = 0;

        while($root_id == $id && $loopCounter < count($json_array)) { // while the current parent_id is == root id
            if($json_array[$loopCounter]['subject_area'] == NULL) { // complex pre-req
                $id_list = array(); // list of ids that are related to a complex pre-req
                $first_child_id = $json_array[$loopCounter]['id']; // the first child pre-req of a complex req
                $cur_child_id = $first_child_id; // current child pre-req of a complex req
                // while current == first id OR its related to one of the listed reqs
                while(($cur_child_id == $first_child_id || in_array($cur_child_id, $id_list)) && $loopCounter < count($json_array) ) {
                    array_push($recursive_json, $json_array[$loopCounter]);
                    array_push($id_list, $json_array[$loopCounter]['id']);
                    $loopCounter += 1;
                    if($loopCounter < count($json_array)) {
                        $cur_child_id = $json_array[$loopCounter]['parent_id'];
                    }
                }

                $result += complexPrereq(json_encode($recursive_json), $courses_taken); // eval recursively
                $recursive_json = array(); // reset json array
                $loopCounter -= 1; // reset counter back to within range
            }
            else { // simple pre-req
                
                $first_child_id = $json_array[$loopCounter]['id']; // same as complex if statement
                $cur_child_id = $first_child_id; // same as complex if statement
                // while still same id as first child
                while($cur_child_id == $first_child_id && $loopCounter < count($json_array)) {
                    array_push($recursive_json, $json_array[$loopCounter]);
                    $loopCounter += 1;
                    if($loopCounter < count($json_array)) {
                        $cur_child_id = $json_array[$loopCounter]['id'];
                    }
                }

                $result += simplePrereq(json_encode($recursive_json), $courses_taken); // eval simple without recursion
                $recursive_json = array();
                $loopCounter -= 1;
            }
            
            $loopCounter += 1;
            if($loopCounter < count($json_array)) {
                $id = $json_array[$loopCounter]['parent_id'];
            }
        }
        // if user got enough matches, then return 1 else 0
        if($result >= $num_required) {
            return 1;
        }
        else {
            return 0;
        }
    }

    function getCourse(string $code){
        $connection = connect();
        if($code == 'all') {
            $result = $connection->query('select * from courses;');
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
            $result = $connection->query('select * from courses where subject_area = \'' . $courseCode[0] . '\' and number = '. $courseCode[1] . ';');
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            return http_response_code(200);
        }
    }

    function addCourse(string $subject_area, int $number,string $name,float $weight,string $description,string $department,string $location,string $prerequisites,?int $requirements_id,float $credits_required){
        //validateInput($subject_area, $number, $name, $weight, $description, $department, $location, $prerequisites, $requirements_id, $credits_required);
        
        $connection = connect();
        header('Content-Type: application/json');
        $stmt = $connection->prepare("INSERT INTO courses (subject_area,number,name,weight,description,department,location,prerequisites,requirements_id,credits_required) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sisdssssid", $subject_area, $number, $name, $weight, $description, $department, $location, $prerequisites, $requirements_id, $credits_required);
        $stmt->execute();
        return http_response_code(200);
    }

    //function deleteCourse deletes a course from the mysql database
    //Pair programmed by Noah and Ryan
    function deleteCourse(string $code){
        $connection = connect();
        header('Content-Type: application/json');
        if(strpos( $code, '*') === false) {
            echo "Invalid course code";
            return http_response_code(400);
        }

            $courseCode = explode("*", $code);
            $result = $connection->query('select * from courses where subject_area = \'' . $courseCode[0] . '\' and number = ' . $courseCode[1] . ';');
            $row = $result->fetch_assoc();
            $exists = (bool)$row;
            if($exists){
                $result = $connection->query('select * from courses where subject_area = \'' . $courseCode[0] . '\' and number = ' . $courseCode[1] . ';');
                echo json_encode($result->fetch_all(MYSQLI_ASSOC));

                $result = $connection->query('delete from courses where subject_area = \'' . $courseCode[0] . '\' and number = ' . $courseCode[1] . ';');
                echo "Course: " . $courseCode[0] . "*" . $courseCode[1]. " has been deleted";
                return http_response_code(200);
            }else{
                echo "Course does not exist";
                http_response_code(304);
            }
    }

    // Function to update specific course

    function putCourse(string $subject_area, int $number, string $name, float $weight, string $description, string $department, string $location, string $prerequisites, ?int $requirements_id, float $credits_required){
        //validateInput($subject_area, $number, $name, $weight, $description, $department, $location, $prerequisites, $requirements_id, $credits_required);
        
        $connection = connect();
        header('Content-Type: application/json');
        
        if (is_null($requirements_id)) {
            $result = $connection->query('update courses set name = \'' . $name . '\' , weight = ' . $weight . ' , description = \'' . $description . '\' , department = \'' . $department . '\' , location = \'' . $location . '\' , prerequisites = \'' . $prerequisites . '\' , requirements_id = NULL , credits_required = ' . $credits_required . ' where subject_area = \'' . $subject_area . '\' and number = ' . $number . ';');
        }
        else {
            $result = $connection->query('update courses set name = \'' . $name . '\' , weight = ' . $weight . ' , description = \'' . $description . '\' , department = \'' . $department . '\' , location = \'' . $location . '\' , prerequisites = \'' . $prerequisites . '\' , requirements_id = ' . $requirements_id . ' , credits_required = ' . $credits_required . ' where subject_area = \'' . $subject_area . '\' and number = ' . $number . ';');
        }

        echo "Course successfully updated.";

        return http_response_code(200);
    }

    function getAllFromSubject(string $subject) {
        $connection = connect();
        
        $result = $connection->query('select * from courses where subject_area = \'' . $subject . '\';');
        header('Content-Type: application/json');
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        return http_response_code(200);
    }

    function getAllSubjects() {
        $connection = connect();
        
        $result = $connection->query('select distinct subject_area from courses;');
        header('Content-Type: application/json');
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        return http_response_code(200);
    }
    function findEligibility($courses_taken) {

        $connection = connect();
        
        $result = $connection->query('select subject_area,number from courses;');
        header('Content-Type: application/json');

        //If first subject area is null, signals that it's a complex prereq
        $eligible_courses = array();
        // print_r($prereqs);
        foreach ($result as $row) {
            $is_eligible = 0;

            //Construct the course_code
            $course_code = $row['subject_area'] . "*" . $row['number'];

            //Get the prereq for the course code
            $json_prereq = getPrereqFromCourse($course_code);

            $prereqs = json_decode($json_prereq,true);
            
            if (!$prereqs[0]['subject_area']){//if the first rows subject_area is null, it's a complex requirement
                $is_eligible = complexPrereq($json_prereq,$courses_taken);
            }
            else{
                $is_eligible = simplePrereq($json_prereq,$courses_taken);
            }

            if ($is_eligible == 1){//Push the eligible course onto the array
                array_push($eligible_courses,$course_code);
            }

        }
         // json_encode($eligible_courses);
        return json_encode($eligible_courses);
              
    }

    function getPrereqFromCourse(string $code) {
        $connection = connect();

        header('Content-Type: application/json');
        if(strpos( $code, '*') === false) {
            echo "Invalid course code";
            return http_response_code(400);
        }

        $courseCode = explode("*", $code);
        $stmt = $connection->prepare("WITH RECURSIVE prereqs AS (
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
        // maybe process the json more before return
        http_response_code(200);
        return json_encode($result->fetch_all(MYSQLI_ASSOC));

    }

    //if ($_SERVER["REQUEST_METHOD"] == "GET") { 
        //------MOVED TO USING POST REQUESTS------
        /*
        parse_str($_SERVER['QUERY_STRING'], $queryParams);
        // if(array_key_exists('url', $queryParams)) {
        //     if($queryParams['url'] == 'getAllFromSubject') {

        //     }
        // }
        
        // $test_array = array("ANSC*3080", "ANSC*3120");
        // echo simplePrereq($json_test,$test_array);
        // return "";

        // $test_array = array("CHEM*1050", "IPS*1510", "MATH*1210", "PHYS*1000");
        // echo complexPrereq($json_test_complex,$test_array);
        // return "";
        if(array_key_exists('apiRoute', $queryParams)){ //our proxy server

            $xml = file_get_contents("https://cis3760f23-09.socs.uoguelph.ca/api/available.php?". $queryParams['apiRoute'] . "&sem=" . $queryParams['sem']);
            echo $xml;
            return http_response_code(200);
        }
        if(array_key_exists('apiGetRoute', $queryParams)){ //our proxy server

            $xml = file_get_contents("https://cis3760f23-09.socs.uoguelph.ca/api/course.php?". $queryParams['apiGetRoute']);
            echo $xml;
            return http_response_code(200);
        }
        elseif(array_key_exists('subject', $queryParams)) {
            if (!validateInput($queryParams, $getSubjectSchema)) {
                return http_response_code(400);
            }
            if($queryParams['subject'] == 'all') {
                getAllSubjects();
            }
            else {
                getAllFromSubject($queryParams['subject']);
            }
        }
        elseif(array_key_exists('findCourses', $queryParams)) {
            if($queryParams['findCourses'] == 'false') {
                return http_response_code(400);            
            }  
            include('findCourses.html');

        }

        elseif (array_key_exists('course', $queryParams)) {
            if (!validateInput($queryParams, $getCourseSchema)) {
                return http_response_code(400);
            }
            getCourse($queryParams['course']);
        }
        elseif (array_key_exists('prereq', $queryParams)) {
            if (!validateInput($queryParams, $getPrereqsSchema)) {
                return http_response_code(400);
            }
            echo getPrereqFromCourse($queryParams['prereq']);
        }
        else{
            echo "Unknown request";
            return http_response_code(400);
        }
        */
    //}
    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $course_arr = array();

        //POST request through form
        if (count($_POST) > 0){
            foreach ($_POST as $key => $value) 
            {
                if (!empty($value) and $key!='submit'){
                    $course_arr[$key] = $value;
                }
            }
        }
        //POST request through API call
        else {
            $entityBody = file_get_contents('php://input');
            $entityBody = json_decode($entityBody, true);
            foreach ($entityBody as $key => $value) 
            {
                if (!empty($value)){
                    $course_arr[$key] = $value;
                }
            }
        }

        //validation
        foreach ($course_arr as $key => $value) {
            $codeComponents = explode('*', $value);
            if (count($codeComponents) != 2) {
                return http_response_code(400);
            }
            $subject_area = $codeComponents[0];
            $number = intval($codeComponents[1]);
            $echo = ''. $subject_area .''. $number .'';
            $isValid = ((strlen($subject_area) >= 2 && strlen($subject_area) <= 4) && ($number >= 1000 && $number < 10000));
            if ($isValid == false) {
                return http_response_code(400);
            }
        }
        $courses_taken = array_values($course_arr);

        echo findEligibility($courses_taken);

        //POST REQUEST NOT IN USE. 
        // addCourse($entityBody['subject_area'],$entityBody['number'],$entityBody['name'],$entityBody['weight'],$entityBody['description'],$entityBody['department'],$entityBody['location'],$entityBody['prerequisites'],$entityBody['requirements_id'],$entityBody['credits_required']);
    }
    //DELETE REQUEST NOT IN
    // elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    //     parse_str($_SERVER['QUERY_STRING'], $queryParams);

    //     if (array_key_exists('course', $queryParams)) {
    //         deleteCourse($queryParams['course']);
    //     }
    //     else {
    //         return http_response_code(400);
    //     }
    // }
    elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
        // Get course info
        $entityBody = file_get_contents('php://input');
        $entityBody = json_decode($entityBody, true);
        // Check if JSON decode was successful
        if ($entityBody == NULL) {
            echo "Empty request body";
            return http_response_code(400);
        }
        putCourse($entityBody['subject_area'], $entityBody['number'], $entityBody['name'], $entityBody['weight'], $entityBody['description'], $entityBody['department'], $entityBody['location'], $entityBody['prerequisites'], $entityBody['requirements_id'], $entityBody['credits_required']);
    } 

?>


