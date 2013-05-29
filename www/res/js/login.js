function login() {
    // Indicate that it is loading
    $("#submit").html("Loading...");
    
    // Get the username and password from the text fields
    var credentials = {
        "username" : $("#username").val(),
        "password" : $("#password").val()
    };
    
    // Send login request
    $.post("/api/login", credentials, function(response) {
        if (response.success) {
            // Redirect to the page
            window.location.replace(login_redirect);
        } else {
            // If the user is already logged in, procceed
            if (response.e_code == "100-already_logged_in") {
                window.location.replace(login_redirect);
            }
            
            // Clear password field
            $("#password").val("");
            
            // Output invalid password
            $("#submit").html(response.e_message); 
            
            // Reset the button in 1.5 seconds
            window.setTimeout(function() {
                $("#submit").html("Login");
            }, 1500);
        }
    });
}

$(document).ready(function(){
    $("#username, #password").keyup(function(event) {
        if (event.keyCode === 13) { 
            login();
        }
    });
});