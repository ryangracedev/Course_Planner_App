<?php
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
?>