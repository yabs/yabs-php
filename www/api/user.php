<?php
    /* User API
     * 
     *  Serves User API requests
     *  
     *  http://badge_sys/api/user/setting
     *   changes a user setting             [Users-only]
     *  
     *  http://badge_sys/api/user/progress
     *   edits progress of a badge          [Users-only]
     *  
     *  http://badge_sys/api/user/progression
     *   shows all of the user's progress   [Users-only]
     *
     *  http://badge_sys/api/user/<user>/progression
     *   shows all of <user>'s progress     [Public]
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../../include/class/validate.php");
    require_once(dirname(__FILE__) . "/../../include/class/session.php");
    require_once(dirname(__FILE__) . "/../../include/class/api.php");
    
    // Get session
    Session::grab();
    
    // Fetch API request
    $action = @$_GET["action"];
    
    switch ($action) {
        case "progress":
            // Verify they are logged in
            if (!Session::is_logged_in()) {
                API::error("not_logged_in");
            }
            
            // Get POST values
            $badge   = @$_POST["badge_id"] or API::invalid("badge_id");
            $level   = @$_POST["level"]    or API::invalid("level");
            $link    = @$_POST["link"]     or API::invalid("link");
            $comment = @$_POST["comment"]  or API::invalid("comment");
            
            // TODO validate POST values
            
            API::invalid_checkout();
            
            // TODO check for duplicate entries
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare query to insert values into Database
            $stmt = "INSERT INTO `Progress`"
                   ." VALUES (null, getUserId(?), ?, ?, ?, ?, 1)";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute query after binding values
            $query->bind_param("siiss", Session::username(), $badge, 
                               $level, $link, $comment);
            $query->execute();
            
            // Check if it were successful
            $result = (boolean) $conn->affected_rows;
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            API::output(array(
                "success" => $result
            ));
            
            break;
        
        case "progression":
            // Get the user to display
            $username = @$_GET["user"] or $username = Session::username();
            
            // Check if it is set
            if (!$username) {
                API::error("not_logged_in");
            }
            
            // Validate the username
            if (!Validate::value($username, "username")) {
                // If it is invalid add it to the error message
                API::invalid("username");
            }
            
            // If the error message is populated display it and exit script
            API::invalid_checkout();
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare SQL statement to fetch progress
            $stmt = "SELECT `badge_id`, `badge_level`, `link`, `comment`"
                   ." FROM `Progress`"
                   ." WHERE `user_id` = getUserId(?)"
                   ." AND `alive` = 1";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute SQL query after binding username
            $query->bind_param("s", $username);
            $query->execute();
            $query->bind_result($badge, $level, $link, $comment);
            
            $progress = array();
            $template = array("badge", "level", "link", "comment");
            
            // Store all the results in an array
            while ($query->fetch()) {
                array_push($progress, compact($template));
            }
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            API::output($progress);
            
            break;
        
        default:
            // Invalid request
            API::error("invalid_request");
            
            break;
    }
    
?>