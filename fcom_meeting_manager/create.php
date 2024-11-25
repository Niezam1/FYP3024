<?php
    //Connect to the database server
    include 'config.php';

    // Enable all error reporting during development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    //Starting session
    session_start();
    
    //Retrieving user ID from session
    $user_id = $_SESSION['user_id'];
    
    if(!isset($user_id)){
        header('Location: login.php');
        exit();
    }else{
        //Set a cookies that expires in 2 hours
        $cookie_name = "user_id";
        $cookie_value = $user_id;
        $expire_time = time() + (2 * 60 * 60); //Current time + 2 hours

        setcookie($cookie_name, $cookie_value, $expire_time, "/"); //Set the cookie
    }

    $selectedWeek = isset($_GET['week']) ? $_GET['week'] : null;
    $selectedDay = isset($_GET['day']) ? $_GET['day'] : null;
    $selectedTime = isset($_GET['time']) ? $_GET['time'] : null;
    $selectedUsers = isset($_GET['users']) ? explode(',', $_GET['users']) : [];

    // Initialize participants result
    $participantsResult = false;

    // Only run the query if there are selected users
    if (!empty($selectedUsers)) {
        $participantsQuery = "SELECT user_name, user_email 
                            FROM userinfo 
                            WHERE user_id IN (" . implode(',', array_map('intval', $selectedUsers)) . ")";
        $participantsResult = mysqli_query($conn, $participantsQuery);
    }

    // For debugging
    if (!$participantsResult) {
        echo "No participants selected or query error: " . mysqli_error($conn);
    }

    if(isset($_POST['create_meeting'])){
        // Get form data
        $meeting_subject = mysqli_real_escape_string($conn, $_POST['meeting_subject']);
        $meeting_description = mysqli_real_escape_string($conn, $_POST['meeting_description']);
        $meeting_week = mysqli_real_escape_string($conn, $_GET['week']);
        $meeting_day = mysqli_real_escape_string($conn, $_GET['day']);
        $meeting_date = mysqli_real_escape_string($conn, $_POST['meeting_date']);
        
        // Fix the time format
        $meeting_time = $_GET['time'];
        // Convert time to proper MySQL time format (HH:MM:SS)
        if (strpos($meeting_time, ':') !== false) {
            $time_parts = explode(':', $meeting_time);
            $meeting_time = $time_parts[0] . ':00:00';
        } else {
            $meeting_time = $meeting_time . ':00:00';
        }

        $participants = isset($_GET['users']) ? $_GET['users'] : '';
        
        // Validate required fields
        if(empty($meeting_subject) || empty($meeting_description) || empty($meeting_date)){
            echo "<script>alert('Please fill in all fields');</script>";
        } else {
            try {
                // Begin transaction
                mysqli_begin_transaction($conn);
            
                // Log debugging information
                error_log("Meeting Time: " . $meeting_time);
                error_log("Meeting Day: " . $meeting_day);
                error_log("Meeting Week: " . $meeting_week);
                error_log("Meeting Date: " . $meeting_date);
                error_log("Meeting Subject: " . $meeting_subject);
                error_log("Meeting Description: " . $meeting_description);
                error_log("Participants: " . $participants);
            
                // Validate that $meeting_date is in 'YYYY-MM-DD' format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $meeting_date)) {
                    throw new Exception("Invalid meeting date format. Please use 'YYYY-MM-DD'.");
                }
            
                // Insert into meetings table
                $insert_meeting = "INSERT INTO meetings (
                    meeting_subject, 
                    meeting_description, 
                    meeting_participants, 
                    meeting_week, 
                    meeting_day, 
                    meeting_time,
                    meeting_date, 
                    date_created
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
                $stmt = mysqli_prepare($conn, $insert_meeting);
                mysqli_stmt_bind_param($stmt, "sssssss", 
                    $meeting_subject,
                    $meeting_description,
                    $participants,
                    $meeting_week,
                    $meeting_day,
                    $meeting_time,
                    $meeting_date
                );
            
                if (mysqli_stmt_execute($stmt)) {
                    // Get the inserted meeting ID
                    $meeting_id = mysqli_insert_id($conn);
                    
                    // Update the timetable for all participants
                    $participants_array = explode(',', $participants);
                    foreach ($participants_array as $user_id) {
                        // Format time for comparison
                        $time_for_query = (int) date('H', strtotime($meeting_time));

                        if($time_for_query > 12){
                            $time_for_query -= 12;
                        }
                        
                        // Get the cellNo from timetable
                        $get_cell = "SELECT CellNo FROM timetable 
                                     WHERE Week = ? 
                                     AND Day = ?
                                     AND Time = ? 
                                     AND User_id = ?";
                        
                        $stmt_cell = mysqli_prepare($conn, $get_cell);
                        mysqli_stmt_bind_param($stmt_cell, "isis", 
                            $meeting_week,
                            $meeting_day,
                            $time_for_query,
                            $user_id
                        );
                        
                        if (mysqli_stmt_execute($stmt_cell)) {
                            $cell_result = mysqli_stmt_get_result($stmt_cell);
                            
                            if ($cell_row = mysqli_fetch_assoc($cell_result)) {
                                // Update timetable availability to 'meeting'
                                $update_timetable = "UPDATE timetable 
                                                     SET Availability = 'meeting'
                                                     WHERE CellNo = ? 
                                                     AND User_id = ? 
                                                     AND Week = ? 
                                                     AND Day = ?
                                                     AND Time = ?";
                                
                                $stmt_update = mysqli_prepare($conn, $update_timetable);
                                mysqli_stmt_bind_param($stmt_update, "iiisi", 
                                    $cell_row['CellNo'],
                                    $user_id,
                                    $meeting_week,
                                    $meeting_day,
                                    $time_for_query
                                );
                                
                                if (!mysqli_stmt_execute($stmt_update)) {
                                    error_log("Error updating timetable for User ID: $user_id");
                                }
                            }
                        } else {
                            error_log("Error fetching timetable cell for User ID: $user_id");
                        }
                    }
            
                    // Commit transaction
                    mysqli_commit($conn);
                    
                    echo "<script>
                            alert('Meeting created successfully!');
                            window.location.href = 'dean_meeting.php';
                          </script>";
                } else {
                    throw new Exception("Error inserting meeting data");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                error_log("Meeting Creation Error: " . $e->getMessage());
                var_dump($e->getMessage()); // This will show the error message on the page for debugging
                echo "<script>alert('Error creating meeting. Please try again.');</script>";
            }            
        }
    }

?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Create</title>
        <link rel="stylesheet" href="./css/dean_header.css">

        <style>
            .form-container{
                display:block;
                justify-content:center;
            }

            .time-container{
                display:inline-flex;
                width: 100%;
                height:50px;
                justify-content:center;
                align-items:center;
            }

            .time-container h3{
                margin:20px 50px 0;
            }

            .participants-container{
                display:flex;
                justify-content:center;
            }

            .participants-container h3{
                border-bottom:2px solid black;
                margin-top:20px;
                margin-bottom:20px;
            }
            
            .meeting-detail {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                width: 70%;
                padding: 20px;
                margin: 0 auto;
                text-align: center;
                height:fit-content;
            }

            .meeting-detail h3{
                border-bottom:2px solid black;
                margin-bottom:10px;
            }

            .text_field {
                width: 100%;
                margin: 10px 0;
                border:2px solid black;
                color:black;
                font-size:15px;
                padding:13px 13px;
                margin:5px 0;
                border-radius:8px;
            }

            textarea {
                width: 100%;
                margin: 10px 0;
            }

            .btn{
                background-color:green;
                color:white;
                font-size:20px;
                border-radius:8px;
                padding:10px 20px;
                margin-top:10px;
                margin-bottom:100px;
            }

            .btn:hover{
                background-color:lime;
                cursor:pointer;
            }

            .participants-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px auto;
    max-width: 600px;
}

.participants-container h3 {
    color: #2c3e50;
    font-size: 1.5em;
    padding-bottom: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #3498db;
    text-align: center;
}

.participants-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.participants-list .subheading-container{
    display:flex;
    justify-content:center;
    margin-top:20px;
}

.participants-list h3{
    width:fit-content;
    border-bottom:2px solid black;
}

.participant-card {
    display: flex;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.participant-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.participant-avatar {
    width: 40px;
    height: 40px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-weight: bold;
}

.participant-info {
    flex-grow: 1;
}

.participant-name {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 4px;
    font-size: 1.1em;
}

.participant-email {
    color: #7f8c8d;
    font-size: 0.9em;
}

.no-participants {
    text-align: center;
    color: #7f8c8d;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    font-style: italic;
}

.date-container{
    border:2px solid black;
    border-radius:8px;
    justify-content:center;
    display:flex;
    width:100%
}

.meeting-date{
    width:fit-content;
    text-align:center;
    margin-bottom:5px;
    margin-top:5px;
    display:flex;
    justify-content:center;
}
        </style>
    </head>
    <body>
        <header>
            <img style="height:40px;
                        margin-left:10px;
                        cursor:pointer;
                        color:white;"
                        src="images/hamburger_icon.png" class="hamburger-pic" onclick="toggleHamburger()"
            />

            <nav>
                <ul>
                    <li><a href="dean_index.php">Home</a></li>
                    <li><a href="dean_meeting.php">Meeting</a></li>
                    <li><a href="dean_search.php">Search</a></li>
                </ul>
            </nav>

            <img style="height:40px; 
                margin-right:10px; 
                cursor:pointer;" 
                src="images/user-icon_master.png" class="user-pic" onclick="toggleMenu()" 
            />
        </header>

        <div id="sidebar" class="sidebar">
            <button class="closebtn" onclick="toggleHamburger()">&times;</button>
            <div class="sidebar-content">
                <h2>Menu</h2>
                <ul>
                    <li><a href="dean_search.php">Search</a></li>
                </ul>
            </div>
        </div>

        <div class="sub-menu-wrap" id="subMenu">
            <div class="sub-menu">
            <!--User Information-->
                <div class="user-info">
                    <img src="images/user-icon_master.png" />
                    <div class="user-info" style="display: block;">
                        <h3>Hello, <?php echo $_SESSION['user_name']?></h3>
                        <h3><?php echo $_SESSION['user_email']?></h3>
                    </div>
                </div>
                <hr />
                <!--Logout link-->
                <a href="logout.php" class="sub-menu-link">
                    <p style="
                        background-color:red; 
                        border-radius:8px; 
                        text-align:center; 
                        color:white; 
                        font-weight:600; 
                        margin-top:5px; 
                        padding:5px;">Logout
                    </p>
                </a>
            </div>
        </div>

        <div class="form-container">
            <form action="" method="POST">
            <input type="hidden" name="week" value="<?php echo htmlspecialchars($selectedWeek); ?>">
    <input type="hidden" name="day" value="<?php echo htmlspecialchars($selectedDay); ?>">
    <input type="hidden" name="time" value="<?php echo htmlspecialchars($selectedTime); ?>">
    <input type="hidden" name="participants" value="<?php echo htmlspecialchars(implode(',', $selectedUsers)); ?>">
 
            <div class="time-container">
    <h3>Week: <?php echo $selectedWeek ? htmlspecialchars($selectedWeek) : 'Not selected'; ?></h3>
    <h3>Day: <?php echo $selectedDay ? htmlspecialchars($selectedDay) : 'Not selected'; ?></h3>
    <h3>Time: <?php echo $selectedTime ? htmlspecialchars($selectedTime) : 'Not selected'; ?></h3>
</div>

<div class="participants-list">
            <div class="subheading-container">
                <h3>Meeting Participants</h3>
            </div>
            <?php if ($participantsResult && mysqli_num_rows($participantsResult) > 0): ?>
                <?php while ($participant = mysqli_fetch_assoc($participantsResult)): ?>
                    <div class="participant-card">
                        <div class="participant-avatar">
                            <?php echo strtoupper(substr($participant['user_name'], 0, 1)); ?>
                        </div>
                        <div class="participant-info">
                            <h4><?php echo htmlspecialchars($participant['user_name']); ?></h4>
                            <p><?php echo htmlspecialchars($participant['user_email']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No participants selected</p>
            <?php endif; ?>
        </div>

                <div class="meeting-detail">
                    <h3>Meeting Details</h3>

                    <input type="text" name="meeting_subject" placeholder="Enter Meeting Subject" class="text_field">
                    <textarea name="meeting_description" placeholder="Enter Meeting Description" class="text_field" cols="18" rows="5"></textarea>
                    <div class="date-container">
                        <input type="date" name="meeting_date" placeholder="Select A Date" min="2024-01-01" max="2040-12-31" class="meeting-date">
                    </div>
                    <span style="color:red;
                                 margin-top:5px;"><strong>PLEASE MAKE SURE THE DATE IS CORRECT</strong></span>
                    <input type="submit" value="Create Meeting" name="create_meeting" class="btn">
                </div>
            </form>
        </div>

        <script>
            let subMenu = document.getElementById("subMenu");
            let currentWeek = 1;

            function toggleHamburger() {
                const sidebar = document.getElementById('sidebar');
                const isOpen = sidebar.style.left === '0px'; // Check if sidebar is open

                // Toggle the sidebar position
                sidebar.style.left = isOpen ? '-250px' : '0px';
            }

            function toggleMenu(){
                subMenu.classList.toggle("open-menu");
            }

            function fetchParticipants() {
    // Get selected user IDs from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const users = urlParams.get('users'); // This gets the users from the URL
    
    if (!users) {
        console.log('No users in URL parameters');
        return;
    }

    const selectedUsers = users.split(','); // Split the comma-separated user IDs
    const week = <?php echo $selectedWeek ?? 1; ?>; // Get the current week, default to 1 if not set
    
    // Build the query string properly
    const queryParams = new URLSearchParams();
    queryParams.set('week', week);
    selectedUsers.forEach(userId => {
        queryParams.append('check[]', userId);
    });

    const url = `get_availability_checkbox.php?${queryParams.toString()}`;
    
    console.log('Fetching participants with URL:', url); // Debug log

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            
            if (data.success && data.data.length > 0) {
                const participantsList = document.getElementById('participants-list');
                if (participantsList) {
                    participantsList.innerHTML = ''; // Clear existing list
                    
                    // Create a Set to store unique users
                    const uniqueUsers = new Set();
                    
                    data.data.forEach(item => {
                        // Only add each user once
                        if (!uniqueUsers.has(item.userId)) {
                            uniqueUsers.add(item.userId);
                            
                            const participantCard = document.createElement('div');
                            participantCard.className = 'participant-card';
                            
                            participantCard.innerHTML = `
                                <div class="participant-avatar">
                                    ${item.userName.charAt(0).toUpperCase()}
                                </div>
                                <div class="participant-info">
                                    <div class="participant-name">${item.userName}</div>
                                    <div class="participant-email">${item.userEmail || ''}</div>
                                </div>
                            `;
                            
                            participantsList.appendChild(participantCard);
                        }
                    });
                }
            } else {
                console.log('No participants found or empty data received');
                const participantsList = document.getElementById('participants-list');
                if (participantsList) {
                    participantsList.innerHTML = '<div class="no-participants">No participants found</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching participants:', error);
            const participantsList = document.getElementById('participants-list');
            if (participantsList) {
                participantsList.innerHTML = '<div class="no-participants">Error loading participants</div>';
            }
        });
}

// Call fetchParticipants when the page loads
document.addEventListener('DOMContentLoaded', fetchParticipants);

            // fetchParticipants();

         currentWeek = sessionStorage.getItem("currentWeek") || "Unknown";
         console.log(currentWeek);
            // document.getElementById("current-week-display").innerText = `Week ${currentWeek}`;

            document.querySelector('form').addEventListener('submit', function(e) {
        const subject = document.querySelector('input[name="meeting_subject"]').value;
        const description = document.querySelector('textarea[name="meeting_description"]').value;
        const meetingdate = document.querySelector('input[name="meeting_date"]').value;
        
        if (!subject || !description || !meetingdate) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });

    function updateTimetableCells(data) {
    console.log('Updating cells with data:', data); // Debug log

    // Reset all cells to red by default
    document.querySelectorAll('.day').forEach(cell => {
        cell.style.backgroundColor = 'red';
        cell.style.cursor = 'not-allowed';
        cell.classList.remove('highlight');
        cell.title = 'Not available';
    });

    if (!data || data.length === 0) {
        console.log('No data to update cells'); // Debug log
        return;
    }

    // Group availability data by CellNo
    const cellAvailabilityMap = {};
    data.forEach(item => {
        const cellNo = item.cellNo;
        if (!cellAvailabilityMap[cellNo]) {
            cellAvailabilityMap[cellNo] = [];
        }
        cellAvailabilityMap[cellNo].push({
            availability: item.availability,
            userId: item.userId,
            userName: item.userName
        });
    });

    console.log('Grouped data:', cellAvailabilityMap); // Debug log

    // Get count of selected users
    const selectedUserCount = document.querySelectorAll('input[name="check[]"]:checked').length;

    // Update cell colors based on the grouped availability
    for (const cellNo in cellAvailabilityMap) {
        const cell = document.getElementById(cellNo);
        if (cell) {
            const availabilityList = cellAvailabilityMap[cellNo];
            const allUsersHaveData = availabilityList.length === selectedUserCount;
            
            if (allUsersHaveData) {
                // Check availability status
                const allFree = availabilityList.every(user => user.availability === 'free');

                // Set cell color and tooltip
                if (allFree) {
                    cell.style.backgroundColor = 'green';
                    cell.style.cursor = 'pointer';
                } else {
                    cell.style.backgroundColor = 'red';
                }

                // Set tooltip
                const tooltipText = availabilityList.map(user => 
                    `${user.userName}: ${user.availability}`
                ).join('\n');
                cell.title = tooltipText;
            }
        }
    }
}

    // Update the Apply button click handler
document.getElementById('applyBtn').addEventListener('click', function() {
    const selectedUsers = Array.from(document.querySelectorAll('input[name="check[]"]:checked'))
        .map(checkbox => checkbox.value);

    console.log('Selected Users:', selectedUsers); // Debug log

    if (selectedUsers.length === 0) {
        alert('Please select at least one user.');
        return;
    }

    // Create the URL with proper parameter format
    const queryParams = new URLSearchParams();
    queryParams.set('week', currentWeek);
    selectedUsers.forEach(userId => {
        queryParams.append('check[]', userId);
    });

    const url = `get_availability_checkbox.php?${queryParams.toString()}`;
    console.log('Request URL:', url); // Debug log

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            if (data.success) {
                updateTimetableCells(data.data);
            } else {
                console.error('Error:', data.message);
                alert('Failed to fetch availability data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching timetable data:', error);
            alert('An error occurred while fetching availability data.');
        });
});
        </script>
    </body>
</html>