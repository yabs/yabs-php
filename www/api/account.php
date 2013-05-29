<?php
    /* Account API
     * 
     *  Serves Account API Requests
     *  
     *  http://badge_sys/api/account/list-all
     *   returns details of all the accounts    [Admin-only]
     *  
     *  http://badge_sys/api/account/add
     *   adds an account                        [Admin-only]
     *  
     *  http://badge_sys/api/account/edit
     *   edits an account                       [Admin-only]
     *  
     *  http://badge_sys/api/account/remove
     *   removes an account                     [Admin-only]
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../../include/class/password.php");
    require_once(dirname(__FILE__) . "/../../include/class/validate.php");
    require_once(dirname(__FILE__) . "/../../include/class/session.php");
    require_once(dirname(__FILE__) . "/../../include/class/config.php");
    require_once(dirname(__FILE__) . "/../../include/class/api.php");
    
    // Get session
    Session::grab();
    
    // Verify they have admin rights
    if (!Session::is_admin()) {
        API::error("invalid_permissions");
    }
    
    // Fetch API request
    $action = @$_GET["action"];
    
    switch ($action) {
        case "list-all":
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare SQL statment to fetch all of the users
            $stmt = "SELECT `username`, `email`, `first`,"
                   ." `last`, `role`, `privacy`"
                   ." FROM `User`"
                   ." WHERE `alive` = 1";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Fetch all of the users from the SQL database
            $query->execute();
            $query->bind_result($username, $email, $first, 
                                $last, $role, $privacy);
            
            $template = array("username", "email", "first",
                              "last", "role", "privacy");
            $accounts = array();
            
            // Loop through all of the accounts
            while ($query->fetch()) {
                array_push($accounts, compact($template));
            }
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            API::output($accounts);
            
            break;
        
        case "add":
            // Get POST values
            $post_input = array(
                // POST name  => input type
                "username"    => "username",
                "password"    => "password",
                "email"       => "email",
                "first_name"  => "name",
                "last_name"   => "name"
            );
            
            foreach ($post_input as $value => $type) {
                // Get the input from the POST
                ${$value} = @$_POST[$value] or API::invalid($value);
                
                // Validate the input
                if (!Validate::value(${$value}, $type)) {
                    // If it's invalid add it to the error message
                    API::invalid($value);
                }
            }
            
            // Get the other POST values
            $role    = @$_POST["role"] or $role = 1;
            $privacy = @$_POST["privacy"]
                or $privacy = Config::setting("default_privacy");
            
            // Check if the privacy setting is above the minimum allowed
            if ($privacy < Config::setting("min_privacy")) {
                // If it isn't, add it to the error message
                API::invalid($privacy);
            }
            
            // If the error message is populated, display it and exit script
            API::invalid_checkout();
            
            // Hash Password, see `./include/class/password.php` for information
            $password = Password::hash($password);
            
            // Open SQL Connection
            $conn = new Connection();
            
            // TODO Merge the two queries for efficiency
            
            // Prepare SQL query to check if user already exists
            $stmt = "SELECT 1 FROM `User`"
                   ." WHERE `username` = ?"
                   ." AND `alive` = 1";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute query after binding username
            $query->bind_param("s", $username);
            $query->execute();
            
            // Check if a result is returned
            if ($query->fetch()) {
                API::output(array(
                    "success"   => false,
                    "e_message" => "User already exists",
                    "e_code"    => "100-user_already_exists"
                ));
            }
            
            $query->close();
            
            // Prepare SQL query to add new user
                // TODO Better alternative for REPLACE
            $stmt = "REPLACE INTO `User`"
                   ." VALUES (null, ?, ?, ?, ?, ?, ?, ?, 1)";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute query after binding User details
            $query->bind_param("sssssii", $username, $password, $email,
                               $first_name, $last_name, $role, $privacy);
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
        
        case "edit":
            // TODO
            break;
        
        case "remove":
            // Get POST values
            $username = @$_POST["username"] or API::invalid("username");
            
            // Validate username
            if (!Validate::value($username, "username")) {
                API::invalid("username");
            }
            
            // Output any errors
            API::invalid_checkout();
            
            // TODO check user actually exists
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare SQL query to delete user
            $stmt = "UPDATE `User`"
                   ." SET `alive` = 0"
                   ." WHERE `username` = ?";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute query after binding username
            $query->bind_param("s", $username);
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
        
        default:
            // Invalid request
            API::error("invalid_request");
            
            break;
    }
    
?>