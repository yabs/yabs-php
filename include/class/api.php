<?php
    /* API class
     * 
     *  Used for API Management
     *  
     *  `API::output(<output>)`
     *   sends <output> in JSON format then exits
     *  
     *  `API::invalid(<value>)`
     *   adds <value> to $invalid array
     *  
     *  `API::invalid_checkout()`
     *   outputs an error if there is anything in the $invalid array
     *  
     *  `API::invalid_reset()`
     *   resets the $invalid array
     */
    
    class API {
        public static $invalid = array();
        
        public static function output($output) {
            // Set content-type HTTP header
            header("Content-Type: application/json");
            
            // Output JSON encoded <output>
            echo json_encode($output);
            
            exit();
        }
        
        public static function invalid($value) {
            // Add <value> to $invalid
            if (!in_array($value, self::$invalid)) {
                array_push(self::$invalid, $value);
            }
        }
        
        public static function invalid_checkout() {
            // If there is anything in the $invalid array, output error and exit
            if (count(self::$invalid) > 0) {
                self::output(array(
                    "success"   => false,
                    "e_message" => "Invalid input",
                    "e_code"    => "100-invalid_post", // TODO Better e_code
                    "e_data"    => self::$invalid
                ));
            }
            
            self::invalid_reset();
        }
        
        // TODO Make this functional
        public static function invalid_reset() {
            // Reset $invalid
            foreach (self::$invalid as &$invalid) {
                unset($invalid);
            }    
        }
        
        public static function error($error) {
            switch($error) {
                case "invalid_request":
                    self::output(array(
                        "success"   => false,
                        "e_message" => "Invalid request",
                        "e_code"    => "404-invalid_request"
                    ));
                    
                    break;
                
                case "invalid_permissions":
                    self::output(array(
                        "success"   => false,
                        "e_message" => "Invalid Permissions",
                        "e_code"    => "403-invalid_permissions"
                    ));
                    
                    break;
                
                case "not_logged_in":
                    self::output(array(
                        "success"   => false,
                        "e_message" => "Not logged in",
                        "e_code"    => "403-not_logged_in"
                    ));
                    
                    break;
                
                case "internal_sql":
                    self::output(array(
                        "success"   => false,
                        "e_message" => "Internal SQL Error",
                        "e_code"    => "500-sql_error"
                    ));
                
                    break;
                
                case "invalid_nonce":
                    self::output(array(
                        "success"   => false,
                        "e_message" => "Invalid Nonce",
                        "e_code"    => "403-invalid_nonce"
                    ));
                    
                    break;
            }
        }
    }
    
?>
