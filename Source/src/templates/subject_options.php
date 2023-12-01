<?php
$db_name = "course_data";
$db = include_once(__DIR__."/../database.php");

function is_selected($target, $sbj) {
    if($target === $sbj) {
        return 'selected="selected"';
    }

    return "";
}

$subjects = $db->query("SELECT DISTINCT subject_area FROM courses");

while($row = mysqli_fetch_assoc($subjects)) {
    echo "<option value=" . $row['subject_area'];
    // is_selected($subject_selected, $row['subject_area'])
    if(!isset($subject_selected)){
        $subject_selected = "ACCT";
    }
    if($subject_selected === $row['subject_area']){ echo ' selected';  };
    echo ">";
    echo $row['subject_area'];
    echo "</option>";
}