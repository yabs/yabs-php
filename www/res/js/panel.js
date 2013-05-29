// Define variables
var i;
var nonce;
var badges;
var badge_changes = [];
/* badge_changes format
    [
        undefined,
        undefined,
        {
            // Badge id is index in array 
            name : <badge_name>,
            levels : [
                {
                    level : <level changed>,
                    desc  : <desc (if changed)>,
                    crit  : <crit (if changed)>,
                    self  : <self (if changed)>,
                },
                {
                    level : <level changed>,
                    
                    [...]
                }
            ]    
        },
        undefined,
        undefined,
        {
            // Badge id is index in array 
            name : <badge_name>,
            levels : [...]
        },
        [...]
    ]
 */

// Fetch the nonce
$.get("/api/status", function(response) { nonce = response.nonce; });

var event_selectors = [
    {
        "type"     : "all",
        "selector" : "td"
    }, {
        "type"     : "logins",
        "selector" : "td:nth-child(2):contains('login'), td:nth-child(2):contains('logout')"
    }, {
        "type"     : "progress",
        "selector" : "td:nth-child(2):contains('progress')"
    }, {
        "type"     : "user_changes",
        "selector" : "td:nth-child(2):contains('user_change')"
    }, {
        "type"     : "sys_changes",
        "selector" : "td:nth-child(2):contains('sys_change')"
    }
];



$(document).ready(function() {    
    // Logout button
    $("#logout").click(function() {
        // Send the logout request
        $.post("/api/logout", {nonce : nonce}, function(response) {
            if (response.success) {
                // If it was successful, reload the page to show the login
                location.reload();
            } else {
                $("#logout").html(response.e_message);
            }
        });
    });
    
    // Add Tabbed JavaScript listeners
    $(".nav li a").click(function() {
        // Highlight current tab
        $(this).parent().addClass("selected");
        // Remove highlighting on siblings
        $(this).parent().siblings().removeClass("selected");
    });
    
    // Event tab menu actions
    $(".event-tab").click(function() {        
        var selector = "";
        // Loop through the Event Selectors and find the match
        for (i in event_selectors) {
            if (event_selectors[i].type == $(this).attr("event-type")) {
                selector = event_selectors[i].selector;
            }
        }
        
        // Hide all of the table cells
        $("#event-table tr td").hide();
        // Show all of the cells that match the selector
        $("#event-table "+selector).siblings().andSelf().show();
    });
    
    // Get all of the badges
    $.get("/api/badge/list-all", function(response) {
        badges = response;
        
        for (i in badges) {
            var badge = badges[i];
            
            var desc = badge.levels[0].desc;
            var crit = badge.levels[0].crit;
            var self = badge.levels[0].self;
            
            $("[badge-id="+badge.id+"] .desc").val(desc);
            $("[badge-id="+badge.id+"] .crit").val(crit);
            $("[badge-id="+badge.id+"] .self").prop("checked", self);
            
            $("[badge-id="+badge.id+"]").attr("selected-level", 1);
        }
    });
    
    // Badge level menu actions
    $(".badge-level").click(function() {
        var badge_id  = $(this).parents(".badge").attr("badge-id");
        var new_level = $(this).attr("level");
        
        for (i in badges) {
            var badge = badges[i];
            
            if (badge.id == badge_id) {
                var desc = badge.levels[new_level-1].desc;
                var crit = badge.levels[new_level-1].crit;
                var self = badge.levels[new_level-1].self;
                
                $("[badge-id="+badge.id+"] .desc").val(desc);
                $("[badge-id="+badge.id+"] .crit").val(crit);
                $("[badge-id="+badge.id+"] .self").prop("checked", self);
                
                $("[badge-id="+badge.id+"]").attr("selected-level", new_level);
            }
        }
    });
    
    // Badge input onchange events
    $(".desc, .crit, .self").change(function() {
        var badge_id = $(this).parents(".badge").attr("badge-id");
        var name     = $(this).parents(".badge").find(".badge-name").val();
        var level    = $(this).parents(".badge").attr("selected-level");
        var input_type = $(this).attr("class").match(/desc|crit|self/)[0];
        var badge;
        
        // Loop through the badges and get the selected badge
        for (i in badges) {            
            if (badges[i].id == badge_id) {
                badge = badges[i];
            }
        }
        
        // If the input hasn't changed, exit the function
        if (badge[input_type] == $(this).val()) { return; }
        
        // Check if the badge already exists in the `badge_changes` array
        if (badge_changes[badge_id] === undefined) {
            // If it doesn't, add it
            badge_changes[badge_id] = { 
                levels : []
            };
        }
        
        //
        if (badge_changes[badge_id].levels[level] !== undefined) {
            
        }
        
    });
});