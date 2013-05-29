<?php
    /* Admin API
     * 
     *  Serves Admin API requests
     *  
     *  TODO topdocs
     */
    
    // Dependencies
    require_once(dirname(__FILE__) . "/../../include/class/connection.php");
    require_once(dirname(__FILE__) . "/../../include/class/session.php");
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
        case "setting":
            // TODO
            break;
        
        default:
            // Invalid request
            API::error("invalid_request");
            
            break;
    }
?>