<?php
    /* Login interface
     *
     *  Displays a login form
     *  After login, users are redirected to given page
     *
     *  $login_title
     *     A string to print at the top of the page
     *     Defaults to "Login"
     *  
     *  $login_redirect
     *     A URL to redirect to on successful login
     *     Defaults to "/"
     */
    
    // Check if $login_title is set
    if (!isset($login_title)) {
        $login_title = "Login";
    }
    
    // Check if $login_redirect is set
    if (!isset($login_redirect)) {
        $login_redirect = "/";
    }
?>
<html>
 <head>
    <script type="text/javascript" language="javascript">
        var login_redirect = '<?php echo $login_redirect; ?>';
    </script>
    <link type="text/css" rel="stylesheet" href="/res/css/login.css" />
    
    <!-- // TODO Combine JavaScript resources with a script -->
    <script type="text/javascript" language="javascript" src="/res/js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="/res/js/login.js"></script>
    <script type="text/javascript" language="javascript" src="/res/js/main.js"></script>
 </head>
 <body>
    <div id="container">
        <div id="header">
            <h1><?php echo $login_title; ?></h1>
        </div>
        <hr id="rule" />
        <input id="username" placeholder="Username" type="text" />
        <br />
        <input id="password" placeholder="Password" type="password" />
        <br />
        <button id="submit" onclick="login()">Login</button>
    </div>
 </body>
</html>