<?php
    /* Login API
     * 
     *  Serves Login API requests
     *  
     *  http://badge_sys/api/status
     *   outputs the session status     [Public]
     *  
     *  http://badge_sys/api/login
     *   login API function             [Public]
     *  
     *  http://badge_sys/api/logout
     *   logout API function            [Users-only]
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../../include/class/password.php");
    require_once(dirname(__FILE__) . "/../../include/class/validate.php");
    require_once(dirname(__FILE__) . "/../../include/class/session.php");
    require_once(dirname(__FILE__) . "/../../include/class/tracker.php");
    require_once(dirname(__FILE__) . "/../../include/class/api.php");
    
    // Get session
    Session::grab();
    
    // Fetch API request
    $action = @$_GET["action"];
    
    switch ($action) {
        case "status":
            // Output the Session stats
            API::output(array(
                "logged_in" => Session::is_logged_in(),
                "username"  => Session::username(),
                "is_admin"  => Session::is_admin(),
                "nonce"     => Session::fetch_nonce()
            ));
            
            break;
        
        case "login":
            if (Session::is_logged_in()) {
                API::output(array(
                    "success"   => false,
                    "e_message" => "Already logged in",
                    "e_code"    => "100-already_logged_in"
                ));
            }
            
            // Get the credentials
            $username = @$_POST["username"] or API::invalid("username");
            $password = @$_POST["password"] or API::invalid("password");
            
            // Output any errors
            API::invalid_checkout();
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare query to get user's password from SQL Database
            $stmt = "SELECT `id`, `password`, `username`"
                   ." FROM `User`"
                   ." WHERE `username` = ?"
                   ." AND `alive` = 1"
                   ." LIMIT 1";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Run prepared query after binding the username
            $query->bind_param("s", $username);
            $query->execute();
            $query->bind_result($user_id, $password_hash, $username);
            
            // Check if there were results
            if (!$query->fetch()) {
                API::output(array(
                    "success"   => false,
                    "e_message" => "Invalid user",
                    "e_code"    => "403-invalid_user"
                ));
            }
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            // Check if the passwords match
            if (Password::compare($password, $password_hash)) {
                // Set session variables
                $_SESSION["is_logged_in"] = true;
                $_SESSION["username"]     = $username;
                
                Tracker::track("login", "success", $user_id);
                
                API::output(array(
                    "success"  => true,
                    "username" => $username,
                    "is_admin" => Session::is_admin(),
                    "nonce"    => Session::create_nonce()
                ));
            } else {
                Tracker::track("login", "failure", $user_id);
                
                API::output(array(
                    "success"   => false,
                    "e_message" => "Invalid Password",
                    "e_code"    => "403-invalid_pass"
                ));
            }
            
            break;
        
        case "logout":
            // Check if they are logged in
            if (!Session::is_logged_in()) {
                // TODO to send an error, or to not?
                API::output(array(
                    "success" => true
                ));
            }
            
            // Get User ID
            $user_id = Session::user_id();
            
            // Check if they have the correct nonce
            if (!Session::check_nonce(@$_POST["nonce"])) {
                Tracker::track("logout", "failure", $user_id);
                
                API::error("invalid_nonce");
            }
            
            // Unset session variables
            unset($_SESSION["nonce"]);
            unset($_SESSION["is_logged_in"]);
            unset($_SESSION["username"]);
            
            Tracker::track("logout", "success", $user_id);
            
            API::output(array(
                "success" => true
            ));
            
            break;
        
        default:
            // Invalid request
            API::error("invalid_request");
            
            break;
    }
    
?>