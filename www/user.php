<?php
    /* User page
     * 
     *  Shows the progress for ?user
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../include/class/validate.php");
    
    // Check if the username is valid
    if (!Validate::value(@$_GET["user"], "username")) {
        // TODO Error page
        die("Invalid User");
    }
    
    // Open SQL Connection
    $conn = new Connection();
    
    // Prepare SQL Query to check if the user exists
    $stmt = "SELECT `username`"
           ." FROM User"
           ." WHERE `username` LIKE ?"
           ." AND `alive` = 1";
    $query = $conn->prepare($stmt);
    
    // Execute Query after binding username
    $query->bind_param("s", @$_GET["user"]);
    $query->execute();
    $query->bind_result($username);
    
    if (!$query->fetch()) {
        // TODO Error page
        $query->close();
        $conn->close();
        
        die("Invalid User");
    }
    
    $query->close();
    
    // Prepare SQL Query to get badges
    $stmt = "SELECT Badge.id, Badge.name"
           ." FROM Badge"
           ." WHERE `alive` = 1"
           ." ORDER BY Badge.id ASC";
    $query = $conn->prepare($stmt);
    
    // Execute Query
    $query->execute();
    $query->bind_result($id, $name);
    
    // Create array to store badges
    $badges   = array();
    $template = array("id", "name");
    
    // Loop through results
    while ($query->fetch()) {
        array_push($badges, compact($template));
    }
    
    // Close SQL Connections
    $query->close();
    $conn->close();
    
?>

<html>
 <head>
     <script type="text/javascript" language="javascript">
        // Preload images
        (new Image()).src = "/res/img/placeholder-locked.png";
        (new Image()).src = "/res/img/placeholder-unlocked.png";
        (new Image()).src = "/res/img/placeholder-profile.png";
        (new Image()).src = "/res/img/overlay.png";
    </script>
    <link type="text/css"  rel="stylesheet" href="/res/css/user.css" />
    
    <!-- // TODO Combine JavaScript resources with a script -->
    <script type="text/javascript" language="javascript" src="/res/js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="/res/js/main.js"></script>
    <script type="text/javascript" language="javascript" src="/res/js/user.js"></script>

 </head>

 <body>
    <div id="left-container">
        <a id="header" href="/">Badge System</a>
        <br />
        <hr />
        <div id="user-container">
            <span id="title"><?php echo ucfirst($username); ?></span>
            <i id="user-picture"><i class="user-overlay"></i></i>
        </div>
        <hr />
        <div id="login-container">
            <input id="login-username" type="text" placeholder="username" />
            <input id="login-password" type="password" placeholder="password" />
            <input id="login-status" type="text" value="Loading.." readonly />
            <button id="login-button">Login</button>
            <button id="signup-button">Sign up</button>
        </div>
    </div>
<?php
    foreach ($badges as $badge) {
?>
    <table badge-id=<?php echo "\"".$badge["id"]."\""; ?> id=<?php echo "\"badge-".$badge["id"]."\""; ?> class="badge-table">
        <tr class="icons">
            <td><i class="icon locked"><i class="overlay"></i></i></td>
            <td><i class="icon locked"><i class="overlay"></i></i></td>
            <td><i class="icon locked"><i class="overlay"></i></i></td>
            <td><i class="icon locked"><i class="overlay"></i></i></td>
            <td><i class="icon locked"><i class="overlay"></i></i></td>
        </tr>
        <tr>
            <td class="info-container" colspan="5">
                <table class="info-table">
                    <tr>
                        <th colspan="2" class="badge-header"><?php echo $badge["name"]; ?><span class="header-level"></span></th>
                    </tr>
                    <tr>
                        <td class="label desc-label">Description</td>
                        <td class="content desc-content" rowspan="4">Hover over an icon</td>
                    </tr>
                    <tr>
                        <td class="label crit-label">Criteria</td>
                        <td class="content crit-content"></td>
                    </tr>
                    <tr>
                        <td class="label link-label">Evidence</td>
                        <td class="content link-content"></td>
                    </tr>
                    <tr>
                        <td class="label comm-label">Comment</td>
                        <td class="content comm-content"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php
    }
?>
    <br />
 </body>
</html>