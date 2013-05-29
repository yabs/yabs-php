<?php
    /* SQL Connection class
     * 
     *  Used for opening SQL connections
     *  
     *  `$conn = new Connection()`
     *   returns mysqli connection
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../config/sql.php");
    
    class Connection extends mysqli {
        public function __construct() {
			parent::__construct(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}
    }
    
?>
