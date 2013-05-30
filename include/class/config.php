<?php
    /* Configuration class
     * 
     *  Used for getting the configuration from the SQL database
     *  
     *  `Config::setting(<key>)`
     *   returns mysqli connection
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../class/connection.php");
    
    class Config {
        // Variable for caching config
        private static $config;
    		
        // Function to get individual setting
    	public static function setting($setting) {
            // Check if we have a cache of the config
    		if (!self::$config) {
                // If we don't, get a fresh version
                self::fetch_all();
            }
            
            return self::$config[$setting];
        }
        
        // Function to fetch config from SQL and cache it
        public static function fetch_all() {
    	    // Check if we have a cache
            if (!self::$config) {
                // Open SQL Connnection
                $conn = new Connection();
                
                // Prepare SQL query for getting settings
                $stmt = "SELECT `setting`, `value`"
                       ." FROM `Config`";
                $query = $conn->prepare($stmt); // TODO `or [...]`
				
                // Execute the query
                $query->execute();
                $query->bind_result($setting, $value);
                
                // Loop through result
                while ($query->fetch()) {
                    self::$config[$setting] = $value;
                }
				
                // Close SQL Connections
                $query->close();
                $conn->close();
			}
            
            return self::$config;
        }
    }
    
    
?>
