<?php
    /* Tracker class
     * 
     *  Used for tracking events, such as logins
     *  
     *  `Tracker::track(<type>, <data>, <user>);`
     *   inserts the event in the SQL `Event` table
     *     <type> - String  - Event type
     *     <data> - String  - Event data
     *     <user> - Boolean - ( Include the current user id : No user )
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../class/connection.php");
    
    class Tracker {
        public static function track($type, $data, $user_id) {
            // Get User IP
            $IP = $_SERVER["REMOTE_ADDR"];
            
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare an SQL Query to insert event
            $stmt = "INSERT INTO Event"
                   ." VALUES (null, null, ?, ?, ?, ?)";
            $query = $conn->prepare($stmt);
            
            // Execute query after binding values
            $query->bind_param("sssi", $IP, $type, $data, $user_id);
            $query->execute();
            
            // Close SQL Connections
            $query->close();
            $conn->close();
        }
    }
    
?>
