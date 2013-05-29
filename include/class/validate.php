<?php
    /* Validation class
     * 
     *  Used for validating user inputted values, ie POST values
     *  
     *  `Validate::value(<input>, <value type> [, <value subtype>])`
     *   <input> is the user input to validate
     *   <value type> is the type of input to validate
     *   <value subtype> is the subtype of input
     *  
     *  `Validate::value(@$_POST["user"], "username")`
     *   Will return true if @$_POST["user"] is a valid username
     */
    
    class Validate {
        public static $regexp = array(
            "username"  => "/^[a-z0-9_]{3,24}$/i",
            "email"     => "/^[a-z0-9_\.-]+@[a-z0-9]+\.[a-z0-9]{2,5}$/i",
            "name"      => "/^[a-z]{1,24}$/i",
            // TODO improve Password Regexp
            "password"  => "/^.{6,32}$/",
            // TODO improve badge Regexps
            "badge"     => array(
                "name"          => "/^\w{4,64}$/",
                "description"   => "/^.{4,1024}$/",
                "criteria"      => "/^.{4,1024}$/"
            )
        );
        
        public static function value($input, $type, $subtype = "") {
            // TODO use $arguments instead of $type and $subtype
            if ($subtype === "") {
                return (preg_match(self::$regexp[$type], $input));
            } else {
                return (preg_match(self::$regexp[$type][$subtype], $input));
            }
        }
    }
    
?>