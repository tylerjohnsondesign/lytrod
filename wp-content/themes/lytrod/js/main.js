

// Check if the current URL matches the specified URL
   if (window.location.href.indexOf("product/customer-success-digital-workflow-services/") > -1) {
    // Select the option with the value of "1" when the page fully loads
    window.addEventListener("load", function() {
        let selectBox = document.getElementById("years");

        // Hide the other options and the empty option
        for (let i = 0; i < selectBox.options.length; i++) {
            if ( selectBox.options[i].value !== "1 Year" ) {
                selectBox.options[i].style.display = "none";
            }
        }
    });

}