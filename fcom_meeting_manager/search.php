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
                padding:0;  
                width:100%;
                justify-content:center;
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
                transition:background-color 0.3 ease;
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="meeting.php">Meeting</a></li>
                    <li><a href="search.php">Search</a></li>
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
                    <li><a href="search.php">Search</a></li>
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
                            <button type="button" id="applyBtn">Apply</button>
                        </div>
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
                                            <input class="checkBox" type="checkbox" name="check[]" value="<?php echo $row['user_id']; ?>">
                                        </td>
                                    </tr>
                            <?php
                                    }
                                } else{
                                    echo '<tr><td colspan="3" class="no-users-message">No user</td></tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>

        <script>
            let subMenu = document.getElementById("subMenu");
            let currentWeek = 1;

            function updateWeekDisplay() {
                document.getElementById('weekNumber').innerText = `Week ${currentWeek}`;
            }

            function toggleHamburger() {
                const sidebar = document.getElementById('sidebar');
                const isOpen = sidebar.style.left === '0px'; 

                sidebar.style.left = isOpen ? '-250px' : '0px';
            }

            function toggleMenu(){
                subMenu.classList.toggle("open-menu");
            }

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
                let selectedUsers = [];
                document.querySelectorAll('input[name="check[]"]:checked').forEach((checkbox) => {
                    selectedUsers.push(checkbox.value);
                });

                if (selectedUsers.length > 0) {
                    updateTimetable(selectedUsers);
                } else {
                    alert('Please select at least one user.');
                }
            });

            const userTableBody = document.getElementById('userTable');

            //Store the initial order of rows
            const initialOrder = Array.from(userTableBody.querySelectorAll('.user-row'));

            //Function to handle checkbox changes
            function handleCheckboxChange(){
                //Get all checked rows
                const checkedRows = Array.from(userTableBody.querySelectorAll('.user-row'))
                    .filter(row => row.querySelector('input[type="checkbox"]').checked);

                //clear the table and re-add checked rows at the top, followed by unchecked rows in their original order
                userTableBody.innerHTML = '';

                //Append checked rows at the top
                checkedRows.forEach(row => userTableBody.appendChild(row));

                //Append the remaining rows in their initial order
                initialOrder.forEach(row => {
                    if (!row.querySelector('input[type="checkbox"]').checked){
                        userTableBody.appendChild(row);
                    }
                });
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
                });

                // Group availability data by CellNo
                const cellAvailabilityMap = {};
                data.forEach(item => {
                    const cellNo = item.cellNo;
                    if (!cellAvailabilityMap[cellNo]) {
                        cellAvailabilityMap[cellNo] = [];
                    }
                    cellAvailabilityMap[cellNo].push(item.availability);
                });

                // Get a count of the selected users
                const selectedUserCount = new Set(data.map(item => item.userId)).size;

                // Update cell colors based on the grouped availability
                for (const cellNo in cellAvailabilityMap) {
                    const cell = document.getElementById(cellNo);
                    if (cell) {
                        const availabilityList = cellAvailabilityMap[cellNo];
            
                        // Check if we have data for all selected users for this cell
                        const allUsersHaveData = availabilityList.length === selectedUserCount;
                        const allFree = availabilityList.every(status => status === 'free');
            
                        // Set cell to green only if all selected users have data and all are "free"
                        if (allUsersHaveData && allFree) {
                            cell.style.backgroundColor = 'green';
                        }
                    }
                }
            }

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
        </script>
    </body>
</html>