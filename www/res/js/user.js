// Define variables
var i;
var progress;
var nonce;
var badges;

// Fetch all of the badges (Descriptions, Criteria, etc)
$.get("/api/badge/list-all", function(response) { badges = response; });

// Login and Logout functions
var login = function() {
    $("#login-button").attr("onclick", "");
    $("#login-button").html("loading...");
    
    var url = "/api/login";
    var post = {
        username : $("#login-username").val(),
        password : $("#login-password").val()
    };
    
    $("#login-password").val("");
    
    var callback = function(response) {
        if (response.success) {
            // Get the nonce
            nonce = response.nonce;
            // Change the login form
            $("#login-status").val("Logged in as "+response.username);
            $("#login-status").show();
            $("#login-username, #login-password, #signup-button").hide();
            $("#login-button").html("Logout");
            $("#login-button").attr("onclick", "logout()");
            
            // If it's the user's own page, add edit buttons
            if (response.username == location.pathname.split("/")[2]) {
                $(".user-overlay").css({
                    "background-image" : "url('/res/img/overlay.png')",
                    "cursor"           : "pointer"
                });
            }
        } else {
            $("#login-button").html(response.e_message);
            $("#login-button").attr("onclick", "");
            window.setTimeout(function() {
                $("#login-button").attr("onclick", "login()");
                $("#login-button").html("Login");
            }, 1500);
        }
    };
    
    $.post(url, post, callback);
};

var logout = function() {
    $.post("/api/logout", { nonce : nonce }, function(response) {
        if (response.success) {
            nonce = false;
            $("#login-status").hide();
            $("#login-button").html("Logged out.");
            $("#login-button").attr("onclick", "");
            window.setTimeout(function() {
                $("#login-username, #login-password, #signup-button").show();
                $("#login-button").html("Login");
                $("#login-button").attr("onclick", "login()");
            }, 800);
            
            $(".user-overlay").css({
                "background-image" : "",
                "cursor"           : "default"
            });
        } else {
            $("#login-button").html(response.e_message);
            $("#login-button").attr("onclick", "");
        window.setTimeout(function() {
                $("#login-button").attr("onclick", "logout()");
                $("#login-button").html("Logout");
            }, 1500);
        }
    });
};

$(document).ready(function() {
    $(".overlay").click(function() {
        
    });
    
    // Display the Login form
    $.get("/api/status", function(response) {
        nonce = response.nonce;
        if (response.logged_in) {
            $("#login-status").val("Logged in as "+response.username);
            $("#login-username, #login-password, #signup-button").hide();
            $("#login-button").html("Logout");
            $("#login-button").attr("onclick", "logout()");
            
            // Show the user overlay if we're logged in
            if (response.username == location.pathname.split("/")[2]) {
                $(".user-overlay").css({
                    "background-image" : "url('/res/img/overlay.png')",
                    "cursor"           : "pointer"
                });
            }
        } else {
            $("#login-status").hide();
            $("#login-button").attr("onclick", "login()");
        }
    });
    
    // Enterkey submit shortcut
    $("#login-username, #login-password").keyup(function(event) {
        if (event.keyCode === 13) { $("#login-button").click(); }
    });

    // Get user's progression
    $.get("/api/user/"+location.pathname.split("/")[2],
        function(response) {
            progress = response;
            $(".badge-table .icon").removeClass("unlocked").addClass("locked");
            for (i in response) {
                var details = response[i];
                $("#badge-"+details.badge+" .icon").eq(details.level-1)
                    .removeClass("locked").addClass("unlocked");
            }
        }
    );
    
    // Set hover triggers
    $(".badge-table .icons td").hover(                
        function() {
            var badge_id = $(this).parent().parent().parent().attr("badge-id");
            var selector = "#badge-"+badge_id;
            
            // Remove any intial CSS / Table Structure
            $(selector+" .desc-content").attr("rowspan", "1");
            $(selector+" .desc-content").css("font-weight", "normal");
            
            // Clear the other icons
            for (i in $(selector+" .icons td")) {
                $(selector+" .icons td").eq(i).css({
                    "background-color" : "inherit",
                    "box-shadow" : "none"
                });
                
                $(selector+" .info-container").round(8);
            }
    
            // Make the background visible
            $(this).css({
                "box-shadow" : "0 1px 16px #222",
                "background-color" :"#2c2c2c"
            });
            
            var level = $(selector+" .icons td").index(this);
            // Change the rounded elements below to straight
            if (level === 0) {
                $(selector+" .info-container").round([0, 8, 8, 8]);
            }
            if (level === 4) {
                $(selector+" .info-container").round([8, 0, 8, 8]);
            }
            
            var levels;
            for (var badge in badges) {
                if (badges[badge].id == badge_id) {
                    levels = badges[badge].levels;
                }
            }
            
            // Change the content of the Badge Descript and Criteria
            $(selector+" .desc-content").html(levels[level].desc);
            $(selector+" .crit-content").html(levels[level].crit);
            $(selector+" .header-level").html("Level "+(level+1));
            
            // Check if the badge is unlocked
            if ($(this).find(".icon").hasClass("unlocked")) {
                for (i in progress) {
                    if (progress[i].badge == badge_id 
                    &&  progress[i].level == level+1) {
                        $(selector+" .link-content").attr("rowspan", "1");
                        $(selector+" .link-content").html(progress[i].link);
                        $(selector+" .comm-content").html(progress[i].comment);
                        
                        break;
                    }
                }
            } else {
                $(selector+" .link-content").attr("rowspan", "2");
                $(selector+" .link-content").html("Not yet earned");
                $(selector+" .comm-content").html("");
            }
        }, function() { }
    );
});