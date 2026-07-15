/*! 
jQuery edit billing details v1.0 
| (c) CBS Foundation and other contributors
| CBS/license 
*/
// When done loading: (also alias '$' for 'jQuery' inside this block)

let $ = jQuery.noConflict();

jQuery(document).ready(function ($) {

    $("button[id^='edit-button-'], button[id^='cancel-button-']").click(function () {
        var method_number = $(this).attr('id').split('-').pop();
        // Toggle input fields
        $("[id='cc-number-" + method_number + "']").toggle();
        $("[id='edit-cc-number-" + method_number + "']").toggle();
        $("[id='cc-exp-" + method_number + "']").toggle();
        $("[id='edit-cc-exp-" + method_number + "']").toggle();
        $("[id='cvv-label-" + method_number + "']").toggle();
        $("[id='edit-cvv-" + method_number + "']").toggle();
        $("[id='cc-address-" + method_number + "']").toggle();
        $("[id='edit-cc-address-" + method_number + "']").toggle();
        $("[id='cc-zip-" + method_number + "']").toggle();
        $("[id='edit-cc-zip-" + method_number + "']").toggle();
        // Toggle buttons
        $("button[id='edit-button-" + method_number + "']").toggle();
        $("button[id='cancel-button-" + method_number + "']").toggle();
        $("input[id='save-button-" + method_number + "']").toggle();
        $("button[id='unlock-delete-button-" + method_number + "']").toggle();
    });

    $("button[id^='unlock-delete-button-'], button[id^='cancel-delete-button-']").click(function () {

        var method_number = $(this).attr('id').split('-').pop();
        // Toggle confirmation message
        $("[id='delete-confirm-msg-" + method_number + "']").toggle();

        // Toggle buttons
        $("button[id='unlock-delete-button-" + method_number + "']").toggle();
        $("button[id='edit-button-" + method_number + "']").toggle();
        $("input[id='delete-button-" + method_number + "']").toggle();
        $("button[id='cancel-delete-button-" + method_number + "']").toggle();

    });

    $("#Add-new-card").click(function () {
        $("#method-card").show();
        $("#hide-method-bar-card").hide();
        $(".woocommerce-error").hide();
        $(".woocommerce-message").hide();
    });

    $("#cancel_button_card").click(function () {
        $("#method-card").hide();
        $("#hide-method-bar-card").show();
        return false;
    });

    $("#Add-new-bank").click(function () {
        $("#method-bank").show();
        $("#hide-method-bar-bank").hide();
        $(".woocommerce-error").hide();
        $(".woocommerce-message").hide();
    });

    $("#cancel_button_bank").click(function () {
        $("#method-bank").hide();
        $("#hide-method-bar-bank").show();
        return false;
    });

    // checkout: Ebizcharge payment method
    // on ebizcharge load
    loadExpiry();
    loadBankDetails();
    // on saved radio click
    $(document.body).on('ready click change', '#ebizcharge-use-stored-payment-info-yes', function () {
        loadExpiry();
    });
    // on update radio click
    $(document.body).on('ready click change', '#ebizcharge-use-update-payment-info-yes', function () {
        loadExpiry();
        loadAvs();
    });
    // on saved select box click/change
    $(document.body).on('ready click change', 'select[id=ebizcharge-stored-card]', function () {
        loadExpiry();
    });
    // on update select box click/change
    $(document.body).on('ready click change', 'select[id=ebizcharge_update_cc]', function () {
        loadExpiry();
        loadAvs();
    });
    // on saved bank select box click/change
    $(document.body).on('ready click change', 'select[id=ebizcharge-stored-bank]', function () {
        loadBankDetails();
    });

    function loadExpiry() {
        var methodId = $('select[id=ebizcharge-stored-card]').val();
        $('#exp_month_saved').val($('#exp_month_saved_' + methodId).val());
        $('#exp_year_saved').val($('#exp_year_saved_' + methodId).val());
        $('#card_number_saved').val($('#card_number_saved_' + methodId).val());
        $('#card_type_saved').val($('#card_type_saved_' + methodId).val());

        var methodIdUpdate = $('select[id=ebizcharge_update_cc]').val();
        $('#exp_month_update').val($('#exp_month_update_' + methodIdUpdate).val());
        $('#exp_year_update').val($('#exp_year_update_' + methodIdUpdate).val());
        $('#card_number_update').val($('#card_number_update_' + methodIdUpdate).val());
        $('#card_type_update').val($('#card_type_update_' + methodIdUpdate).val());
    }

    function loadAvs() {
        var methodId = $('select[id=ebizcharge_update_cc]').val();
        $('#avs_street').val($('#avs_street_' + methodId).val());
        $('#avs_zip').val($('#avs_zip_' + methodId).val());
    }

    function loadBankDetails() {
        var methodIdBank = $('select[id=ebizcharge-stored-bank]').val();
        $('#ac_holder_saved').val($('#ac_holder_saved_' + methodIdBank).val());
        $('#ac_type_saved').val($('#ac_type_saved_' + methodIdBank).val());
        $('#ac_number_saved').val($('#ac_number_saved_' + methodIdBank).val());
        $('#routing_saved').val($('#routing_saved_' + methodIdBank).val());
    }

    function saved_card_fields_validated() {
        let exp_month_saved = true;
        let exp_year_saved = true;
        let cvvS = true;
        let cvvSLength = $("#cvvS").val().length;

        let expiryMonthS = $('#exp_month_saved').val();
        let expiryYearS = $('#exp_year_saved').val();
        let expiryMonthYearS = expiryMonthS + "/" + expiryYearS;

        let currentDateS = new Date();
        let currentMonthS = currentDateS.getMonth() + 1;
        if (currentMonthS < 10) {
            currentMonthS = "0" + currentMonthS;
        }
        let currentYearS = currentDateS.getFullYear();
        let currentMonthYearS = currentMonthS + "/" + currentYearS;

        let validDate = validateMonthYear(expiryMonthYearS, currentMonthYearS); // -1 - 0 - 1

        if (expiryMonthS == '') {
            monthYearResults(".expmonthyear-saved-error", "#exp_month_saved", "<p>Expiration Date is required</p>");
            exp_month_saved = false;
        } else {
            $("#exp_month_saved").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryYearS == '') {
            monthYearResults(".expmonthyear-saved-error", "#exp_year_saved", "<p>Expiration Date is required</p>");
            exp_year_saved = false;
        } else {
            $("#exp_year_saved").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryMonthS != '' && expiryYearS != '' && validDate < 0) {
            monthYearResults(".expmonthyear-saved-error", "#exp_month_saved", "<p>Expiration Date is invalid</p>");
            exp_month_saved = false;
        }

        if (expiryMonthS != '' && expiryYearS != '' && validDate >= 0) {
            $('.expmonthyear-saved-error').html("");
            $('.expmonthyear-saved-error').hide();
        }

        var selectedCardSVal = $("#ebizcharge-stored-card option:selected").val();
        var selectedCardTypeS = $("#card_type_saved_" + selectedCardSVal).val();

        if ($('#cvvS').val() == '') {
            cvvResults(".cvv-saved-error", "#cvvS", "<p>CVV is required</p>");
            cvvS = false;

        } else if (selectedCardTypeS != 'A' && cvvSLength != 3) {
            cvvResults(".cvv-saved-error", "#cvvS", "<p>CVV is required, 3 digits</p>");
            cvvS = false;

        } else if (selectedCardTypeS == 'A' && cvvSLength != 4) {
            cvvResults(".cvv-saved-error", "#cvvS", "<p>CVV is required, 4 digits</p>");
            cvvS = false;

        } else {
            $("#cvvS").css({"border": "", "box-shadow": ""});
            $('.cvv-saved-error').html("");
            $('.cvv-saved-error').hide();
        }

        return exp_month_saved && exp_year_saved && cvvS;
    }

    function update_card_fields_validated() {
        let avs_street = true;
        let avs_zip = true;
        let exp_month_update = true;
        let exp_year_update = true;
        let cvvU = true;
        let cvvULength = $("#cvvU").val().length;

        let expiryMonthU = $('#exp_month_update').val();
        let expiryYearU = $('#exp_year_update').val();
        let expiryMonthYearU = expiryMonthU + "/" + expiryYearU;

        let currentDateU = new Date();
        let currentMonthU = currentDateU.getMonth() + 1;
        if (currentMonthU < 10) {
            currentMonthU = "0" + currentMonthU;
        }
        let currentYearU = currentDateU.getFullYear();
        let currentMonthYearU = currentMonthU + "/" + currentYearU;

        let validDate = validateMonthYear(expiryMonthYearU, currentMonthYearU); // -1 - 0 - 1

        if (expiryMonthU == '') {
            monthYearResults(".expmonthyear-update-error", "#exp_month_update", "<p>Expiration Date is required</p>");
            exp_month_update = false;
        } else {
            $("#exp_month_update").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryYearU == '') {
            monthYearResults(".expmonthyear-update-error", "#exp_year_update", "<p>Expiration Date is required</p>");
            exp_year_update = false;
        } else {
            $("#exp_year_update").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryMonthU != '' && expiryYearU != '' && validDate < 0) {
            monthYearResults(".expmonthyear-update-error", "#exp_month_update", "<p>Expiration Date is invalid</p>");
            exp_month_update = false;
        }

        if (expiryMonthU != '' && expiryYearU != '' && validDate >= 0) {
            $('.expmonthyear-update-error').html("");
            $('.expmonthyear-update-error').hide();
        }

        if ($('#avs_street').val() == '') {
            $("#avs_street").focus();
            //$(".avs_street").css("color", "red");
            $("#avs_street").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.billing-update-error').html("<p>Billing Street is required</p>");
            $('.billing-update-error').show();
            avs_street = false;
        } else {
            //$(".avs_street").css("color", "");
            $("#avs_street").css({"border": "", "box-shadow": ""});
            $('.billing-update-error').html("");
            $('.billing-update-error').hide();
        }

        if ($('#avs_zip').val() == '') {
            $("#avs_zip").focus();
            //$(".avs_zip").css("color", "red");
            $("#avs_zip").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.avs-update-error').html("<p>Zip/Postal Code is required</p>");
            $('.avs-update-error').show();
            avs_zip = false;
        } else {
            //$(".avs_zip").css("color", "");
            $("#avs_zip").css({"border": "", "box-shadow": ""});
            $('.avs-update-error').html("");
            $('.avs-update-error').hide();
        }

        var selectedCardUVal = $("#ebizcharge_update_cc option:selected").val();
        var selectedCardTypeU = $("#card_type_update_" + selectedCardUVal).val();

        if ($('#cvvU').val() == '') {
            cvvResults(".cvv-update-error", "#cvvU", "<p>CVV is required</p>");
            cvvU = false;

        } else if (selectedCardTypeU != 'A' && cvvULength != 3) {
            cvvResults(".cvv-update-error", "#cvvU", "<p>CVV is required, 3 digits</p>");
            cvvU = false;

        } else if (selectedCardTypeU == 'A' && cvvULength != 4) {
            cvvResults(".cvv-update-error", "#cvvU", "<p>CVV is required, 4 digits</p>");
            cvvU = false;

        } else {
            $("#cvvU").css({"border": "", "box-shadow": ""});
            $('.cvv-update-error').html("");
            $('.cvv-update-error').hide();
        }

        return exp_month_update && exp_year_update && avs_street && avs_zip && cvvU;
    }

    function new_card_fields_validated() {
        let ccholder = true;
        let ccnum = true;
        let cardtype = true;
        let expmonth = true;
        let expyear = true;
        let cvv = true;
        let numLength = $("#ccnum").val().length;
        let cvvLength = $("#cvv").val().length;

        let expiryMonth = $('#expmonth').val();
        let expiryYear = $('#expyear').val();
        let expiryMonthYear = expiryMonth + "/" + expiryYear;

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth() + 1;
        if (currentMonth < 10) {
            currentMonth = "0" + currentMonth;
        }
        let currentYear = currentDate.getFullYear();
        let currentMonthYear = currentMonth + "/" + currentYear;

        let validDate = validateMonthYear(expiryMonthYear, currentMonthYear); // -1 - 0 - 1

        if ($('#ccholder').val() == '') {
            $("#ccholder").focus();
            //$(".ccholder").css("color", "red");
            $("#ccholder").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.ccholder-new-error').html("<p>Name on Card is required</p>");
            $('.ccholder-new-error').show();
            ccholder = false;
        } else {
            //$(".ccholder").css("color", "");
            $("#ccholder").css({"border": "", "box-shadow": ""});
            $('.ccholder-new-error').html("");
            $('.ccholder-new-error').hide();
        }

        if ($('#ccnum').val() == '') {
            ccnumResults("<p>Credit Card# is required</p>");
            ccnum = false;

        } else if (numLength < 15 || numLength > 16) {
            ccnumResults("<p>Credit Card# is required, 15-16 digits</p>");
            ccnum = false;

        } else {
            $("#ccnum").css({"border": "", "box-shadow": ""});
            $('.ccnum-new-error').html("");
            $('.ccnum-new-error').hide();
        }

        if ($('#cardtype').val() == '') {
            $("#cardtype").focus();
            //$("#cardtype").css("color", "red");
            $("#cardtype").css({"border": "1px solid red", "border-radius": "2px"});
            $('.cardtype-new-error').html("<p>Credit Card Type is required</p>");
            $('.cardtype-new-error').show();
            cardtype = false;

        } else {
            //$(".cardtype").css("color", "");
            $("#cardtype").css({"border": "", "border-radius": "", "padding": ""});
            $('.cardtype-new-error').html("");
            $('.cardtype-new-error').hide();
        }

        if (expiryMonth == '') {
            monthYearResults(".expmonthyear-new-error", "#expmonth", "<p>Expiration Month is required</p>");
            expmonth = false;
        } else {
            $("#expmonth").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryYear == '') {
            monthYearResults(".expmonthyear-new-error", "#expyear", "<p>Expiration Year is required</p>");
            expyear = false;
        } else {
            $("#expyear").css({"border": "", "border-radius": "", "padding": ""}).removeClass('sixpixel');
        }

        if (expiryMonth != '' && expiryYear != '' && validDate < 0) {
            monthYearResults(".expmonthyear-new-error", "#expmonth", "<p>Expiration Date is invalid</p>");
            expmonth = false;
        }

        if (expmonth != '' && expyear != '' && validDate >= 0) {
            $('.expmonthyear-new-error').html("");
            $('.expmonthyear-new-error').hide();
        }

        if ($('#cvv').val() == '') {
            cvvResults(".cvv-new-error", "#cvv", "<p>CVV is required</p>");
            cvv = false;

        } else if ($('#cardtype').val() != 'American Express' && cvvLength != 3) {
            cvvResults(".cvv-new-error", "#cvv", "<p>CVV is required, 3 digits</p>");
            cvv = false;

        } else if ($('#cardtype').val() == 'American Express' && cvvLength != 4) {
            cvvResults(".cvv-new-error", "#cvv", "<p>CVV is required, 4 digits</p>");
            cvv = false;

        } else {
            $("#cvv").css({"border": "", "box-shadow": ""});
            $('.cvv-new-error').html("");
            $('.cvv-new-error').hide();
        }

        return ccholder && ccnum && cardtype && expmonth && expyear && cvv;
    }

    function new_bank_fields_validated() {
        let acholder = true;
        let acnum = true;
        let routingnum = true;
        var anumLength = $("#acnum").val().length;
        var rnumLength = $("#routingnum").val().length;

        if ($('#acholder').val() == '') {
            $("#acholder").focus();
            //$(".acholder").css("color", "red");
            $("#acholder").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.acholder-error').html("<p>Account Holder is required</p>");
            $('.acholder-error').show();
            acholder = false;
        } else {
            //$(".acholder").css("color", "");
            $("#acholder").css({"border": "", "box-shadow": ""});
            $('.acholder-error').html("");
            $('.acholder-error').hide();
        }

        if ($('#acnum').val() == '') {
            $("#acnum").focus();
            //$(".acnum").css("color", "red");
            $("#acnum").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.acnum-error').html("<p>Account Number is required</p>");
            $('.acnum-error').show();
            acnum = false;
        } else if (anumLength < 5 || anumLength > 17) {
            $("#acnum").focus();
            //$(".acnum").css("color", "red");
            $("#acnum").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.acnum-error').html("<p>Account Number is required, 5-17 digits</p>");
            $('.acnum-error').show();
            acnum = false;
        } else {
            $(".acnum").css("color", "");
            $("#acnum").css({"border": "", "box-shadow": ""});
            $('.acnum-error').html("");
            $('.acnum-error').hide();
        }

        if ($('#routingnum').val() == '') {
            $("#routingnum").focus();
            //$(".routingnum").css("color", "red");
            $("#routingnum").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.routingnum-error').html("<p>Routing Number is required</p>");
            $('.routingnum-error').show();
            routingnum = false;
        } else if (rnumLength != 9) {
            $("#routingnum").focus();
            //$(".routingnum").css("color", "red");
            $("#routingnum").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
            $('.routingnum-error').html("<p>Routing Number is required, 9 digits</p>");
            $('.routingnum-error').show();
            routingnum = false;
        } else {
            $(".routingnum").css("color", "");
            $("#routingnum").css({"border": "", "box-shadow": ""});
            $('.routingnum-error').html("");
            $('.routingnum-error').hide();
        }

        return acholder && acnum && routingnum;
    }

    function cvvResults(clas, id, msg) {
        $(id).focus();
        $(id).css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
        $(clas).html(msg);
        $(clas).show();
    }

    function ccnumResults(msg) {
        $("#ccnum").focus();
        $("#ccnum").css({"border": "1px solid red", "box-shadow": "inset 2px 0 0 red"});
        $('.ccnum-new-error').html(msg);
        $('.ccnum-new-error').show();
    }

    function monthYearResults(clas, id, msg) {
        $(id).focus();
        $(id).css({"border": "1px solid red", "border-radius": "2px"});
        $(clas).html(msg);
        $(clas).show();
    }

    function validateMonthYear(expiryDate, currentDate) {
        var m1 = expiryDate.split("/");
        var m2 = currentDate.split("/");
        var res = -1;

        if (parseInt(m1[1]) > parseInt(m2[1])) {
            res = 1;
        } else if (parseInt(m1[1]) == parseInt(m2[1])) {
            if (parseInt(m1[0]) > parseInt(m2[0])) {
                res = 1;
            } else if (parseInt(m1[0]) == parseInt(m2[0])) {
                res = 0;
            }
        }
        return res;
    }

    // 3DS functions
    if ($("#Is3DS2Enabled").val() == true) {
        var SecurityToken = {
            Password: "",
            UserId: "",
            SecurityId: $('#SecurityId').val()
        };

        eBiz3DSecure = new EBiz3DSecure(JSON.stringify(SecurityToken));
    }

    function EBiz3DSecureValidation() {
        return new Promise((theSuccess, theFailure) => {
            try {
                if (eBiz3DSecure.JWT == null || eBiz3DSecure.JWT == "") {
                    console.log('Merchant is not enabled for 3D Secure, continue with payment without 3D Secure');
                    theSuccess('NO_JWT')
                }
                const inputData = class InputData {
                    static CardType = $('#cardtype').val()
                    static CardNumber = $('#ccnum').val()
                    //static NameOnCard = $('#ccholder').val()
                    static ExpireMonth = $('#expmonth').val()
                    static ExpireYear = $('#expyear').val()
                    //static SecurityCode = $('#cvv').val()
                    static Amount = $('#orderAmount').val()
                    static OrderNumber = $('#orderNumber').val()
                    static CurrencyCode = $('#storeCurrency').val()
                    static BillingAddress = $('#billing_address_1').val() + ' ' + $('#billing_address_2').val()
                    //static BillingAddress2 = $('#billing_address_2').val()
                    static BillingCity = $('#billing_city').val()
                    static BillingCountryCode = "840"
                    static BillingFirstName = $('#billing_first_name').val()
                    static BillingLastName = $('#billing_last_name').val()
                    static BillingPhone = $('#billing_phone').val()
                    static BillingPostalCode = $('#billing_postcode').val()
                    static BillingState = "CA";
                    static Zip = $('#billing_postcode').val()
                    static ShippingCountryCode = "840"
                    static Email = $('#billing_email').val()
                    static phone = $('#billing_phone').val()
                    static TransactionType = "C"
                    static TransactionMode = "S"
                }

                eBiz3DSecure.EBiz3DSecureCheck(inputData)
                    .then(successData => {
                        theSuccess(successData)

                    }).catch(failureData => {
                    if (failureData.bypass3DS2OnError ?? false) {
                        theSuccess('bypass3DS2OnError')
                    } else {
                        // show error message and don't continue to payment process
                        theFailure(eBiz3DSecure.GetErrorMessage(failureData))
                    }
                });
            } catch (error) {
                console.log('error', error);
                theFailure(error);
            }
        });
    }

    function EBiz3DSecureValidationCall() {
        EBiz3DSecureValidation()
            .then(response => {
                $("#place_order").prop("disabled", false)
                hideLoader()
                if (response === 'NO_JWT' || response === 'bypass3DS2OnError') {
                    $("#WCPlaceOrder").val(true)
                    submitOrder()
                    return true
                } else {
                    //alert("Payment is authorized by 3D Secure.");
                    $("#CAVV").val(response.cavv)
                    $("#XID").val(response.xid)
                    $("#ECI").val(response.eciFlag)
                    $("#Pares").val(response.paResStatus)
                    $("#DSTransactionId").val(response.dsTransactionId)
                    $("#WCPlaceOrder").val(true)
                    submitOrder()
                    return true
                }
            }).catch(error => {
                $("#WCPlaceOrder").val(false);
                $("#place_order").prop("disabled", false);
                hideLoader();
                alert(error ?? "Payment is not Authorized by 3D Secure.");
                return false;
        });
    }

    function ebiz3DSecureValidationCallback() {
        return EBiz3DSecureValidation()
            .then(response => {
                return response; // Promise resolved
            }).catch(error => {
                throw error; // Throw the error for handling elsewhere if needed
            });
    }

    function submitOrder() {

        $('form#order_review').length > 0
            ? $('form#order_review').submit()
            : $('#place_order').submit();
    }

    $(document.body).on('click', '#place_order', function (event) {
        // Check payment methods is EbizCharge selected
        let selectedPaymentRadio = $("input[type='radio'][name='payment_method']:checked");

        if (selectedPaymentRadio.length > 0 && selectedPaymentRadio.val() == 'ebizcharge') {
            event.preventDefault();

            let is3DS2Enabled = $("#Is3DS2Enabled").val() == true;

            if ($("#ebizcharge-cc-payment").is(':checked')) {
                var selectedRadio = $("input[type='radio'][name='ebizcharge-use-stored-payment-info']:checked");
                if (selectedRadio.length > 0) {
                    let selectedRadioVal = selectedRadio.val();

                    if (selectedRadioVal == 'yes') {
                        // return saved_card_fields_validated();
                        if (saved_card_fields_validated()) {
                            submitOrder()
                        }

                    }

                    if (selectedRadioVal == 'update') {
                        // return update_card_fields_validated();
                        if (update_card_fields_validated()) {
                            submitOrder()
                        }
                    }

                    if (selectedRadioVal == 'no') {

                        if (new_card_fields_validated()) {
                            if (is3DS2Enabled) {
                                $('#place_order').attr("disabled", "disabled");
                                showLoader()
                                return EBiz3DSecureValidationCall()
                            } else {
                                submitOrder()
                            }
                        } else {
                            return false
                        }
                    }

                } else {
                    if (new_card_fields_validated()) {
                        if (is3DS2Enabled) {
                            event.preventDefault();
                            $('#place_order').attr("disabled", "disabled");
                            showLoader();
                            return EBiz3DSecureValidationCall();
                        } else {
                            submitOrder();
                        }
                    } else {
                        return false;
                    }
                }
            }

            if ($("#ebizcharge-bank-payment").is(':checked')) {
                let selectedBankRadio = $("input[type='radio'][name='ebizcharge-use-stored-bank-info']:checked")
                if (selectedBankRadio.length > 0) {
                    let selectedBankRadioVal = selectedBankRadio.val()

                    if (selectedBankRadioVal == 'yes') {
                        submitOrder();
                        // return true
                    }

                    if (selectedBankRadioVal == 'no') {
                        if (new_bank_fields_validated()) {
                            submitOrder();
                        }
                    }
                } else {
                    // return new_bank_fields_validated();
                    if (new_bank_fields_validated()) {
                        submitOrder();
                    }
                }
            }
        }
    });

    const hideLoader = () => {
        $('div.blockUI.blockOverlay').css('display', 'none')
    }

    const showLoader = () => {
        $('div.blockUI.blockOverlay').css('display', 'block')
    }

    const calculateSurcharge = () => {

        const isSurchargeEnabled = $("#surcharge_enabled").val();
        let cc = ''
        let selectedCardId = ''
        let zip = $("#billing_postcode").val()

        if (isSurchargeEnabled && $("#ebizcharge-cc-payment").is(':checked')) {
            let selectedRadio = $("input[type='radio'][name='ebizcharge-use-stored-payment-info']:checked");
            cc = $("#ccnum").val();

            if (selectedRadio.length > 0) {
                let selectedRadioVal = selectedRadio.val();

                if (selectedRadioVal === 'yes') {
                    selectedCardId = $("#ebizcharge-stored-card option:selected").val();
                    cc = ''

                } else if (selectedRadioVal === 'update') {
                    selectedCardId = $("#ebizcharge_update_cc option:selected").val();
                    cc = ''
                    zip = $("#avs_zip").val()
                }
            }

            if (((cc !== '' && cc.length > 14) || selectedCardId !== '')) {
                // Extract order id from the URL using a regular expression
                const orderIdMatch = window.location.href.match(/(?:[?&]order-pay=|\/order-pay\/)(\d+)/);
                const orderId = orderIdMatch ? orderIdMatch[1] : '';
                const data = {
                    action: 'calculate_surcharge',
                    'surcharge_enabled': isSurchargeEnabled,
                    'zip': zip,
                    'cc': cc,
                    'method_id': selectedCardId,
                    'order_id': orderId
                }

                showLoader()

                jQuery.post(ebiz_ajax_object.ebiz_ajax_url, data, function (response) {
                    if (response.success) {
                        $('#ebiz-surcharge').html(response.data);
                        $('#surcharge_amount').val(response.surcharge_amount);
                    }
                }).always(function () {
                    hideLoader()
                })
            }
        }
    }

    // admin order match
    if (window.location.href.match(/order-pay\/(\d+)/)) {
        calculateSurcharge()
    }

    $(document).on('click', 'input[name="ebizcharge-use-stored-payment-info"]', function () {
        calculateSurcharge()
    })

    // surcharge
    $(document.body).on(
        'change',
        '#billing_postcode, #avs_zip, #ccnum, #ebizcharge-stored-card, #ebizcharge_update_cc, #ebizcharge-use-stored-payment-info',
        calculateSurcharge
    )

    $(document.body).on('change', '#ebizcharge-bank-payment', function () {
        if ($("#ebizcharge-bank-payment").is(':checked')) {
            $('#ebiz-surcharge').hide();
        }
    })

    $(document.body).on('change', '#ebizcharge-cc-payment', function () {
        if ($("#ebizcharge-cc-payment").is(':checked')) {
            $('#ebiz-surcharge').show();
        }
    })

})
