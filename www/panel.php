<?php
    /* Admin Panel
     * 
     *  Control Panel for the badge system
     *  
     *  User must have admin rights to use this page.
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../include/class/session.php");
    
    // Grab the Session
    Session::grab();
    
    // Check if the user is an admin
    if (!Session::is_admin()) {
        // If they are not admin, display a login page for the admin panel
        $login_title    = "Admin login";
        $login_redirect = "/api/cpanel";
        
        require_once(dirname(__FILE__) . "/../include/html/login.php");
        
        exit();
    }
    
    // Open an SQL Connection
    $conn = new Connection();
    
    // Prepare an SQL Query to get all of the events
    $stmt = "SELECT Event.time, event, Event.data, User.username, Event.ip"
           ." FROM Event"
           ." LEFT JOIN User ON User.id = Event.user_id"
           ." ORDER BY Event.time DESC";
    $query = $conn->prepare($stmt); // TODO `or [...]`
    
    // Execute the query
    $query->execute();
    $query->bind_result($time, $event, $data, $username, $IP);
    
    // Loop through the results, store them in $events
    $events = array();
    
    while ($query->fetch()) {
        array_push($events, compact("time", "event", "data", "username", "IP"));
    }
    
    // Close the query
    $query->close();
    
    // Prepare an SQL Query to get all of the badges
    $stmt = "SELECT Badge.id, Badge.name"
           ." FROM Badge"
           ." WHERE Badge.alive = TRUE"
           ." ORDER BY id ASC";
    $query = $conn->prepare($stmt); // TODO `or [...]`
    
    // Execute the query
    $query->execute();
    $query->bind_result($id, $name);
    
    // Loop through the results, store them in $badges
    $badges = array();
    
    while ($query->fetch()) {
        array_push($badges, compact("id", "name"));
    }
    
    // Close the query
    $query->close();
    
    // Prepare an SQL Query to get all of the users
    $stmt = "SELECT id, username, email, first, last, privacy, role"
           ." FROM User"
           ." WHERE User.alive = TRUE"
           ." ORDER BY id ASC";
    $query = $conn->prepare($stmt); // TODO `or [...]`
    
    // Execute the query
    $query->execute();
    $query->bind_result($id, $username, $email, $first, $last, $privacy, $role);
    
    // Loop through the results, store them in $users
    $users = array();
    $user= array("id", "username", "email", "first", "last", "privacy", "role");
    
    while ($query->fetch()) {
        array_push($users, compact($user));
    }
    
    // Close the Connections
    $query->close();
    
    // Prepare an SQL Query to get the configuration
    $stmt = "SELECT `key`, `value`"
           ." FROM Config";
    $query = $conn->prepare($stmt); // TODO `or [...]`
    
    // Execute the query
    $query->execute();
    $query->bind_result($key, $value);
    
    // Loop through the results, stor then in $config
    $config = array();
    while ($query->fetch()) {
        $config[$key] = $value;
    }
    
    // Close the SQL Connections
    $query->close();
    $conn->close();
    
    // Create a function for the Config section
    //   `config(<key>, <value>);` 
    //      if <value> matches <config>[<key>], return " selected"; 
    function config($key, $value) {
        global $config;
        
        return ($config[$key] == $value ? " selected" : "");
    }
?>
<!DOCTYPE html>
<html>
 <head>
    <script type="text/javascript">
        // Preload images
        (new Image()).src = "/res/img/texture.png";
        (new Image()).src = "/res/img/overlay.png";
        (new Image()).src = "/res/img/placeholder-locked.png";
        (new Image()).src = "/res/img/placeholder-unlocked.png";
        (new Image()).src = "/res/img/placeholder-profile.png";
    </script>
    
    <link type="text/css" rel="stylesheet" href="/res/css/panel.css" />
    <!-- // TODO Combine JavaScript resources -->
    <script type="text/javascript" src="/res/js/jquery.js"></script>
    <script type="text/javascript" src="/res/js/panel.js"> </script>
    <script type="text/javascript" src="/res/js/main.js">  </script>
 </head>
 <body>
    <div id="top-container">
        <div id="header-container">
            <a id="header" href="/">Badge System</a>
            <a id="title" href="/api/cpanel">Admin Panel</a>
            
            <button id="logout" class="button input">Logout</button>
        </div>
    </div>
    
    <div id="event-container" class="container">
        <span class="header">Events</span>
        <br />
        <ul id="event-nav" class="nav">
            <li class="selected"><a class="event-tab" event-type="all">ALL</a></li>
            <li><a class="event-tab" event-type="logins">LOGINS</a></li>
            <li><a class="event-tab" event-type="progress">BADGE PROGRESS</a></li>
            <li><a class="event-tab" event-type="user_changes">PROFILE CHANGES</a></li>
            <li><a class="event-tab" event-type="sys_changes">SYSTEM CHANGES</a></li>
        </ul>
        <table id="event-table" class="table">
            <tr>
                <th>TIME</th>
                <th>EVENT</th>
                <th>INFO</th>
                <th>USER</th>
                <th>IP</th>
            </tr>
<?php
    foreach ($events as $event) {
?>
            <tr>
                <td><?php echo $event["time"];     ?></td>
                <td><?php echo $event["event"];    ?></td>
                <td><?php echo $event["data"];     ?></td>
                <td><?php echo $event["username"]; ?></td>
                <td><?php echo $event["IP"];       ?></td>
            </tr>
<?php
    }
?>
        </table>
    </div>
    
    <div id="config-container" class="container">
        <span class="header">Config</span>
        <br />
        <table id="config-table" class="table">
            <tr>
                <th>DEFAULT PRIVACY</th>
                <td><select id="default_privacy" class="input">
                    <option class="input"<?php echo config("default_privacy", 1); ?>>1: Progress and actual name is public</option>
                    <option class="input"<?php echo config("default_privacy", 2); ?>>2: Progress and username is public</option>
                    <option class="input"<?php echo config("default_privacy", 3); ?>>3: Progress can only be seen by Users and Admins</option>
                    <option class="input"<?php echo config("default_privacy", 4); ?>>4: Progress can only be seen by Admins</option>
                </select></td>
            </tr>
            <tr>
                <th>MINIMUM PRIVACY</th>
                <td><select id="min_privacy" class="input">
                    <option class="input"<?php echo config("min_privacy", 1); ?>>1: Progress and actual name is public</option>
                    <option class="input"<?php echo config("min_privacy", 2); ?>>2: Progress and username is public</option>
                    <option class="input"<?php echo config("min_privacy", 3); ?>>3: Progress can only be seen by Users and Admins</option>
                    <option class="input"<?php echo config("min_privacy", 4); ?>>4: Progress can only be seen by Admins</option>
                </select></td>
            </tr>
            <tr>
                <th>REQUIRE E-MAIL</th>
                <td><select id="require_email" class="input">
                    <option class="input"<?php echo config("require_email", 1); ?>>TRUE</option>
                    <option class="input"<?php echo config("require_email", 0); ?>>FALSE</option>
                </select></td>
            </tr>
            <tr>
                <th>ACCOUNT CREATION</th>
                <td><select id="new_restrict" class="input">
                    <option class="input"<?php echo config("new_restrict", 1); ?>>Admins only</option>
                    <option class="input"<?php echo config("new_restrict", 2); ?>>Public, require Admin approval</option>
                    <option class="input"<?php echo config("new_restrict", 3); ?>>Public, instant without approval</option>
                </select></td>
            </tr>
            <tr>
                <th colspan="2"><button class="button input">Save changes</button></th>
            </tr>
        </table>
    </div>
    
    <div id="account-container" class="container">
        <span class="header">Accounts</span>
        <br />
        <table id="account-table" class="table">
            <tr>
                <th>ID</th>
                <th>USERNAME</th>
                <th>E-MAIL</th>
                <th>FIRST NAME</th>
                <th>LAST NAME</th>
                <th>PRIVACY</th>
                <th>ROLE</th>
            </tr>
<?php
    foreach ($users as $user) {
?>
            <tr>
                <td><?php echo $user["id"];       ?></td>
                <td><?php echo $user["username"]; ?></td>
                <td><?php echo $user["email"];    ?></td>
                <td><?php echo $user["first"];    ?></td>
                <td><?php echo $user["last"];     ?></td>
                <td><?php echo $user["privacy"];  ?></td>
                <td><?php echo $user["role"];     ?></td>
            </tr>
<?php
    }
?>
        </table>
    </div>
    
    <div id="badge-container" class="container">
        <span class="header">Badges</span>
        <br />
<?php
    foreach ($badges as $badge) {
?>
        <div class="badge" badge-id=<?php echo "\"".$badge["id"]."\"" ?>>
            <input class="text input badge-name" value=<?php echo "\"".$badge["name"]."\"" ?> type="text" placeholder="Name" />
            <ul class="nav">
                <li class="selected"><a class="badge-level" level="1">Level 1</a></li>
                <li><a class="badge-level" level="2">Level 2</a></li>
                <li><a class="badge-level" level="3">Level 3</a></li>
                <li><a class="badge-level" level="4">Level 4</a></li>
                <li><a class="badge-level" level="5">Level 5</a></li>
            </ul>
            <table class="badge-table table">
                <tr>
                    <td>Description</td>
                    <th rowspan="5">
                        <i class="unlocked icon"></i>
                        <input class="file input" type="file" size="14" />
                        <button class="upload button input">Change icon</button>
                    </th>
                    <th rowspan="5">
                        <i class="locked icon"></i>
                        <input class="file input" type="file" size="14" />
                        <button class="upload button input">Change icon</button>
                    </th>
                </tr>
                <tr>
                    <td><textarea class="desc text input textarea" placeholder="Description"></textarea></td>
                </tr>
                <tr>
                    <td>Criteria</td>
                </tr>
                <tr>
                    <td><textarea class="crit text input textarea" placeholder="Criteria"></textarea></td>
                </tr>
                <tr>
                    <td><input class="self" type="checkbox" /> Self-Approvable</td>
                </tr>
            </table>
        </div>
<?php
    }
?>
    </div>
 </body>
</html>