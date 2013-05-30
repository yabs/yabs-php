<?php
    /* Password class
     * 
     *  Used for password hashing and comparing functions
     *  
     *  `Password::hash(<plaintext>)`
     *   returns 60 character salted blowfish hash of <plaintext>
     *  
     *  `Password::compare(<hash>, <plaintext>)`
     *   returns boolean of whether or not the plaintext matches the hash
     */
    
    class Password {
        // Generates a random string, used for salt
        public static function random_string($length) {
            $chars = "0123456789./qwer"
                    ."tyuiopasdfghjklz"
                    ."xcvbnmQWERTYUIOP"
                    ."ASDFGHJKLZXCVBNM";
            $string = "";
            
            // Append a random character to the string
            for ($i = 0; $i < $length; $i++) {
                $string .= $chars[rand(0, strlen($chars) - 1)];
            }
            
            return $string;
        }
        
        // Hash <plaintext> using Blowfish with 22 character salt
        public static function hash($plaintext) {
            return crypt($plaintext, "$2y$13$" . self::random_string(22));
        }
        
        // Compare a hash and a plaintext
        public static function compare($plaintext, $hash) {
            return (crypt($plaintext, $hash) === $hash);
        }
    }
    
?>
