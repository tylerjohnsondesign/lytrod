/*! 
jQuery CVV Validate v1.0 
| (c) CBS Foundation and other contributors
| CBS/license 
*/
function validate_cvv(str) {

    if (jQuery("input#place_order").attr("disabled") !== "disabled") {
        jQuery("input#place_order").attr("disabled", "disabled");
        jQuery("input#place_order").val("Please enter 3 or 4 numbers in the card security code field");
    }

    for (var i = 0; i < str.length; i++) {
        if (isNaN(parseInt(str.charAt(i), 10)) || i > 3) {
            return;
        }
    }

    if (i > 2) {
        jQuery("input#place_order").removeAttr("disabled");
        jQuery("input#place_order").val("Place order");
    }

}

function cardTypeAllowed(selectedCard, cardTypes) {
    return (cardTypes.indexOf(selectedCard) > -1);
}

function getCardType(value) {
    var result = '';

    if (/^5[12345]\d{14}$/.test(value)) {
        result = "MasterCard";

    } else if (/^4\d{12}(\d\d\d){0,1}$/.test(value)) {
        result = "Visa";

    } else if (/^6011\d{12}$/.test(value)) {
        result = "Discover";

    } else if (/^3[47]/.test(value)) {
        result = "American Express";
    }

    if (result !== '') {
        var allowedCardTypes = jQuery('#allowedCardTypes').val();
        var cardAllowed = cardTypeAllowed(result, allowedCardTypes);

        if (cardAllowed) {
            jQuery('#cardtype').val(result);
            jQuery('.cardtype-new-error').hide();
            jQuery("#cardtype").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        } else {
            jQuery('#cardtype').val('');
            jQuery('.cardtype-new-error').html("<p>" + result + " not allowed</p>");
            jQuery('.cardtype-new-error').show();
        }

    } else {
        jQuery('#cardtype').val('');
    }
}