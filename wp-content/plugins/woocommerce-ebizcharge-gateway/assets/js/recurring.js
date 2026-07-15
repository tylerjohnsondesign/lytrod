/*! 
| jQuery Recurrings v1.0 
| (c) CBS Foundation and other contributors
| CBS/license 
*/
// When done loading: (also alias '$' for 'jQuery' inside this block)
jQuery(document).ready(function ($) {
    /* For Product detail page start */
    // Start Date Picker
    $('.sdate').datepicker({
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: false,
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        minDate: 0
    });

    // End Date Picker
    $('.edate').datepicker({
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: false,
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        minDate: 0
    });

    $(document).ready(function () {
        // Subscription fileds show/hide for product detail page
        $('.subs_frequency').hide();
        $('.subs_dates').hide();
        $('.subs_indefinitely').hide();
        $('.subs_message').hide();
        $("#otp").attr('checked', true);
        $('.woocommerce-recurring-frequency').hide();
        $('.woocommerce-recurring-sdate').hide();
        $('.woocommerce-recurring-invalid-dates').hide();

        // Payment options show/hide sub edit page		
        if ($('.selectdivPaymentCc').val()) {
            $('.cc-div').show();
            $('.cc-div').prop("disabled", false);
            $('.selectdivPaymentCc').prop("disabled", false);
            $('.bank-div').hide();
            $('.bank-div').prop("disabled", true);
            $('.selectdivPaymentBank').prop("disabled", true);
            $(".cc-method").css("border-bottom", "3px solid red");
            $(".check-method").css("border-bottom", "3px solid transparent");
        } else if ($('.selectdivPaymentBank').val()) {
            $('.cc-div').hide();
            $('.cc-div').prop("disabled", true);
            $('.selectdivPaymentCc').prop("disabled", true);
            $('.bank-div').show();
            $('.bank-div').prop("disabled", false);
            $('.selectdivPaymentBank').prop("disabled", false);
            $(".cc-method").css("border-bottom", "3px solid transparent");
            $(".check-method").css("border-bottom", "3px solid red");
        } else {
            $('.cc-div').show();
            $('.cc-div').prop("disabled", false);
            $('.selectdivPaymentCc').prop("disabled", false);
            $('.bank-div').hide();
            $('.bank-div').prop("disabled", true);
            $('.selectdivPaymentBank').prop("disabled", true);
            $(".cc-method").css("border-bottom", "3px solid red");
            $(".check-method").css("border-bottom", "3px solid transparent");
        }
    });

    $("input[name='subscription']").click(function () {
        var selectedRadioVal = "";
        var selectedRadio = $("input[type='radio'][name='subscription']:checked");
        if (selectedRadio.length > 0) {
            selectedRadioVal = selectedRadio.val();
        }

        if (selectedRadioVal == 1) {
            $('.subs_frequency').show();
            $('.subs_dates').show();
            $('.subs_indefinitely').show();
            $('.subs_message').show();

        } else {
            $('.subs_frequency').hide();
            $('.subs_dates').hide();
            $('.subs_indefinitely').hide();
            $('.subs_message').hide();
            $('#frequency').val('');
            $('#sdate').val('');
            $('#edate').val('');
            $('#indefinitely').prop('checked', false); // Unchecks it
        }
    });

//-------------------------------------------------------------------------------------
if ($('#indefinitely').prop("checked") == true) {
    $('#edate').prop("disabled", true);
} else {
    $('#edate').prop("disabled", false);
}
//------------------------
$('#indefinitely').click(function () {
    if ($(this).prop("checked")) {
        $('#edate').prop("disabled", true);
        $('#edate').val('');
        $(this).val(1);
    } else {
        $('#edate').prop("disabled", false);
        $(this).val(0);
    }
});

//----------------------------------------------------------------------------------------

    $('.single_add_to_cart_button').click(function () {
        if ($('#otp').is(':checked')) {
            return true;
        }

        if ($('#frequency').val() === '') {
            $('.woocommerce-recurring-frequency').show();
            return false;
        } else {
            $('.woocommerce-recurring-frequency').hide();
        }

        if ($('#sdate').val() === '') {
            $('.woocommerce-recurring-sdate').show();
            return false;
        } else {
            $('.woocommerce-recurring-sdate').hide();
        }

        if ($('.indefinitely').prop('checked') === false) {
            if (frequencyInvalidDates()) {
                $('.woocommerce-recurring-invalid-dates').show();
                return false;
            } else {
                $('.woocommerce-recurring-invalid-dates').hide();
            }
        }

        return true;
    });
    /* For Product detail page end */

    /* For Subscription edit page start */
    $("input[name='ebzc_option_type']").click(function () {
        var selectedRadioVal = "";
        var selectedRadio = $("input[type='radio'][name='ebzc_option_type']:checked");
        if (selectedRadio.length > 0) {
            selectedRadioVal = selectedRadio.val();
        }

        if (selectedRadioVal == 'cc') {
            $('.cc-div').show();
            $('.cc-div').prop("disabled", false);
            $('.selectdivPaymentCc').prop("disabled", false);
            $('.bank-div').hide();
            $('.bank-div').prop("disabled", true);
            $('.selectdivPaymentBank').prop("disabled", true);
            $(".cc-method").css("border-bottom", "3px solid red");
            $(".check-method").css("border-bottom", "3px solid transparent");
        } else if (selectedRadioVal == 'check') {
            $('.cc-div').hide();
            $('.cc-div').prop("disabled", true);
            $('.selectdivPaymentCc').prop("disabled", true);
            $('.bank-div').show();
            $('.bank-div').prop("disabled", false);
            $('.selectdivPaymentBank').prop("disabled", false);
            $(".cc-method").css("border-bottom", "3px solid transparent");
            $(".check-method").css("border-bottom", "3px solid red");
        }
    });

    $('form[id="update"]').validate({
        rules: {
            qty: {required: true, number: true, min: 1},
            frequency: {required: true},
            sdate: {required: true},
            shippingMethod: {required: true},
            selectdivPayment: {required: true}
        },
        messages: {
            qty: 'Minimum 1 quantity is required',
            frequency: 'Frequency is required',
            sdate: 'Start date is required',
            shippingMethod: 'Shipping method is required',
            selectdivPayment: 'Payment method is required'
        }
    });

    $(".save_btn").click(function () {
        $("#update").valid();

        if ($('.indefinitely').prop("checked") == false) {

            if (frequencyInvalidDates()) {
                $('.show-error').css('display', 'block');
                $('.show-error').html('<li>Please select valid dates for the selected frequency.</li>');
                animate('#sub-alerts');
                return false;

            } else {
                //$('#update').submit();
            }
        } else {
            //$('#update').submit();

        }

        if (!$("#update").valid()) {
            return false;
        }

        $.confirm({
            title: 'Update Subscription',
            content: 'Are you sure you want to update this subscription?',
            boxWidth: '400px',
            useBootstrap: false,
            buttons: {
                update: function () {
                    $('#update').submit();
                },
                cancel: function () {
                    //$.alert('Canceled!');
                }
            }
        });
    });

    $('.unsub_btn').click(function () {
        $.confirm({
            title: 'Unsubscribe',
            content: 'Are you sure you want to unsubscribe to this product?',
            boxWidth: '500px',
            useBootstrap: false,
            buttons: {
                unsubscribe: function () {
                    $('#unsub').submit();
                },
                cancel: function () {
                    //$.alert('Canceled!');
                }
            }
        });
    });

    $('.delsub_btn').click(function () {
        $.confirm({
            title: 'Delete',
            content: 'Are you sure to delete this subscription?',
            boxWidth: '400px',
            useBootstrap: false,
            buttons: {
                delete: function () {
                    $('#delsub').submit();
                },
                cancel: function () {
                    //$.alert('Canceled!');
                }
            }
        });
    });

    $('.resub_btn').click(function () {
        if (resubInvalidDates()) {
            $.confirm({
                title: 'Resubscribe',
                content: 'Are you sure you want to resubscribe to this subscription?',
                boxWidth: '400px',
                useBootstrap: false,
                buttons: {
                    resubscribe: function () {
                        $('#resub').submit();
                    },
                    cancel: function () {
                        //$.alert('Canceled!');
                    }
                }
            });
        } else {
            $.confirm({
                title: 'Hello',
                boxWidth: '400px',
                useBootstrap: false,
                content: 'Please update subscription with valid future dates before resubscribe.',
                buttons: {
                    ok: function () {
                        // Do nothing.
                    }
                }
            });
        }
    });

    function animate(idorclass) {
        $('html, body').animate({
            scrollTop: $(idorclass).offset().top
        }, 500);
    }

    function resubInvalidDates() {
        const frequency = $(".frequency").val();
        const today = new Date().toISOString().slice(0, 10);
        const endDate = $(".edate").val();

        const frequencyDays = getDaysFromFrequency(frequency);

        const diffInMs = new Date(endDate) - new Date(today)
        const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

        return Math.round(diffInDays) > Math.round(frequencyDays);
    }

    function frequencyInvalidDates() {
        const frequency = $(".frequency").val();
        const startDate = $(".sdate").val();
        const endDate = $(".edate").val();

        if (frequency === '' || startDate ===  '' || endDate === '') {
            return true;
        }

        const frequencyDays = getDaysFromFrequency(frequency);

        const diffInMs = new Date(endDate) - new Date(startDate)
        const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

        return Math.round(diffInDays) < Math.round(frequencyDays);
    }

    function getDaysFromFrequency(frequency) {
        var days = 1;
        switch (frequency) {
            case "daily":
                days = 1;
                break;
            case "weekly":
                days = 7;
                break;
            case "monthly":
                days = 30;
                break;
            case "quarterly":
            case "three-month":
            case "90-days":
                days = 90;
                break;
            case "two-month":
                days = 60;
                break;
            case "four-month":
                days = 120;
                break;
            case "five-month":
                days = 150;
                break;
            case "bi-weekly":
                days = 14;
                break;
            case "bi-monthly":
                days = 15;
                break;
            case "bi-annually":
            case "six-month":
            case "180-days":
                days = 180;
                break;
            case "four-week":
                days = 28;
                break;
            case "annually":
                days = 365;
                break;
            default:
                days = 30;
        }
        return days;
    }

    /* For Subscription edit page end */

});