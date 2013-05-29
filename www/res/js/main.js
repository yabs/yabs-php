// Define looping variables
var i,j;
        
// Function to round the corners of an element
// `selector` is the DOM Selector for the element
// `corners` can be an array, and int, or a string
//    The array should contain a radius for each corner, clockwise
//    The int is used if you want each corner the same pixel radius
//    The string is for every cornere to have the same value
$.fn.round = function(values) {
    // Array of Vendor Prefixes
    var vendors = [
        "-moz-",
        "-webkit-",
        "-khtml-",
        ""
    ];
    // Array of CSS positions, clockwise
    var positions = [
        "-top-left",
        "-top-right",
        "-bottom-right",
        "-bottom-left"
    ];
    
    // Check which format of values is supplied
    switch (typeof(values)) {
    // Check if it is an array
        case "object":
            // Validate arguments
            if (values.length !== 4) {
                throw ("Invalid Arguments");
            }
            
            // Remove any previous CSS
            for (j in vendors) {
                $(this).css("{0}border-radius".format(vendors[j]), "");
            }
            
            // Loop through each corner
            for (i in positions) {
                // Add 'px' suffix if it is an int
                if (typeof(values[i]) == "number") values[i] += "px";
                
                // Loop through each Vendor prefix
                for (j in vendors) {
                    // Apply the CSS
                    $(this).css(
                        "{0}border{1}-radius".format(vendors[j],positions[i]),
                        values[i]
                    );
                }
            }
            
            break;
        // Check if it is an int or a string
        case "number":
        case "string":
            if (typeof(values) == "number") {
                // Add 'px' suffix if it is an int
                values += "px";
            }
            
            // Remove any previous border-radius CSS
            for (i in positions) {
                for (j in vendors) {
                    // Apply the CSS
                    $(this).css(
                        "{0}border{1}-radius".format(vendors[j],positions[i]),
                        values[i]
                    );
                }
            }
            
            // Apply new CSS
            for (j in vendors) {
                $(this).css("{0}border-radius".format(vendors[j]), values);
            }
            
            break;
        default:
            throw ("Invalid Arguments");
    }
};

$.log = function(type, message) {
    switch(type) {
        case "error":
            window.setTimeout(function() {
                throw new Error(message);
            }, 0);
            break;
        
        case "info":
            console.log("Info: {0}".format(message));
            break;
        
        default: return;
    }
};

String.prototype.format = function() {
    var args = arguments;
    
    var formatted = this.replace(/\{(\d+)\}/g, function(match, number) {
        return (args[number] !== undefined ? args[number] : match);
    });
    
    return formatted;
};