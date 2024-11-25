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
?>

<!DOCTYPE HTML>
<html lang="en">
    <head></head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Meeting</title>
        <link rel="stylesheet" href="./css/Home.css">

        <style type="text/css">
            .hamburger-pic {
                cursor: pointer;
                width: 40px;
                margin: 10px;
            }

            #sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                height: 100%;
                background-color: #333;
                color: white;
                transition: left 0.3s ease;
                z-index: 1000;
            }

            .sidebar-content {
                padding: 20px;
            }
            
            .sidebar h2 {
                margin: 0;
                font-size: 24px;
            }

            .sidebar ul {
                list-style-type: none;
                padding: 0;
            }

            .sidebar ul li {
                margin: 15px 0;
            }

            .sidebar ul li a {
                color: white;
                text-decoration: none;
            }

            .sidebar .closebtn {
                background: none;
                border: none;
                color: white;
                font-size: 30px;
                cursor: pointer;
                position: absolute;
                top: 10px;
                right: 20px;
            }

            .container{
                display:flex;
                width:100%;
            }

            .meeting-week{
                display:flex;
                align-items:center;
                justify-content:space-between;
                border-bottom:2px solid black;
                width:50%;
                height:50px;
                margin-right:auto;
                margin-left:auto;
                padding:0 50px;
            }

            .meeting-week .prev,
            .meeting-week .next{
                cursor:pointer;
            }

            .sub-menu-wrap{
                position: fixed;
                top: 9%;
                right: -1%;
                width: 320px;
                max-height: 0px;
                overflow: hidden;
                transition: max-height 0.5s;
                z-index: 2000;
            }

            .sub-menu-wrap.open-menu{
                max-height: 400px;
            }

            .sub-menu{
                background: #018fd1;
                padding: 20px;
                margin: 10px;
                border-bottom-right-radius: 16px;
                border-bottom-left-radius: 16px;
            }

            .user-info{
                display: flex;
                align-items: center;
            }

            .user-info h3{
                font-weight: 500;
                font-family: "Poppins", sans-serif;
                color:rgb(214, 226, 232);
            }

            .user-info img{
                width: 60px;
                border-radius: 50%;
                margin-right: 15px;
            }

            .sub-menu hr{
                border: 0;
                height: 1px;
                width: 100%;
                background: #ccc;
                margin: 15px 10px;
            }

            .sub-menu-link{
                display: flex;
                align-items: center;
                text-decoration: none;
                color: #525252;
                margin: 12px 0;
            }

            .sub-menu-link p{
                width: 100%;
            }

            .sub-menu-link:hover p{
                font-weight: 600;
            }

            .link-container{
                position:absolute;
                height:fit-content;
                margin:0;
            }

            .new-meeting{
                width:110px;
                height:40px;
                background-color:green;
                color:white;
                cursor:pointer;
                text-align:center;
                text-decoration:none;
                display:flex;
                align-items:center;
                justify-content:center;
                border-radius:3px;
            }

            .new-meeting:hover{
                background-color:lime;
            }

            .container{
                min-height: 100px;
            }

            .content-container{
                width: 100%;
                min-height:800px;
                display:flex;
                flex-direction:column;
                align-items:center;
                margin-bottom:200px;
            }

            .meeting-box {
                display: flex;
                flex-direction: column;
                gap: 20px;
                width:60%;
            }

            .box {
                border: 5px solid #ccc;
                padding: 15px;
                border-radius: 8px;
                background-color: #f9f9f9;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            }

            .box p {
                margin: 5px 0;
            }

            .box p span {
                font-weight: bold;
                color: #333;
            }

            .filterbtn{
                background-color:#28a745;
                color:white;
                padding:10px 0;
                width:200px;
                font-weight:700;
                border-radius:10px;
                margin-bottom:10px;
                cursor:pointer;
            }

            .filterbtn:hover{
                background-color:#218838;
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
                        padding:5px;">Log Out
                    </p>
                </a>
            </div>
        </div>

        <div class="container">

            <div class="link-container">
                <a class="new-meeting" href="new-meeting.php">New Meeting</a>
            </div>

            <div class="meeting-week">
                <i class="fa fa-angle-left prev" onclick="changeWeek(-1)"></i>
                <h3>Meetings for Week <span id="weekNumber"></span></h3>
                <i class="fa fa-angle-right next" onclick="changeWeek(1)"></i>
            </div>
        </div>

        <div class="content-container">
            <button class="filterbtn" onclick="toggleFilter()" id="filterButton">Show My Meetings Only</button>
            <div class="meeting-box" id="meetingBox">
                <?php
                    include 'fetch_meetings.php';
                    // Get the week number from the query or default to 1
                    $currentWeek = isset($_GET['week']) ? intval($_GET['week']) : 1;
                    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // Check if the filter is active

                    // Determine SQL query based on filter status
                    if ($filter === 'mine') {
                        $select_meetings = mysqli_query($conn, "SELECT * FROM `meetings` WHERE `meeting_week` = '$currentWeek' AND FIND_IN_SET('$user_id', meeting_participants) > 0") or die('Query failed');
                    } else {
                        $select_meetings = mysqli_query($conn, "SELECT * FROM `meetings` WHERE `meeting_week` = '$currentWeek'") or die('Query failed');
                    }

                    // Display meeting boxes
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
                ?>
                            <div class="box">
                                <p> Subject : <span><?php echo $meeting['meeting_subject']; ?></span> </p>
                                <p> Description : <span><?php echo $meeting['meeting_description']; ?></span> </p>
                                <p> Participants : <span><?php echo $participantNamesString; ?></span> </p>
                                <p> Day : <span><?php echo $meeting['meeting_day']; ?></span> </p>
                                <p> Time : <span><?php echo $meeting['meeting_time']; ?></span> </p>
                                <p> Date : <span><?php echo $meeting['meeting_date']; ?></span> </p>
                                <p> Created On : <span><?php echo $meeting['date_created']; ?></span> </p>
                            </div>
                <?php
                        }
                    } else {
                        echo '<p class="empty">No meetings found for this week.</p>';
                    }
                ?>
            </div>
        </div>

        <script type="text/javascript">
            let currentWeek = 1; // Default to week 1
            let filter = 'all'; // Default filter

            function toggleHamburger() {
                const sidebar = document.getElementById('sidebar');
                const isOpen = sidebar.style.left === '0px'; // Check if sidebar is open

                // Toggle the sidebar position
                sidebar.style.left = isOpen ? '-250px' : '0px';
            }

            function toggleMenu(){
                subMenu.classList.toggle("open-menu");
            }

            // Update the displayed week number
            function updateWeekDisplay() {
                document.getElementById('weekNumber').innerText = currentWeek;
            }

            // Change week and update the URL and timetable
            function changeWeek(weekChange) {
                currentWeek += weekChange;

                // Prevent week number from going below 1 or above 14
                if (currentWeek < 1) {
                    currentWeek = 1;
                } else if (currentWeek > 14) {
                    currentWeek = 14;
                }

                // Update the URL with the current week
                const url = new URL(window.location);
                url.searchParams.set('week', currentWeek);
                window.history.pushState({}, '', url);

                // Fetch the meetings for the new week
                fetchMeetingsForWeek(currentWeek, filter);

                // Update the displayed week number
                updateWeekDisplay();
            }

            // Toggle the filter between 'mine' and 'all'
            function toggleFilter() {
                filter = (filter === 'all') ? 'mine' : 'all';
                const buttonText = (filter === 'all') ? 'Show My Meetings Only' : 'Show All Meetings';
                document.getElementById('filterButton').textContent = buttonText;

                // Fetch meetings with the new filter
                fetchMeetingsForWeek(currentWeek, filter);
            }

            // Fetch meetings for the selected week and filter
            function fetchMeetingsForWeek(week, filter) {
                const meetingBox = document.getElementById('meetingBox');
                meetingBox.innerHTML = '<p>Loading meetings...</p>'; // Show loading text

                console.log(`Fetching URL: fetch_meetings.php?week=${week}&filter=${filter}`);

                // Send AJAX request to fetch meetings
                fetch(`fetch_meetings.php?week=${week}&filter=${filter}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response data:', data); // Check the structure here
                        meetingBox.innerHTML = ''; // Clear previous content

                        if (data.success) {
                            data.data.forEach(meeting => {
                                const meetingElement = document.createElement('div');
                                meetingElement.className = 'box';
                                meetingElement.innerHTML = `
                                <p> Subject : <span>${meeting.meeting_subject}</span> </p>
                                <p> Description : <span>${meeting.meeting_description}</span> </p>
                                <p> Participants : <span>${meeting.participantNames}</span> </p>
                                <p> Day : <span>${meeting.meeting_day}</span> </p>
                                <p> Time : <span>${meeting.meeting_time}</span> </p>
                                <p> Date : <span>${meeting.meeting_date}</span> </p>
                                <p> Created On : <span>${meeting.date_created}</span> </p>
                                `;
                                meetingBox.appendChild(meetingElement);
                            });
                        } else {
                            meetingBox.innerHTML = `<p class="empty">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching meetings:', error);
                        meetingBox.innerHTML = `<p class="empty">Error fetching meetings. Please try again later.</p>`;
                    });
            }

            // Initial page load actions
            window.onload = function() {
                const urlParams = new URLSearchParams(window.location.search);
                const savedWeek = urlParams.get('week'); // Get 'week' from URL
                if (savedWeek) {
                    currentWeek = parseInt(savedWeek, 10);
                } else {
                    currentWeek = 1; // Default to week 1 if no week is in the URL
                }

                updateWeekDisplay(); // Show the saved week
                fetchMeetingsForWeek(currentWeek, filter); // Load meetings for the current week
            }
        </script>
    </body>
</html>