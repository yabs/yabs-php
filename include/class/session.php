<?php
    /* Session class
     * 
     *  Used for Session management
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../class/user.php");
    
    class Session {
        // Variable for caching user data
        public static $user;
        public static $nonce;
        
        // Function for getting current session
        public static function grab() {
            // Set session parameters
    		session_set_cookie_params(0, '/api/', '', false, true);
            // Set the cookie name
			session_name("session");
            // Start session
			session_start();
        }
        
        // Function for checking if the session is logged in
        public static function is_logged_in() {
            return (@$_SESSION["is_logged_in"] ? true : false);
        }
        
        // Function for retrieving current user data
        public static function fetch_user() {
            // Check if the session is logged in
            if (!self::is_logged_in()) { return false; }
            
            // Check if we have up-to-date user information
            if (!self::$user
             || @self::$user->details["username"] != $_SESSION["username"]) {
                // If the user info is outdated, fetch fresh data
                self::$user = new User($_SESSION["username"]);
            }
            
            return self::$user;
        }
        
        // Function to get username
        public static function username() {
            // Check if the session is logged in
            if (!self::is_logged_in()) { return false; }
            
            return $_SESSION["username"];
        }
        
        // Function to get user id
        public static function user_id() {
            // Check if the session is logged in
            if (!self::is_logged_in()) { return false; }
            
            return self::fetch_user()->details["id"];
        }
        
        // Function to check if the user has administrative rights
        public static function is_admin() {
            // Check if the session is logged in
            if (self::fetch_user()) {
                // If the role is greater than 3, we're admin
                return (self::$user->details["role"] > 3);
            } else {
                // Return false if the session isn't logged in
                return false;
            }
        }
        
        // Function to check nonce
        public static function check_nonce($nonce) {
            return (@$_SESSION["nonce"] === $nonce);
        }
        
        // Function to generate a nonce
        public static function create_nonce() {
            // Create a string of possible characters for the nonce
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
                    ."abcdefghijklmnopqrstuvwxyz"
                    ."0123456789";
            $nonce = "";
            
            // Add 10 random characters to the nonce
            for ($i = 0; $i < 10; $i++) {
                $nonce .= $chars[rand(0, strlen($chars) - 1)];
            }
            
            $_SESSION["nonce"] = $nonce;
            return $nonce;
        }
        
        // Function to get the current nonce
        public static function fetch_nonce() {
            // If a nonce is set, return the nonce, if not retun false
            if (isset($_SESSION["nonce"])) {
                return $_SESSION["nonce"];
            } else {
                return false;
            }
        }
        
        // Function to destroy the current nonce
        public static function destroy_nonce() {
            // Unset the nonce
            unset($_SESSION["nonce"]);
            
            return;
        }
    }
    
?>
