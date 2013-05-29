<?php
    /* User class
     * 
     *  Used for getting user data
     *  
     *  `new User(<username>)`
     *   returns a class with all of the user's details
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../class/connection.php");
    
    class User {
        public $details;
        public $progress;
        
        // Constructor
        public function __construct($username) {
            // Open SQL Connection
            $conn = new Connection();
            
            // Prepare query to fetch user details
            $stmt = "SELECT `id`, `email`, `first`, `last`, `role`, `privacy`"
                   ." FROM `User`"
                   ." WHERE `username` = ?"
                   ." AND `alive` = 1 "
                   ." LIMIT 1";
            $query = $conn->prepare($stmt); // TODO `or [...]`
            
            // Run prepared query after binding the username
            $query->bind_param("s", $username);
            $query->execute();
            $query->bind_result($id, $email, $first, $last, $role, $privacy);
            $query->fetch();
            $query->close();
            
            $conn->close();
            
            // Store data in object
            $template = array("id", "email", "first", "last", "role", "privacy");
            $this->details = compact($template);
        }
    }
    
?>