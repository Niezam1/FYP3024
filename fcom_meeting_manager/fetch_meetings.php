<?php
// Include database connection
include('config.php');

session_start();
$user_id = $_SESSION['user_id'];

$currentWeek = isset($_GET['week']) ? intval($_GET['week']) : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // Check if the filter is active

// Determine SQL query based on filter status
if ($filter === 'mine') {
    $select_meetings = mysqli_query($conn, "SELECT * FROM `meetings` WHERE `meeting_week` = '$currentWeek' AND FIND_IN_SET('$user_id', meeting_participants) > 0");
    if (!$select_meetings) {
        error_log('SQL Error: ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Error fetching meetings.']);
        exit;
    }    
} else {
    $select_meetings = mysqli_query($conn, "SELECT * FROM `meetings` WHERE `meeting_week` = '$currentWeek'");
    if (!$select_meetings) {
        error_log('SQL Error: ' . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Error fetching meetings.']);
        exit;
    }    
}

// Prepare meetings data for response
$meetings = [];
if (mysqli_num_rows($select_meetings) > 0) {
    while ($meeting = mysqli_fetch_assoc($select_meetings)) {
        // Get participant names
        $participantIds = explode(',', $meeting['meeting_participants']);
        $participantNames = [];
        foreach ($participantIds as $id) {
            $userQuery = mysqli_query($conn, "SELECT user_name FROM `userinfo` WHERE user_id = '$id'");
            if ($userRow = mysqli_fetch_assoc($userQuery)) {
                $participantNames[] = $userRow['user_name'];
            }
        }
        $participantNamesString = implode(', ', $participantNames);

        // Add meeting to response data
        $meetings[] = [
            'meeting_subject' => $meeting['meeting_subject'],
            'meeting_description' => $meeting['meeting_description'],
            'participantNames' => $participantNamesString,
            'meeting_day' => $meeting['meeting_day'],
            'meeting_time' => $meeting['meeting_time'],
            'meeting_date' => $meeting['meeting_date'],
            'date_created' => $meeting['date_created']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $meetings]);
?>
