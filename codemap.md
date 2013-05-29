Badge System - Code Map
=========
Introduction
--------------
The badge system uses an API, the API is not meant to be external.

The API is used internally, the served pages are 100% HTML/JavaScript/CSS (With a few exceptions)

I chose this method in order to soft-code the entire front-end, making the project more customizable

One of the fallbacks to this method is the lack of noscript support. That will be added later.



The Bulk of the code happens in ```./www/api/*```.
These files serve all of the API requests

File System
--------------
```
./www/
    Stores all of the front-end HTML and PHP files.
    This is the DocumentRoot.

./www/api/
    Contains all of the files for the API functions
    Basically the entire back-end

./include/
    Contains all of the back-end PHP files, safely out of DocumentRoot

./include/class/
    Contains all of the back-end classes

./include/config/
    Contains all of the configuration files
```

API Requests
--------------
```
             URL                      Permissions     Description

http://badge_sys/api/status             Anyone      Returns login status     [DONE]  <--- Needs testing
http://badge_sys/api/login              Anyone      Login function           [DONE]  <--- Tested
http://badge_sys/api/logout             User        Logout function          [DONE]  <--- Tested

http://badge_sys/api/badge/list-all     Anyone      Lists all the badges     [DONE]  <--- Tested
http://badge_sys/api/badge/add          Admin       Adds a badge             [DONE]  <--- Needs more testing
http://badge_sys/api/badge/edit         Admin       Edits a badge            [TODO]
http://badge_sys/api/badge/remove       Admin       Removes a badge          [DONE]  <--- Needs testing

http://badge_sys/api/user/setting       User        Change User settings     [TODO]
http://badge_sys/api/user/progress      User        Edit user progression    [DONE]  <--- Needs testing
http://badge_sys/api/user/progression   User        Shows user's progression [DONE]  <--- Needs testing

http://badge_sys/api/account/list-all   Admin       Lists all users          [DONE]  <--- Tested
http://badge_sys/api/account/add        Admin       Creates a user           [DONE]  <--- Tested
http://badge_sys/api/account/edit       Admin       Edits a user             [TODO]
http://badge_sys/api/account/remove     Admin       Removes a user           [DONE]  <--- Tested

http://badge_sys/api/admin/setting      Admin       Changes settings         [TODO]
```

Login request
```
Input
    GET  http://badge_sys/api/login
    POST
         username : {username}
         password : {password}
         
Output
    JSON
        success : {whether or not successful}
      (IF it was unsuccessful {
        e_message : {Error message}
        e_code    : {Error code}
      })
```

Logout request
```
Input
    GET http://badge_sys/api/logout

Output
    JSON
        success : {whether or not successful}
      (IF it was unsuccessful {
        e_message : {Error message}
        e_code    : {Error code}
      })
```

``` TODO Finish Docs```


Classes (```./include/class/```)
--------------
Connection (from ```connection.php```)
```
    SQL Connection class.
    
    $conn = new Connection();
     returns mysqli class connected to the SQL server
```
Password (from ```password.php```) [Static class]
```
    Password Utilities class.
    
    Password::hash({plaintext});
     returns 60 character Blowfish hash of {plaintext} with 22 character salt
    
    Password::compare({plaintext}, {hash});
     returns true if {plaintext} matches {hash}, false otherwise.
```
Session (from ```session.php```) [Static class]
```
    Session management class.
    
    Session::grab();
     used to get the current session. usually at the top of a file
    
    Session::is_logged_in();
     returns true if the session has a user logged in, false otherwise.
    
    Session::is_admin();
     returns true if the current user is admin, false otherwise.
    
    Session::fetch_user();
     returns a User object representing the current user logged in.
```
User (from ```user.php```)
```
    User object
    
    new User(<username>);
     fetches the details of <username> from SQL Database, and stores them in the object.
```
API (from ```api.php```)
```
    API management class.
    
    API::output(<output>)
     sends <output> in JSON format then exits the script.
     
    API::invalid(<value>)
     adds <value> to $invalid array
     
    API::invalid_checkout()
     outputs an error and exist the script if there is anything in the $invalid array
    
    API::invalid_reset()
     resets the $invalid array
     
    API::error(<error>)
     outputs <error>
```