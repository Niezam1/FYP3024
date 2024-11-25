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
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>New Meeting</title>
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

            .content_container{
                display:flex;
                flex-direction:column;
                align-items: center;
                width:100%;
                min-height:850px;
                min-width:1200px;
                padding-top:10px;
                gap: 400px;
                margin-bottom:100px;
            }

            .userList_container{
                text-align:center;
                width:1000px;
                margin-top:1000px auto 0;
                background:#f9f9f9;
                box-shadow:0px 4px 10px rgba(0, 0, 0, 0.1);
                border-radius:10px;
            }

            .filter_container{
                display:flex;
                flex-wrap:wrap;
                padding:0;  
                width:100%;
                justify-content:space-between;
                align-items:center;
                padding-bottom:5px;
                gap:20px;
            }

            .search_container{
                margin:auto 5px;
                flex:1;
            }

            .apply_container{
                width:30%;
                display:flex;
                justify-content:center;
            }

            #new-meeting{
                text-decoration:none;
                background-color:#2dcc2d;
                padding:10px 20px;
                color:white;
                border-radius:5px;
                font-size:1rem;
                font-weight:bold;
                transition: background-color 0.3s ease;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                cursor:pointer;
            }

            #new-meeting:hover{
                background-color:#39e639;
            }

            table{
                width:100%;
                border-collapse:collapse;
                margin-top:10px;
            }

            table td, table th{
                padding:12px;
                text-align:left;
                border:1px solid #ddd;
            }

            table th{
                background-color:#007bff;
                color:white;
                font-weight:bold;
                text-align:center;
            }

            table tbody tr:nth-child(even){
                background-color:#ffffff;
            }

            table tbody tr:nth-child(odd){
                background-color:#f4f4f4;
            }

            table tbody tr:hover{
                background-color:#eaf4ff;
            }

            input[type="checkbox"]{
                margin:0 auto;
                cursor:pointer;
            }

            #search{
                font-size:1rem;
                border:2px solid rgb(0, 123, 255);
                border-radius:5px;
                padding:8px 12px;
                width:100%;
                height:30px;
                box-sizing:border-box;
                outline:none;
                transition: border-color 0.3s ease-in-out;
            }

            #search:focus{
                border-color:#0056b3;
            }

            .apply_container button{
                width:30%;
                background-color:#007BFF;
                border-radius:5px;
                font-size:1rem;
                cursor:pointer;
                border:none;
                color:white;
                text-align:center;
                padding:10px 20px;
                transition:background-color 0.3s ease;
            }

            .apply_container button:hover{
                background-color: #0056b3;
            }

            .no-users-message{
                text-align:center;
                color:#666;
                font-style:italic;
                padding:20px 0;
            }

            .form{
                margin-top:410px;
            }

            .form input, textarea{
                border:1px solid black;
            }

            .backBtn{
                color:black;
                border:1px solid black;
            }

            .day.highlight{
                box-shadow: 0 0 6px 3px #00d4ff,   /* Inner bright glow */
                            0 0 12px 6px #00baff,  /* Middle glow */
                            0 0 18px 9px #00a3e0;  /* Outer, softer glow */
                            transition: box-shadow 0.3s ease; /* Smooth transition */
            }

            .day{
                transition: all 0.3s ease;
                position: relative;
            }

            .day[style*="background-color: green"] {
                box-shadow: 0 0 5px rgba(0,255,0,0.3);
            }

            .day[style*="background-color: red"] {
                opacity: 0.8;
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

        <div class="content_container">
            <div class="timetable">
                <div class="week">
                    <i class="fa fa-angle-left prev" onclick="changeWeek(-1)"></i>
                    <div class="date" id="weekNumber">Week 1</div>
                    <i class="fa fa-angle-right next" onclick="changeWeek(1)"></i>
                </div>
                <div class="timetable-container" id="timetableContent">
                    <!--Timetable header cells-->
                    <div class="blank-cell" id="blank"></div>
                    <div>8<br>8:00-9:00</div>
                    <div>9<br>9:00-10:00</div>
                    <div>10<br>10:00-11:00</div>
                    <div>11<br>11:00-12:00</div>
                    <div>12<br>12:00-1:00</div>
                    <div>1<br>1:00-2:00</div>
                    <div>2<br>2:00-3:00</div>
                    <div>3<br>3:00-4:00</div>
                    <div>4<br>4:00-5:00</div>
                    <div>5<br>5:00-6:00</div>
                    <div>6<br>6:00-7:00</div>

                    <!-- Timetable for each day -->
                    <div class="days">Mo</div>
                    <div class="day" id="1" data-hidden-value="Monday-8"></div>
                    <div class="day" id="2" data-hidden-value="Monday-9"></div>
                    <div class="day" id="3" data-hidden-value="Monday-10"></div>
                    <div class="day" id="4" data-hidden-value="Monday-11"></div>
                    <div class="day" id="5" data-hidden-value="Monday-12"></div>
                    <div class="day" id="6" data-hidden-value="Monday-1"></div>
                    <div class="day" id="7" data-hidden-value="Monday-2"></div>
                    <div class="day" id="8" data-hidden-value="Monday-3"></div>
                    <div class="day" id="9" data-hidden-value="Monday-4"></div>
                    <div class="day" id="10" data-hidden-value="Monday-5"></div>
                    <div class="day" id="11" data-hidden-value="Monday-6"></div>
                    <div class="days">Tu</div>
                    <div class="day" id="12" data-hidden-value="Tuesday-8"></div>
                    <div class="day" id="13" data-hidden-value="Tuesday-9"></div>
                    <div class="day" id="14" data-hidden-value="Tuesday-10"></div>
                    <div class="day" id="15" data-hidden-value="Tuesday-11"></div>
                    <div class="day" id="16" data-hidden-value="Tuesday-12"></div>
                    <div class="day" id="17" data-hidden-value="Tuesday-1"></div>
                    <div class="day" id="18" data-hidden-value="Tuesday-2"></div>
                    <div class="day" id="19" data-hidden-value="Tuesday-3"></div>
                    <div class="day" id="20" data-hidden-value="Tuesday-4"></div>
                    <div class="day" id="21" data-hidden-value="Tuesday-5"></div>
                    <div class="day" id="22" data-hidden-value="Tuesday-6"></div>
                    <div class="days">We</div>
                    <div class="day" id="23" data-hidden-value="Wednesday-8"></div>
                    <div class="day" id="24" data-hidden-value="Wednesday-9"></div>
                    <div class="day" id="25" data-hidden-value="Wednesday-10"></div>
                    <div class="day" id="26" data-hidden-value="Wednesday-11"></div>
                    <div class="day" id="27" data-hidden-value="Wednesday-12"></div>
                    <div class="day" id="28" data-hidden-value="Wednesday-1"></div>
                    <div class="day" id="29" data-hidden-value="Wednesday-2"></div>
                    <div class="day" id="30" data-hidden-value="Wednesday-3"></div>
                    <div class="day" id="31" data-hidden-value="Wednesday-4"></div>
                    <div class="day" id="32" data-hidden-value="Wednesday-5"></div>
                    <div class="day" id="33" data-hidden-value="Wednesday-6"></div>
                    <div class="days">Th</div>
                    <div class="day" id="34" data-hidden-value="Thursday-8"></div>
                    <div class="day" id="35" data-hidden-value="Thursday-9"></div>
                    <div class="day" id="36" data-hidden-value="Thursday-10"></div>
                    <div class="day" id="37" data-hidden-value="Thursday-11"></div>
                    <div class="day" id="38" data-hidden-value="Thursday-12"></div>
                    <div class="day" id="39" data-hidden-value="Thursday-1"></div>
                    <div class="day" id="40" data-hidden-value="Thursday-2"></div>
                    <div class="day" id="41" data-hidden-value="Thursday-3"></div>
                    <div class="day" id="42" data-hidden-value="Thursday-4"></div>
                    <div class="day" id="43" data-hidden-value="Thursday-5"></div>
                    <div class="day" id="44" data-hidden-value="Thursday-6"></div>
                    <div class="days">Fr</div>
                    <div class="day" id="45" data-hidden-value="Friday-8"></div>
                    <div class="day" id="46" data-hidden-value="Friday-9"></div>
                    <div class="day" id="47" data-hidden-value="Friday-10"></div>
                    <div class="day" id="48" data-hidden-value="Friday-11"></div>
                    <div class="day" id="49" data-hidden-value="Friday-12"></div>
                    <div class="day" id="50" data-hidden-value="Friday-1"></div>
                    <div class="day" id="51" data-hidden-value="Friday-2"></div>
                    <div class="day" id="52" data-hidden-value="Friday-3"></div>
                    <div class="day" id="53" data-hidden-value="Friday-4"></div>
                    <div class="day" id="54" data-hidden-value="Friday-5"></div>
                    <div class="day" id="55" data-hidden-value="Friday-6"></div>
                </div>
            </div>

            <form id="userForm" method="GET">
                <div class="userList_container">
                    <div class="filter_container">
                        <div class="search_container">
                            <input type="search" id="search" placeholder="Search name...">
                        </div>

                        <div class="apply_container">
                            <button type="button" id="applyBtn" value="apply">Apply</button>
                        </div>
                        
                        <a id="new-meeting" href="create.php">Create</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th><!--Checkboxes--></th>
                            </tr>   
                        </thead>
                        <tbody id="userTable">
                            <?php
                                $select_user = $conn->query("SELECT user_id, user_name, user_email FROM userinfo");
                                if($select_user->num_rows > 0){
                                    while ($row = $select_user->fetch_assoc()){
                            ?>
                                    <tr class="user-row">
                                        <td><?php echo $row['user_name']; ?></td>
                                        <td><?php echo $row['user_email']; ?></td>
                                        <td>
                                            <input type="checkbox" name="check[]" value="<?php echo $row['user_id']; ?>">
                                        </td>
                                    </tr>
                            <?php
                                    }
                                } else{
                                    echo '<tr><td colspan="3">No user</td></tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </form>  
        </div>

        <script>
            let subMenu = document.getElementById("subMenu");
            let currentWeek = 1;

            function updateWeekDisplay() {
                document.getElementById('weekNumber').innerText = `Week ${currentWeek}`;
            }

            function toggleHamburger() {
                const sidebar = document.getElementById('sidebar');
                const isOpen = sidebar.style.left === '0px'; // Check if sidebar is open

                // Toggle the sidebar position
                sidebar.style.left = isOpen ? '-250px' : '0px';
            }

            function toggleMenu(){
                subMenu.classList.toggle("open-menu");
            }

            // Change week and update the URL and timetable
            function changeWeek(weekChange) {
                currentWeek += weekChange;

                if (currentWeek < 1) {
                    currentWeek = 1;
                } else if (currentWeek > 14) {
                    currentWeek = 14;
                }

                updateWeekDisplay();

                const url = new URL(window.location);
                url.searchParams.set('week', currentWeek);
                window.history.pushState({}, '', url);

                let selectedUsers = [];
                document.querySelectorAll('input[name="check[]"]:checked').forEach((checkbox) => {
                    selectedUsers.push(checkbox.value);
                });
                
                if (selectedUsers.length > 0) {
                    updateTimetable(selectedUsers);
                }
            }

            // Get the highlighted timeslot and selected users when clicking Create
            document.getElementById("new-meeting").addEventListener("click", function(event) {
                event.preventDefault();
    
                const selectedUsers = [];
                document.querySelectorAll('input[name="check[]"]:checked').forEach((checkbox) => {
                    selectedUsers.push(checkbox.value);
                });

                // Validate selections
                const highlightedCell = document.querySelector('.day.highlight');
    
                if (!highlightedCell) {
                    alert('Please select an available timeslot first.');
                    return;
                }

                if (selectedUsers.length === 0) {
                    alert('Please select at least one participant.');
                    return;
                }

                // Verify the selected cell is actually available (green)
                const bgColor = window.getComputedStyle(highlightedCell).backgroundColor;
                if (bgColor !== 'rgb(0, 128, 0)' && bgColor !== 'green') {
                    alert('Please select an available timeslot (green cell).');
                    return;
                }

                // Get time and day information
                const [day, time] = highlightedCell.getAttribute('data-hidden-value').split('-');
    
                // Format time for 24-hour format
                let formattedTime;
                if (parseInt(time) < 8) {
                    formattedTime = (parseInt(time) + 12) + ":00";
                } else {
                    formattedTime = time + ":00";
                }

                // Store current week and redirect
                sessionStorage.setItem("currentWeek", currentWeek);
                const url = `create.php?week=${currentWeek}&day=${day}&time=${formattedTime}&users=${selectedUsers.join(',')}`;
                window.location.href = url;
            });

            document.getElementById('search').addEventListener('input', function(){
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('.user-row');

                rows.forEach(function(row){
                    let name = row.cells[0].textContent.toLowerCase();

                    if(name.includes(filter)){
                        row.style.display = '';
                    } else{
                        row.style.display = 'none';
                    }
                });
            });

            document.getElementById('applyBtn').addEventListener('click', function() {
                const selectedUsers = [];
                document.querySelectorAll('input[name="check[]"]:checked').forEach((checkbox) => {
                    selectedUsers.push(checkbox.value);
                });

                if (selectedUsers.length === 0) {
                    alert('Please select at least one user.');
                    return;
                }

                // Create the URL for fetching availability data
                const queryString = selectedUsers.map(id => `check[]=${id}`).join('&');
                fetch(`get_availability_checkbox.php?week=${currentWeek}&${queryString}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Availability data:', data.data);
                            updateTimetableCells(data.data);
                        } else {
                            console.error('Error:', data.message);
                            alert('Failed to fetch availability data.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching timetable data:', error);
                        alert('An error occurred while fetching availability data.');
                    });
            });

            const userTableBody = document.getElementById('userTable');

            //Store the initial order of rows
            const initialOrder = Array.from(userTableBody.querySelectorAll('.user-row'));

            function updateSelectedUsersInfo() {
                const selectedUsersInfo = document.querySelector('.selected-users-info');
                const selectedUserCount = document.querySelector('.selected-user-count');
                const selectedUsersList = document.querySelector('.selected-users-list');
                const checkedBoxes = document.querySelectorAll('input[name="check[]"]:checked');
    
                if (checkedBoxes.length > 0) {
                    selectedUsersInfo.classList.add('show');
                    selectedUserCount.textContent = `Selected Participants (${checkedBoxes.length})`;
        
                    selectedUsersList.innerHTML = Array.from(checkedBoxes)
                        .map(checkbox => {
                            const row = checkbox.closest('tr');
                            const name = row.cells[0].textContent;
                            const email = row.cells[1].textContent;
                            return `
                                <div class="selected-user-item">
                                    <span>${name}</span>
                                    <span style="color: #666; font-size: 0.9em;">${email}</span>
                                </div>
                            `;
                        })
                        .join('');
                } else {
                    selectedUsersInfo.classList.remove('show');
                }
            }

            //Function to handle checkbox changes
            function handleCheckboxChange() {
                const checkedRows = Array.from(userTableBody.querySelectorAll('.user-row'))
                    .filter(row => row.querySelector('input[type="checkbox"]').checked);
    
                // Clear and re-add rows in the table
                userTableBody.innerHTML = '';
                checkedRows.forEach(row => {
                    row.classList.add('selected');
                    userTableBody.appendChild(row);
                });
    
                initialOrder.forEach(row => {
                    if (!row.querySelector('input[type="checkbox"]').checked) {
                        row.classList.remove('selected');
                        userTableBody.appendChild(row);
                    }
                });

                // Update selected users info
                updateSelectedUsersInfo();
            }

            //Attach the checkbox change event listener to each checkbox
            document.querySelectorAll('input[name="check[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', handleCheckboxChange);
            });

            function updateTimetable(selectedUsers) {
                fetch(`get_availability_checkbox.php?week=${currentWeek}&check[]=${selectedUsers.join('&check[]=')}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateTimetableCells(data.data); 
                        } else {
                            console.error('Error:', data.message);
                        }
                    })
                    .catch(error => console.error('Error fetching timetable data:', error));
            }

            function updateTimetableCells(data) {
                // Reset all cells to red by default
                document.querySelectorAll('.day').forEach(cell => {
                    cell.style.backgroundColor = 'red';
                    cell.style.cursor = 'not-allowed';
                    cell.classList.remove('highlight');
                });

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

                // Get count of selected users
                const selectedUserCount = document.querySelectorAll('input[name="check[]"]:checked').length;

                // Update cell colors based on the grouped availability
                for (const cellNo in cellAvailabilityMap) {
                    const cell = document.getElementById(cellNo);
                    if (cell) {
                        const availabilityList = cellAvailabilityMap[cellNo];
            
                        // Check if we have data for all selected users
                        const allUsersHaveData = availabilityList.length === selectedUserCount;
            
                        if (allUsersHaveData) {
                            // Check if all users have 'free' status
                            const allFree = availabilityList.every(user => user.availability === 'free');

                            if (allFree) {
                                cell.style.backgroundColor = 'green';
                                cell.style.cursor = 'pointer';
                            } else {
                                cell.style.backgroundColor = 'red';
                            }

                            // Add title attribute to show user availability on hover
                            const tooltipText = availabilityList.map(user => 
                                `${user.userName}: ${user.availability}`
                            ).join('\n');
                            cell.title = tooltipText;
                        }
                    }
                }
            }

            // Check if timeslots is a NodeList
            const timeslots = document.querySelectorAll('.day');
            timeslots.forEach(function(div) {
                div.addEventListener('click', function() {
                    const bgColor = window.getComputedStyle(div).backgroundColor;
        
                    if (bgColor === 'rgb(0, 128, 0)' || bgColor === 'green') {
                        // Remove any existing highlights
                        document.querySelectorAll('.day.highlight').forEach(cell => {
                            cell.classList.remove('highlight');
                        });
                        // Add highlight to clicked cell
                        div.classList.add('highlight');
                    } else {
                        alert('This timeslot is not available for all selected participants.');
                    }
                });
            });

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
                updateTimetable(); // Load timetable for the current week
            }

            // Add this style to your existing CSS
            const newStyle = document.createElement('style');
            newStyle.textContent = `
                .selected-users-info {
                    background-color: #f0f8ff;
                    padding: 10px;
                    margin: 10px 0;
                    border-radius: 5px;
                    border: 1px solid #ccc;
                    display: none;
                }

                .selected-users-info.show {
                    display: block;
                }

                .selected-user-count {
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 5px;
                }

                .selected-users-list {
                    max-height: 100px;
                    overflow-y: auto;
                    padding: 5px;
                }

                .selected-user-item {
                    background: #e9ecef;
                    margin: 2px 0;
                    padding: 5px;
                    border-radius: 3px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .user-row.selected {
                    background-color: #e3f2fd;
                }
            `;
            document.head.appendChild(newStyle);

            // Add this HTML right after your table
            const selectedUsersDiv = document.createElement('div');
            selectedUsersDiv.className = 'selected-users-info';
            selectedUsersDiv.innerHTML = `
                <div class="selected-user-count"></div>
                <div class="selected-users-list"></div>
            `;
            document.querySelector('.userList_container').insertBefore(selectedUsersDiv, document.querySelector('table'));

            document.querySelectorAll('input[name="check[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    handleCheckboxChange();
        
                    // Get selected users and update timetable if any users are selected
                    const selectedUsers = Array.from(document.querySelectorAll('input[name="check[]"]:checked'))
                        .map(cb => cb.value);
            
                    if (selectedUsers.length > 0) {
                        updateTimetable(selectedUsers);
                    } else {
                        // Reset timetable if no users are selected
                        document.querySelectorAll('.day').forEach(cell => {
                            cell.style.backgroundColor = 'red';
                            cell.style.cursor = 'not-allowed';
                            cell.classList.remove('highlight');
                            cell.title = 'No users selected';
                        });
                    }
                });
            });

            // Update search function to maintain selection visibility
            document.getElementById('search').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('.user-row');

                rows.forEach(function(row) {
                    let name = row.cells[0].textContent.toLowerCase();
                    if (name.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Initial call to set up the selected users info
            updateSelectedUsersInfo();
        </script>
    </body>
</html>