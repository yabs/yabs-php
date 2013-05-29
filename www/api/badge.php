<?php
    /* Badge API
     * 
     *  Serves Badge API requests
     *
     *  http://badge_sys/api/badge/list-all
     *   returns details of all the badges  [Public]
     *  
     *  http://badge_sys/api/badge/add
     *   adds a badge to the database       [Admin-only]
     *  
     *  http://badge_sys/api/badge/edit
     *   edits a badge in the database      [Admin-only]
     *  
     *  http://badge_sys/api/badge/remove
     *   removes a badge from the database  [Admin-only]
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
        case "list-all": // API request to list all of the badges
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare SQL Query to get badges
            $stmt = "SELECT Badge.id, Badge.name,"
                   ." Level.level, Level.desc, Level.criteria, Level.self,"
                   ." LIcon.path, UIcon.path"
                   ." FROM Badge"
                   
                   ." JOIN BadgeLevel AS Level ON Level.badge_id = Badge.id"
                   ." LEFT JOIN Icon AS LIcon ON LIcon.id = Level.locked"
                   ." LEFT JOIN Icon AS UIcon ON UIcon.id = Level.unlocked"
                   
                   ." WHERE Badge.alive = TRUE"
                   ." ORDER BY Badge.id ASC";
            $query = $conn->prepare($stmt);
            
            // Execute Query
            $query->execute();
            $query->bind_result($id, $name, $level, $desc, $crit, $self,
                                $l_icon, $u_icon);
            
            // Create array to store badges
            $badges = array();
            $level_template = array("desc", "crit", "self", "l_icon", "u_icon");
            $current_id = -1;
            
            // Loop through results
            while ($query->fetch()) {
                // If there is no icon, put a placeholder
                if ($u_icon === null) {
                    $u_icon = "/res/img/placeholder-unlocked.png";
                }
                
                if ($l_icon === null) {
                    $l_icon = "/res/img/placeholder-locked.png";
                }
                
                // Check if we're the same badge as last itteration
                if ($current_id != $id) {
                    // If we're not, add this one to the badges array
                    array_push($badges, array(
                        "id"     => $id,
                        "name"   => $name,
                        "levels" => array()
                    ));
                    
                    // Get the key, and cache the id
                    $current_id = $id;
                    end($badges);
                    $key = key($badges);
                }
                
                // Add each level
                array_push($badges[$key]["levels"], compact($level_template));
            }
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            API::output($badges);
            
            break;
        
        case "edit": // API request to edit a badge (Admin-only)
            if (!Session::is_admin()) {
                API::error("invalid_permissions");
            }
            
            // Get POST values
            $id     = @$_POST["id"] or API::invalid("id");
            $name   = @$_POST["name"];
            $levels = @$_POST["levels"];
            
            // TODO validate badge ID
            
            // If the error message is populated, display it and exit script
            API::invalid_checkout();
            
            // If the name was submitted, update the SQL Server
            if (isset($name)) {
                // Validate the name
                if (!Validate::value($name, "badge", "name")) {
                    // If it is invalid, add it to the error message
                    API::invalid("name");
                }
                
                // Open Connection
                $conn = new Connection();
                
                // Prepare SQL Query
                $stmt = "UPDATE Badge"
                       ." SET `name` = ?"
                       ." WHERE `id` = ?";
                $query = $conn->prepare($stmt);
                
                // Execute the query after binding values
                $query->bind_param("si", $name, $id);
                $query->execute();
                
                // Close query
                $query->close();
            }
            
            // Check if level changes were submitted
            if (isset($levels)) {
                // Loop through each level
                foreach ($levels as $level) {
                    // Start the SQL Statement
                    $stmt = "UPDATE BadgeLevel";
                    
                    $lvl  = $level["level"];
                    $desc = $level["desc"];
                    $crit = $level["crit"];
                    
                    // TODO Validate values
                    // TODO Edit Self-Approvable
                    
                    switch ((int) isset($desc) + (int) isset($crit)) {
                        case 2: // Both Description and Criteria are set
                            // Prepare Query
                            $stmt .= " SET `desc` = ?, `criteria` = ?"
                                    ." WHERE `level` = ?"
                                    ." AND `badge_id` = ?";
                            $query = $conn->prepare($stmt) or die($conn->error);
                            
                            // Execute query after binding values
                            $query->bind_param("ssii", $desc, $crit, $lvl, $id);
                            $query->execute();
                            
                            // Close query
                            $query->close();
                            
                            break;
                        
                        case 1: // Only one is set
                            // Figure out which is set
                            $type  = (isset($desc) ? "desc" : "criteria");
                            $value = (isset($desc) ? $desc : $crit);
                            
                            // Prepare Query
                            $stmt .= " SET `".$type."` = ?"
                                    ." WHERE `level` = ?"
                                    ." AND `badge_id` = ?";
                            $query = $conn->prepare($stmt);
                            
                            // Execute query after binding values
                            $query->bind_param("sii", $value, $lvl, $id);
                            $query->execute();
                            
                            // Close query
                            $query->close();
                            
                            break;
                    }
                }
            }
            
            API::output(array(
                "success" => "unknown"
            ));
            
            break;
        
        case "add": // API request to add a badge (Admin-only)
            if (!Session::is_admin()) {
                API::error("invalid_permissions");
            }
            
            // Get POST values
            $name  = @$_POST["name"]        or API::invalid("name");
            $descs = @$_POST["description"] or API::invalid("description");
            $crits = @$_POST["criteria"]    or API::invalid("criteria");
            $selfs = @$_POST["self"]        or API::invalid("self");
            
            // Validate the name
            if (!Validate::value($name, "badge", "name")) {
                // If it is invalid, add it to the error message
                API::invalid("name");
            }
            
            // Loop through each level of the badge
            for ($level = 0; $level < 5; $level++) {
                // Validate each level
                if (!Validate::value($descs[$level], "badge", "description")) {
                    API::invalid("description-".$level);
                }
                if (!Validate::value($descs[$level], "badge", "criteria")) {
                    API::invalid("description-".$level);
                }
                if ($selfs[$level] < 0 || $selfs[$level] > 1) {
                    API::invalid("description-".$level);
                }
            }
            
            // If the error message is populated, display it and exit script
            API::invalid_checkout();
            
            // TODO file upload
            
            // Open SQL Connection
            $conn = new Connection();
            
            // TODO Merge SQL Queries
            
            // Prepare query to check for duplicate
            $dupe_stmt = "SELECT 1 FROM Badge"
                        ." WHERE `name` = ?"
                        ." AND `alive` = 1";
            $dupe_query = $conn->prepare($dupe_stmt) 
                or API::error("internal_sql");
            
            // Execute query after binding proposed name
            $dupe_query->bind_param("s", $name);
            $dupe_query->execute();
            
            // Check if a result is returned
            if ($dupe_query->fetch()) {
                API::output(array(
                    "success"   => false,
                    "e_message" => "Duplicate Badge Name",
                    "e_code"    => "100-dupe_badge_name"
                ));
            }
            
            // Prepare query to add badge to SQL Database
            $badge_stmt = "REPLACE INTO `Badge`"
                         ." VALUES (null, ?, 1)";
            $badge_query = $conn->prepare($badge_stmt)
                or API::error("internal_sql");
            
            // Run prepared query after binding badge name
            $badge_query->bind_param("s", $name);
            $badge_query->execute();
            
            $badge_query->close();
            
            // Get the new badge's ID
            $id = $conn->insert_id;
            
            // Prepare query to add each level of the badge to the SQL Database
                // TODO Better alternative for REPLACE
            $level_stmt = "REPLACE INTO `BadgeLevel`"
                         ." VALUES (null, ?, ?, ?, ?, ?)";
            $level_query = $conn->prepare($level_stmt) 
                or API::error("internal_sql");
            
            // Loop through each level
            for ($level = 1; $level < 6; $level++) {
                $desc = $descs[$level-1];
                $crit = $crits[$level-1];
                $self = $selfs[$level-1];
                
                // Add the level to the SQL Database
                $level_query->bind_param("iissi", $id, $level, $desc, $crit, $self);
                $level_query->execute();
            }
            
            // Check if it were successful
            $result = (boolean) $conn->affected_rows;
            
            // Close SQL Connections
            $level_query->close();
            $conn->close();
            
            API::output(array(
                "success" => $result
            ));
            
            break;
        
        case "remove": // API request to remove badge (Admin-only)
            if (!Session::is_admin()) {
                API::error("invalid_permissions");
            }
            
            $badge_id = @$_POST["id"] or API::invalid("id");
            
            // TODO validate Badge ID
            
            API::invalid_checkout();
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare query to remove badge
            $stmt = "UPDATE `Badge`"
                   ." SET `alive` = 0"
                   ." WHERE `id` = ?";
            $query = $conn->prepare($stmt) or API::error("internal_sql");
            
            // Execute query after binding badge id
            $query->bind_param("i", $badge_id);
            $query->execute();
            
            // Check if it were successful
            $result = (boolean) $conn->affected_rows;
            
            // Close SQL Connections
            $query->close();
            $conn->close();
            
            API::output(array(
                "success" => $result,
                "id"      => $badge_id
            ));
            
            break;
        
        default:
            // Invalid request
            API::error("invalid_request");
            
            break;
    }
    
?>