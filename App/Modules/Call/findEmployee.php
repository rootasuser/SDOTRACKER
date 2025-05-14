<?php

require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;  // PDO connection


$searchQuery = isset($_POST['search_query']) ? $_POST['search_query'] : '';
$assignSchool = isset($_POST['assign_school']) ? $_POST['assign_school'] : '';
$position = isset($_POST['position']) ? $_POST['position'] : '';
$teachingSubject = isset($_POST['teaching_subject']) ? $_POST['teaching_subject'] : '';

$query = "SELECT * FROM employees WHERE 1";

if (!empty($searchQuery)) {
    $query .= " AND (name LIKE :searchQuery OR empAssignSchool LIKE :searchQuery OR empPosition LIKE :searchQuery OR empTeachingSubject LIKE :searchQuery)";
}

if (!empty($assignSchool)) {
    $query .= " AND empAssignSchool = :assignSchool";
}

if (!empty($position)) {
    $query .= " AND empPosition = :position";
}

if (!empty($teachingSubject)) {
    $query .= " AND empTeachingSubject = :teachingSubject";
}


$stmt = $conn->prepare($query);


if (!empty($searchQuery)) {
    $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
}

if (!empty($assignSchool)) {
    $stmt->bindValue(':assignSchool', $assignSchool, PDO::PARAM_STR);
}

if (!empty($position)) {
    $stmt->bindValue(':position', $position, PDO::PARAM_STR);
}

if (!empty($teachingSubject)) {
    $stmt->bindValue(':teachingSubject', $teachingSubject, PDO::PARAM_STR);
}


$stmt->execute();

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($employees) {
    echo json_encode([
        'status' => 'success',
        'data' => $employees
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No matching employees found.'
    ]);
}

?>
