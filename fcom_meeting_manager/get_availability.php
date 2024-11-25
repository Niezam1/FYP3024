<?php

//database connection
include 'config.php';

// Starting session
session_start();
$user_id = $_SESSION['user_id'];

//for check error purpose
error_reporting(E_ALL);
ini_set('display_errors', 1);

//check if user click next or previous week
if (isset($_GET['week'])) {
    
    //if week is exist then get the week value to search in database
    $currentWeek = intval($_GET['week']);
    
    $response = ['success' => false, 'data' => []];

    //select all the cellNo, and availalability from timetable table based on week's value and user id
    $query = "SELECT CellNo, availability FROM timetable WHERE `Week` = '$currentWeek' AND `User_Id` = '$user_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $response['success'] = true;
        while ($row = mysqli_fetch_assoc($result)) {
            $response['data'][$row['CellNo']] = $row['availability'];
        }
    } else {
        $response['message'] = "No data found for week $currentWeek";
    }

    echo json_encode($response);
}
?>
