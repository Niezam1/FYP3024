<?php
include 'config.php';

// Starting session
session_start();
$user_id = $_SESSION['user_id'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (isset($_GET['week']) && isset($_GET['check'])) {
    $currentWeek = intval($_GET['week']);
    $userChecked = array_map('intval', $_GET['check']); // Convert to integers
    
    // Debug logs
    error_log("Week: " . $currentWeek);
    error_log("Users: " . implode(',', $userChecked));

    if (!empty($userChecked)) {
        $userIds = implode(',', $userChecked);
        
        $query = "SELECT DISTINCT t.CellNo, t.availability, t.user_Id, 
                         u.user_name, u.user_email 
                  FROM timetable t
                  JOIN userinfo u ON t.user_Id = u.user_id 
                  WHERE t.Week = ? AND t.user_Id IN ($userIds)
                  ORDER BY u.user_name";

        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $currentWeek);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $response = [
                        'success' => true,
                        'data' => []
                    ];
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $response['data'][] = [
                            'cellNo' => $row['CellNo'],
                            'availability' => $row['availability'],
                            'userId' => $row['user_Id'],
                            'userName' => $row['user_name'],
                            'userEmail' => $row['user_email']
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => "No data found for week $currentWeek and users $userIds"
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => "Query execution failed: " . mysqli_error($conn)
                ];
            }
            mysqli_stmt_close($stmt);
        } else {
            $response = [
                'success' => false,
                'message' => "Query preparation failed: " . mysqli_error($conn)
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => "No valid users selected"
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => "Missing required parameters (week or check)"
    ];
}

echo json_encode($response);
?>