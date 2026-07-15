/*! 
| jQuery Subscriptions v1.0 
| (c) CBS Foundation and other contributors
| CBS/license 
*/
jQuery(document).ready(function () {
    /* For Admin pages Loader start */
    function show_loader() {
        jQuery('.ajax-loader').css('visibility', 'visible')
    }

    function hide_loader() {
        jQuery('.ajax-loader').css('visibility', 'hidden')
    }
    
    jQuery(".woocommerce-save-button").html("Save Changes");
    var submitedForm = jQuery('form[method="post"]');
    jQuery(document).on('submit', submitedForm, function () {
        if (jQuery('#bulk-action-selector-top').val() == 'exportCSV') {
            hide_loader()
        } else {
            show_loader()
        }
    });

    jQuery("button.action-next").click(function () {
        show_loader()
    });

    jQuery("button.action-prev").click(function () {
        show_loader()
    });

    /* For Admin pages Loader end */
    // @todo this should be fixed, #doaction impacting other modules
    jQuery('#doaction, #doaction2').attr('disabled', 'disabled');

    jQuery('#bulk-action-selector-top, #bulk-action-selector-bottom').change(function () {
        if (jQuery(this).val() != -1) {
            jQuery('#doaction, #doaction2').removeAttr('disabled');
        } else {
            jQuery('#doaction, #doaction2').attr('disabled', 'disabled');
        }
    });

//-----------------------------------------------------------------------------

    jQuery('#doaction').click(function () {
        var count_email = jQuery("[name='bulk-email[]']:checked").length;
        var count_delete = jQuery("[name='bulk-delete[]']:checked").length;
        var searchFilter = jQuery('#search-submit').val();
        var selectedValue = jQuery('#bulk-action-selector-top').val();
        if (searchFilter === 'Search' && count_delete < 1) {
            return showAlert();
        }
        if (searchFilter !== 'Search' && count_email < 1) {
            return showAlert();
        }

        if (searchFilter != 'Search' && count_email > 0 && selectedValue == 'exportHistory') {
            return exportHistory();
        }
    });

    function showAlert() {
        jQuery.confirm({
            title: 'Bulk Action',
            content: 'Please check at least one record!',
            boxWidth: '400px',
            useBootstrap: false,
            buttons: {
                OK: function () {
                }
            }
        });

        return false;
    }

    function exportHistory() {
        var csv = [];
        var header = [];
        var table_id = 'subscriptions_page_payment-history';
        var rows = document.querySelectorAll('table.' + table_id + ' tr');

        jQuery('table > thead  > tr > th').each(function (index, th) {
            if (index < 11) {
                var thead = th.innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                thead = thead.replace(/[↕]/g, '');
                thead = thead.replace(/"/g, '""');
                header.push('"' + thead + '"');
            }
        });
        csv.push(header.join(','));
        jQuery(".subscriptions_page_payment-history input[type=checkbox]:checked").each(function () {
            var row = jQuery(this).closest("tr")[0];
            var rec = [];
            jQuery(row).find('td').each(function (index, td) {
                if (index < 11) {
                    var data = td.innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                    data = data.replace(/[↕]/g, '');
                    data = data.replace(/"/g, '""');
                    rec.push('"' + data + '"');
                }
            });
            csv.push(rec.join(','));

        });
        var csv_string = csv.join('\n');
        var filename = 'Subscriptions_payment_history_' + new Date().toLocaleDateString() + '.csv';
        var link = document.createElement('a');
        link.style.display = 'none';
        link.setAttribute('target', '_blank');
        link.setAttribute('href', 'data:application/csv;charset=UTF-8,%EF%BB%BF' + encodeURIComponent(csv_string));
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        return false;
    }

//---------------------------------------------------------------------------

    /* Onclick crear dates start */
    jQuery('#clearDates').on('click', function () {
        jQuery('#start_date_search').val('');
        jQuery('#expire_date_search').val('');
        jQuery('#user_email').val('');
        jQuery('#search_keyword').val('');
        jQuery('#freqId').val('');
        var urlClear = jQuery('#urlClear').val();
        if (urlClear != '') {
            window.location = urlClear;
        }
    });
    /* Onclick crear dates end */
});

jQuery(document).ready(function () {
    /* CC and ACH form validations start */
    var payment_type = jQuery('#ebiz_option').val();
    var page_case = jQuery('#pageCase').val();

    if (page_case == 'edit' && payment_type == 'ACH') {
        jQuery('#ebizs_option_ach').prop('checked', true);
        jQuery("#ebizs_option_ach").trigger('click');
        jQuery('#add-new').hide();
        jQuery('#add-new-card-saved').show();
        jQuery('#saved').prop('checked', true);
        jQuery("#saved").trigger('click');
        jQuery('#newLabel').html('New Bank Account');
        jQuery('#savedLabel').html('Saved Bank Accounts');
        jQuery('.update-radio').hide();
        jQuery('#add-new-card-update').hide();
        getSavedMethodsByCustomer();
    } else if (page_case == 'edit' && payment_type == 'credit_card') {
        jQuery('#ebizs_option').prop('checked', true);
        jQuery("#ebizs_option").trigger('click');
        jQuery('#add-new').hide();
        jQuery('#add-new-card-saved').show();
        jQuery('#saved').prop('checked', true);
        jQuery("#saved").trigger('click');
        jQuery('#newLabel').html('New Card');
        jQuery('#savedLabel').html('Saved Card');
        jQuery('.update-radio').show();
        jQuery('#add-new-card-update').hide();
        getSavedMethodsByCustomer();
    } else {
        jQuery('#ebizs_option').prop('checked', true);
        jQuery("#ebizs_option").trigger('click');
        jQuery('#add-new').show();
        jQuery('#add-new-card-saved').hide();
        jQuery('#add-new-card-update').hide();
        jQuery('#payment_avs_street_update').val('');
        jQuery('#payment_avs_zip_update').val('');


    }
    var todaydt = new Date();
    jQuery('#start_date_search').datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function (date) {
            var date2 = jQuery('#start_date_search').datepicker('getDate');
            jQuery('#expire_date_search').datepicker('option', 'minDate', date2);
        }
    });
    jQuery('#rec_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    jQuery('#expire_date_search').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    // Here order create daterange picker is added.
    jQuery(".dateclassorder").datepicker({
        dateFormat: "yy-mm-dd",
        maxDate: 0,
        showButtonPanel: true,
        beforeShowDay: function (date) {
            var date1 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_date").val());
            var date2 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_end_date").val());
            return [true, date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2)) ? "dp-highlight" : ""];
        },
        onSelect: function (dateText, inst) {
            var olddate1 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_date").val());
            var olddate2 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_end_date").val());
            jQuery(this).val(dateText);
            var date1 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_date").val());
            var date2 = jQuery.datepicker.parseDate('yy-mm-dd', jQuery("#order_end_date").val());
            var selectedDate = jQuery.datepicker.parseDate('yy-mm-dd', dateText);

            if (date2 < date1) {
                if (dateText == jQuery("#order_date").val()) {
                    jQuery("#order_end_date").val(jQuery("#order_date").val());
                } else {
                    jQuery("#order_date").val(jQuery("#order_end_date").val());
                }
            }
        }
    });

    jQuery('#start_date').datepicker({
        dateFormat: 'yy-mm-dd', minDate: 0
    });
    jQuery('#expire_date').datepicker({
        dateFormat: 'yy-mm-dd', minDate: 0
    });
    jQuery("#payment_cc_number_new").bind("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode;
        if (!(keyCode >= 48 && keyCode <= 57)) {
            return false;
        } else {
            // nothing
        }
    });
    if (jQuery("#requestCC").val() == 1) {
        jQuery("#payment_cc_cid_new").bind("keypress", function (e) {
            var keyCode = e.which ? e.which : e.keyCode
            if (!(keyCode >= 48 && keyCode <= 57)) {
                return false;
            }
        });
    }
    //cc_owner_ach
    // Validate form start
    jQuery('form[id="form-validate"]').validate({
        rules: {
            selectdivProduct: {required: true},
            qty: {required: true, number: true, min: 1},
            customer_id: {required: true},
            addressBill: {required: true},
            addressShip: {required: true},
            schedule: {required: true},
            shipping_method: {required: true},
            shipping_amount: {required: true},
            'avs_street': {required: true},
            'avs_zip': {required: true, number: true},
            'method_id': {required: true},
            'payment[cc_owner]': {required: true},
            'payment[cc_number]': {required: true, number: true, minlength: 15, maxlength: 16},
            'payment[cc_type]': {required: true},
            'payment[cc_exp_month]': {required: true},
            'payment[cc_exp_year]': {required: true},
            'payment[cc_cid]': {
                required: true,
                number: true,
                minlength: function () {
                    var cardtype = jQuery("#payment_cc_type_new").val();
                    if (cardtype === "American Express") {
                        return 4;
                    } else {
                        return 3;
                    }
                },
                maxlength: function () {
                    var cardtype = jQuery("#payment_cc_type_new").val();
                    if (cardtype === "American Express") {
                        return 4;
                    } else {
                        return 3;
                    }
                }
            },
            'payment[avs_street]': {required: true},
            'payment[cc_number_ach]': {required: true, number: true, minlength: 5, maxlength: 17},
            'payment[cc_routing_ach]': {required: true, number: true, minlength: 9, maxlength: 9},
            'payment[avs_zip]': {required: true, number: true}
        }, messages: {
            selectdivProduct: 'Product is required',
            qty: 'Minimum Quantity of 1 is required',
            customer_id: 'Customer is required',
            addressBill: 'Billing Address is required',
            addressShip: 'Shipping Address is required',
            schedule: 'Frequency is required',
            shipping_method: 'Shipping Method is required',
            shipping_amount: 'Shipping Amount is required',
            'avs_street': 'Billing Street is required',
            'avs_zip': 'Billing Zip is required, digits only',
            'method_id': 'Valid Payment Method is required',
            'payment[cc_owner]': 'Name on Card is required',
            'payment[cc_number]': 'Credit Card Number is required, 15-16 digits',
            'payment[cc_type]': 'Credit Card Type is required',
            'payment[cc_exp_month]': 'Expiration Month is required',
            'payment[cc_exp_year]': 'Expiration Year is required',
            'payment[cc_cid]': {
                required: 'CVV is required, 3-4 digits',
                number: '3-4 digits only',
                minlength: function () {
                    var cardtype = jQuery("#payment_cc_type_new").val();
                    if (cardtype === "American Express") {
                        return 'CVV is required 4 digits';
                    } else {
                        return 'CVV is required 3 digits';
                    }
                },
                maxlength: function () {
                    var cardtype = jQuery("#payment_cc_type_new").val();
                    if (cardtype === "American Express") {
                        return 'CVV is required 4 digits';
                    } else {
                        return 'CVV is required 3 digits';
                    }
                }
            },
            'payment[avs_street]': 'Billing Street is required',
            'payment[avs_zip]': 'Billing Zip is required, numbers only',
            'payment[cc_owner_ach]': 'Account Holder is required',
            'payment[cc_type_ach]': 'Account Type is required',
            'payment[cc_number_ach]': 'Account Number is required, 5-17 digits',
            'payment[cc_routing_ach]': 'Routing Number is required, 9 digits',
        },
    });
    // Validate form end

    jQuery('.unsub_btn').click(function () {
        if (jQuery('#sid').val() == 0) {
            if (resubInvalidDates()) {
                jQuery.confirm({
                    title: 'Resubscribe',
                    content: 'Are you sure you want to resubscribe to this subscription?',
                    boxWidth: '400px',
                    useBootstrap: false,
                    buttons: {
                        resubscribe: function () {
                            jQuery('#unsub').submit();
                        }, cancel: function () {
                            //jQuery.alert('Canceled!');
                        }
                    }
                });
            } else {
                jQuery.alert({
                    title: 'Alert!',
                    boxWidth: '400px',
                    useBootstrap: false,
                    content: 'Please update subscription with valid future dates before resubscribe.',
                });
            }
        } else {
            jQuery.confirm({
                title: 'Unsubscribe',
                content: 'Are you sure you want to unsubscribe from this product?',
                boxWidth: '400px',
                useBootstrap: false,
                buttons: {
                    unsubscribe: function () {
                        jQuery('#unsub').submit();
                    }, cancel: function () {
                        //jQuery.alert('Canceled!');
                    }
                }
            });
        }
    });

    jQuery('.delsub_btn').click(function () {
        jQuery.confirm({
            title: 'Delete',
            content: 'Are you sure to delete this subscription?',
            boxWidth: '400px',
            useBootstrap: false,
            buttons: {
                delete: function () {
                    jQuery('#delsub').submit();
                }, cancel: function () {
                    //jQuery.alert('Canceled!');
                }
            }
        });
    });

    jQuery('.save_btn').click(function (e) {

        var isValidated = jQuery("#form-validate").valid();

        if (jQuery('#ebiz_option_payment').val() == 'new' && jQuery('#ebiz_option').val() == 'credit_card') {

            // check for credit card number
            var numLength = jQuery("#payment_cc_number_new").val().length;
            var validity = expiryDateValidation('#payment_exp_new', '#payment_exp_yr_new');
            var expiryMonth = jQuery("#payment_exp_new").val();

            //if (numLength != 16 )
            if (numLength != "" && (numLength < 15 || numLength > 16)) {
                jQuery("#payment_cc_number_new").focus();
                return false;
            }
            if (jQuery("#requestCC").val() == 1) {
                // check for CVV
                var cvvLength = jQuery("#payment_cc_cid_new").val().length;
                if (cvvLength < 3 || cvvLength > 4) {
                    //jQuery("#payment_cc_cid_new").focus();
                    //return false;
                } else {
                    jQuery('#payment_msgCvv').hide();
                }
            }
            // for checking valid expiry dates
            if (expiryMonth !== '' && validity < 0) {
                jQuery("#payment_exp_new").focus();
                jQuery("#payment_exp_new-error").show();
                jQuery('#payment_exp_new-error').html("Expiration Month is invalid");
                return false;
            }
        }

        if (jQuery("#start_date").val() == '') {
            jQuery('#start_date_required').html("<div>Start Date is required.</div>");
        } else {
            jQuery('#start_date_required').html('');
        }

        if (jQuery('#rec_indefinitely').prop("checked") == false) {
            var frequency = jQuery("#freqId").val();
            var sdate = jQuery("#start_date").val();
            var edate = jQuery("#expire_date").val();
            var frequencyDays = getDaysFromFrequency(frequency);

            const diffInMs = new Date(edate) - new Date(sdate)
            const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

            if (jQuery("#expire_date").val() == '') {
                jQuery('#Datecheck').html("<div>End Date is required.</div>");
            } else if (Math.round(diffInDays) < Math.round(frequencyDays)) {
                jQuery('#Datecheck').html("<div>Please select valid Subscription Dates for " + frequency + " frequency.</div>");
            } else {
                jQuery('#Datecheck').html("");
                if (jQuery("#action").val() == "update_applicant" && isValidated == true) {
                    //compareStartDate();
                    jQuery.confirm({
                        title: 'Update Subscription',
                        content: 'Are you sure you want to save this subscription?',
                        boxWidth: '400px',
                        useBootstrap: false,
                        buttons: {
                            update: function () {
                                handleSubmit()
                            }, cancel: function () {
                                //jQuery.alert('Canceled!');
                            }
                        }
                    });
                }

                if (jQuery("#action").val() == "creat_applicant" && isValidated == true) {
                    jQuery.confirm({
                        title: 'Save Subscription',
                        content: 'Are you sure you want to save this subscription?',
                        boxWidth: '400px',
                        useBootstrap: false,
                        buttons: {
                            save: function () {
                                checkRecDate();
                            }, cancel: function () {
                                //jQuery.alert('Canceled!');
                            }
                        }
                    });
                }
            }
        } else {

            if (jQuery("#action").val() == "update_applicant" && isValidated == true) {
                //compareStartDate();
                jQuery.confirm({
                    title: 'Update Subscription',
                    content: 'Are you sure you want to update this subscription?',
                    boxWidth: '400px',
                    useBootstrap: false,
                    buttons: {
                        update: function () {
                            handleSubmit()
                        }, cancel: function () {
                            //jQuery.alert('Canceled!');
                        }
                    }
                });
            }

            if (jQuery("#action").val() == "creat_applicant" && isValidated == true) {
                jQuery('#Datecheck').html("");
                jQuery.confirm({
                    title: 'Save Subscription',
                    content: 'Are you sure to save this subscription?',
                    boxWidth: '400px',
                    useBootstrap: false,
                    buttons: {
                        save: function () {
                            checkRecDate();
                        }, cancel: function () {
                            //jQuery.alert('Canceled!');
                        }
                    }
                });
            }
        }

    });

}); // end of ready function

jQuery(window).ready(function () {
    jQuery('#selectdivPayment').ready(function () {
        getDropdownSelected();
    });
});

jQuery(document).ready(function () {
    jQuery('input[name="qty"]').keyup(function (e) {
        if (/\D/g.test(this.value)) {
            this.value = this.value.replace(/\D/g, '');
        }
    });

//------------------------------------------------------------------------
    if (jQuery('#rec_indefinitely').prop("checked") == true) {
        jQuery('#expire_date').prop("disabled", true);
    } else {
        jQuery('#expire_date').prop("disabled", false);
    }
//----------------------------
    jQuery('#rec_indefinitely').click(function () {
        jQuery('#Datecheck').html("");
        if (jQuery(this).prop("checked") == true) {
            jQuery('#expire_date').prop("disabled", true);
        } else if (jQuery(this).prop("checked") == false) {
            jQuery('#expire_date').prop("disabled", false);
        }
    });

    jQuery('#selectdivCustomer').change(function () {
        if ((this).value != "") {
            var data = {
                'action': 'get_billaddress',
                'customerId': this.value
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response != "") {
                    jQuery('#addressBill').empty();
                    jQuery('#addressBill').html(response);
                } else {
                    jQuery('#addressBill').html('<option value="">No billing address found</option>');
                }
            });
        } else {
            jQuery('#addressBill').html('<option value="">Please Select Billing Address</option>');
        }
    });

    jQuery('#shippingMethod').on('click ready change', function () {
        if ((this).value != "") {
            var data = {
                'action': 'get_shipping_amount',
                'shipping_method': this.value
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response != "") {
                    jQuery('#shipping_amount').val('');
                    jQuery('#shipping_amount').val(response);
                } else {
                    jQuery('#shipping_amount').val('');
                }
            });
        } else {
            jQuery('#shipping_amount').val('');
        }
    });

    jQuery('#selectdivCustomer').change(function () {

        if ((this).value != "") {
            var data = {
                'action': 'get_shipaddress',
                'customerId': this.value
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response != "") {
                    jQuery('#addressShip').empty();
                    jQuery('#addressShip').html(response);
                } else {
                    jQuery('#addressShip').html('<option value="">No shipping address found</option>');
                }
            });
        } else {
            jQuery('#addressShip').html('<option value="">Please Select Shipping Address</option>');
        }
    });

    jQuery('#selectdivCustomer').change(function () {

        var data = {
            'action': 'get_customs_paymentmethods',
            'customerId': this.value,
            'paymentType': jQuery('#ebiz_option').val(),
        };

        jQuery.post(ajaxurl, data, function (response) {

            if (response != "") {
                jQuery('#selectdivPayment').empty();
                jQuery('#selectdivPayment').html(response);
                if (jQuery('#ebiz_option_payment').val() == 'saved') {
                    jQuery('#payment_method_name').val(jQuery('#selectdivPayment option:selected').text());
                } else {
                    jQuery('#payment_method_name').val('');
                }

            } else {
                jQuery('#selectdivPayment').html('<option value="">No Saved Method(s)</option>');
            }
        });

    });

    jQuery('#selectdivPayment').change(function () {

        if (jQuery('#ebiz_option_payment').val() == 'saved') {
            getDropdownSelected();
        } else if (jQuery('#ebiz_option_payment').val() == 'update') {
            getDropdownSelected();
            getMethodProfile();
        } else {
            jQuery('#payment_method_name').val('');
        }
    });

    jQuery("#options_add_new").prop('checked', false);

    jQuery('.ebizs_option').on('click ready change', function () {

        if (this.value == 'credit_card') {
            jQuery('#new').prop('checked', true);
            jQuery("#new").trigger('click');
            window.scrollBy(0, 180);
            jQuery('#add-new').show();
            jQuery('#add-new-card-saved').hide();
            jQuery('#add-new-ach').hide();
            jQuery('#ach-saved').hide();
            jQuery('#ebiz_option').val('credit_card');
            jQuery('#newLabel').html('New Card');
            jQuery('#savedLabel').html('Saved Cards');
            jQuery('.update-radio').show();
        } else if (this.value == 'ACH') {
            jQuery('#new').prop('checked', true);
            jQuery("#new").trigger('click');
            window.scrollBy(0, 180);
            jQuery('#add-new-ach').show();
            jQuery('#add-new').hide();
            jQuery('#add-new-card-saved').hide();
            jQuery('#ach-saved').hide();
            jQuery('#ebiz_option').val('ACH');
            jQuery('#newLabel').html('New Bank Account');
            jQuery('#savedLabel').html('Saved Bank Accounts');
            jQuery('.update-radio').hide();
            jQuery('#add-new-card-update').hide();
        }
    });

    jQuery('.ebiz_option_card').on('click ready change', function () {

        var paymenOption = jQuery('#ebiz_option').val();

        if (this.value == 'new' && paymenOption == 'credit_card') {
            window.scrollBy(0, 100);
            jQuery('#add-new').show();
            jQuery('#add-new-card-saved').hide();
            jQuery('#add-new-card-update').hide();
            jQuery('#add-new-ach').hide();
            jQuery('#ebiz_option_payment').val('new');
        } else if (this.value == 'saved' && paymenOption == 'credit_card') {
            var customerid = jQuery('#selectdivCustomer').val();
            var data = {
                'action': 'get_customs_paymentmethods',
                'customerId': customerid,
                'paymentType': jQuery('#ebiz_option').val(),
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response != "") {
                    jQuery('#selectdivPayment').empty();
                    jQuery('#selectdivPayment').html(response);
                    getDropdownSelected();
                } else {
                    jQuery('#selectdivPayment').html('<option value="">No Saved Method(s)</option>');
                }
            });

            window.scrollBy(0, 100);
            jQuery('#add-new').hide();
            jQuery('#add-new-ach').hide();
            jQuery('#add-new-card-update').hide();
            jQuery('#add-new-card-saved').show();
            jQuery('#ebiz_option_payment').val('saved');
        } else if (this.value == 'update' && paymenOption == 'credit_card') {
            
            jQuery('#payment_avs_street_update').val('');
            jQuery('#payment_avs_zip_update').val('');
            getMethodProfile();
            window.scrollBy(0, 100);
            jQuery('#add-new').hide();
            jQuery('#add-new-ach').hide();
            jQuery('#add-new-card-saved').show();
            jQuery('#add-new-card-update').show();
            jQuery('#ebiz_option_payment').val('update');
        } else if (this.value == 'new' && paymenOption == 'ACH') {
            jQuery('#add-new-card-update').hide();
            window.scrollBy(0, 100);
            jQuery('#add-new-ach').show();
            jQuery('#add-new').hide();
            jQuery('#add-new-card-saved').hide();
            jQuery('#ebiz_option_payment').val('new');
        } else if (this.value == 'saved' && paymenOption == 'ACH') {
            jQuery('#add-new-card-update').hide();
            var customerid = jQuery('#selectdivCustomer').val();

            var data = {
                'action': 'get_customs_paymentmethods',
                'customerId': customerid,
                'paymentType': jQuery('#ebiz_option').val(),
            };

            jQuery.post(ajaxurl, data, function (response) {
                if (response != "") {
                    jQuery('#selectdivPayment').empty();
                    jQuery('#selectdivPayment').html(response);
                    getDropdownSelected();
                } else {
                    jQuery('#selectdivPayment').html('<option value="">No Saved Method(s)</option>');
                }
            });

            window.scrollBy(0, 100);
            jQuery('#add-new-ach').hide();
            jQuery('#add-new').hide();
            jQuery('#add-new-card-saved').show();
            jQuery('#ebiz_option_payment').val('saved');
        }
    });

    jQuery('.ebiz_option_ach').on('click ready change', function () {
        
        var paymenOption = jQuery('#ebiz_option').val();

        if (this.value == 'new') {
            window.scrollBy(0, 100);
            jQuery('#add-new').show();
            jQuery('#add-new-card-saved').hide();
            jQuery('#add-new-ach').hide();
            jQuery('#ebiz_option_payment').val('new');

        } else if (this.value == 'saved') {
            window.scrollBy(0, 100);
            jQuery('#add-new').hide();
            jQuery('#add-new-card-saved').show();
            jQuery('#ebiz_option_payment').val('saved');
        }
    });

    jQuery('#payment_cc_number_new').change(function () {

        jQuery('#payment_cc_type_new option:not(:selected)').prop('disabled', false);

        let cc = jQuery(this).val();
        cc = cc.replaceAll("\\D", "");
        if (!cc || !cc.length) return undefined;
        let ccType = '';
        let firstNumber = cc.charAt(0);
        if (firstNumber == '4') ccType = 'Visa';
        if (firstNumber == '5') ccType = 'MasterCard';
        if (firstNumber == '3') ccType = 'American Express';
        if (firstNumber == '6') ccType = 'Discover';

        jQuery('#payment_cc_type_new').val(ccType);
        if (ccType != '') {
            jQuery('#payment_cc_type_new option:not(:selected)').prop('disabled', true);
        }
    });
});

jQuery(document).ready(function ($) {
    $(".search-custom-class").select2();
});


jQuery(document).ready(function () {
    jQuery('#wpwm_popClose').click(function () {
        jQuery('#wpwm_hideBody').css('display', 'none');
    });
});

function goBack() {
    window.history.back();
}

function validateMonthYear(newDate, oldDate) {
    var m1 = newDate.split("/");
    var m2 = oldDate.split("/");
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

function expiryDateValidation(idMonth, idYear) {
    let expiryMonth = jQuery(idMonth).val();
    let expiryYear = jQuery(idYear).val();
    let expiryMonthYear = expiryMonth + "/" + expiryYear;

    let currentDate = new Date();
    let currentMonth = currentDate.getMonth() + 1;
    if (currentMonth < 10) {
        currentMonth = "0" + currentMonth;
    }
    let currentYear = currentDate.getFullYear();
    let currentMonthYear = currentMonth + "/" + currentYear;
    let validDate = validateMonthYear(expiryMonthYear, currentMonthYear); // -1 , 0 , 1
    return validDate;
}

function getSavedMethodsByCustomer() {

    var data = {
        'action': 'get_customs_paymentmethods',
        'customerId': jQuery('#selectdivCustomer').val(),
        'paymentType': jQuery('#ebiz_option').val(),
    };

    jQuery.post(ajaxurl, data, function (response) {
        if (response != "") {
            jQuery('#selectdivPayment').empty();
            jQuery('#selectdivPayment').html(response);
            getDropdownSelected();

            if (jQuery('#ebiz_option_payment').val() == 'saved') {
                jQuery('#payment_method_name').val(jQuery('#selectdivPayment option:selected').text());
            } else {
                jQuery('#payment_method_name').val('');
            }

        } else {
            jQuery('#selectdivPayment').html('<option value="">No Saved Method(s)</option>');
        }
    });

}

function getMethodProfile() {
    var customerid = jQuery('#selectdivCustomer').val();
    var selectdivPayment = jQuery('#selectdivPayment').val();

    var data = {
        'action': 'get_customers_paymentmethodprofile',
        'customerId': customerid,
        'paymentMethodId': selectdivPayment
    };

    jQuery.post(ajaxurl, data, function (response) {
        if (response.success) {
            if (jQuery('#ebiz_option_payment').val() == 'update') {
                getDropdownSelected();
                jQuery('.avs_street').val(response.avs_street);
                jQuery('.avs_zip').val(response.avs_zip);
            } else {
                jQuery('.avs_street').val('');
                jQuery('.avs_zip').val('');
            }
        } else {
            jQuery('.avs_street').val('');
            jQuery('.avs_zip').val('');
        }

    }, 'json')
        .fail(function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        });
}

function getDropdownSelected() {

    jQuery("#selectdivPayment option").each(function () {
        if (jQuery(this).val() == jQuery('#eb_rec_method_id').val()) {
            jQuery(this).attr('selected', 'selected');
        }
    });

    jQuery('#payment_method_name').val(jQuery('#selectdivPayment option:selected').text());
}

function resubInvalidDates() {
    var frequency = jQuery("#freqId").val();
    var today = new Date().toISOString().slice(0, 10);
    var endDate = jQuery("#expire_date").val();

    var frequencyDays = getDaysFromFrequency(frequency);

    const diffInMs = new Date(endDate) - new Date(today)
    const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

    return Math.round(diffInDays) > Math.round(frequencyDays);
}

function compareStartDate() {
    var today = new Date().toISOString().slice(0, 10);
    var startDate = jQuery("#start_date").val();
    var todayDate = new Date();
    var compareDate = new Date(startDate);

    if (compareDate >= todayDate) {
        // ok
    } else {
        jQuery.alert({
            title: 'Alert!',
            boxWidth: '400px',
            useBootstrap: false,
            content: 'Start date must be equal to or greater than today.',
        });
        jQuery('#start_date').val(today);
        Event.preventDefault();
    }
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

function checkRecDate() {
    var data = {
        'action': 'get_subscriptdate',
        'customerId': jQuery('#selectdivCustomer').val(),
        'product_id': jQuery('#selectdivProduct').val(),
        'start_date': jQuery('#start_date').val(),
        'rec_id': jQuery('#rec_id').val(),
    };

    jQuery.post(ajaxurl, data, function (response) {
        if (response != "") {
            if (response == 'P') {
                handleSubmit()

            } else {
                jQuery.alert({
                    title: 'Alert!',
                    boxWidth: '400px',
                    useBootstrap: false,
                    content: 'This product is already subscribed with this customer between the selected dates. Please choose a different date range or update the existing subscription.',
                });
            }

        }
    });
}

function handleSubmit() {
    if (jQuery('#verify_card_before_saving').val() == '1'
        && jQuery('#ebiz_option').val() == 'credit_card'
        && jQuery('#ebiz_option_payment').val() == 'new') {

        validate_card()
    } else {
        jQuery('#form-validate').submit();
    }
}

function validate_card() {
    jQuery('.ajax-loader').css('visibility', 'visible')

    var data = {
        'action': 'validate_card',
        'customer_id': jQuery('#selectdivCustomer').val(),
        'cc_owner': jQuery('#payment_cc_owner_new').val(),
        'cc': jQuery('#payment_cc_number_new').val(),
        'expiry': jQuery('#payment_exp_new').val() + '_' + jQuery('#payment_exp_yr_new').val().slice(-2),
        'cvv': jQuery('#payment_cc_cid_new').val(),
        'avs_street': jQuery('#payment_avs_street_new').val(),
        'avs_zip': jQuery('#payment_avs_zip_new').val(),
        'billing_address': jQuery('#addressBill').val(),
        'shipping_address': jQuery('#addressShip').val(),
    };

    jQuery.post(ajaxurl, data, function (response) {
        if (!response.success) {
            return jQuery.alert({
                title: 'Error',
                boxWidth: '400px',
                useBootstrap: false,
                content: response.data.error || 'Gateway error'
            });
        }

        let avs_status = response.data.avs_status,
            avs = response.data.avs_result;

        if (avs_status === 'matched') {
            jQuery('#form-validate').submit();

        } else if (avs_status === 'partially_matched') {

            jQuery.confirm({
                title: 'Security Mismatch',
                boxWidth: '400px',
                useBootstrap: false,
                content: response.data.message,
                buttons: {
                    'GO BACK': {btnClass: 'btn-light'},
                    'SAVE CARD ANYWAY': {
                        btnClass: 'btn-primary',
                        action: function () {
                            jQuery('#form-validate').submit();
                        }
                    }
                }
            });

        } else {
            // 0 or 2 Ys → decline
            jQuery.alert({
                title: 'Declined',
                boxWidth: '400px',
                useBootstrap: false,
                content: 'AVS failed: "' + avs + '". Transaction cannot proceed.'
            });
        }

    }, 'json').fail(function (_, status) {

        jQuery.alert({
            title: 'Error',
            boxWidth: '400px',
            useBootstrap: false,
            content: 'AJAX error: ' + status
        });

    }).always(function() {
        jQuery('.ajax-loader').css('visibility', 'hidden')
    });
}


jQuery(document).ready(function () {
    jQuery("#searchInput").on("keyup", function () {
        let userChoice = jQuery(this).val().toLowerCase();
        jQuery("#the-list tr").filter(function () {
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(userChoice) > -1)
        });
    });

    jQuery(".print_btn").click(function () {
        var tid = jQuery(this).attr('id');
        var rid = jQuery('#rid').val();
        var data = {
            'action': 'get_print', 'rid': rid, 'tid': tid,
        };
        jQuery.post(ajaxurl, data, function (response) {
            if (response != "") {
                if (response !== undefined) {
                    var newWin = window.open('', '_blank', 'width=500,height=500');
                    newWin.document.open();
                    newWin.document.write('<html><body onload="window.print()">' + response + '</html>');
                    newWin.print();
                    newWin.close();
                }
            } else {
                jQuery.alert({
                    title: 'Alert!',
                    boxWidth: '400px',
                    useBootstrap: false,
                    content: 'Error in print process.',
                });
            }
        });
    });

    jQuery(".email_btn").click(function () {
        var rid = jQuery('#rid').val();
        var tid = jQuery(this).attr('id');
        var email = jQuery(this).attr('data-id');

        var data = {
            'action': 'send_email', 'rid': rid, 'tid': tid, 'email': email,
        };
        jQuery.post(ajaxurl, data, function (response) {
            if (response != "") {
                if (response !== undefined) {
                    if (response == 1) {

                        jQuery('#success').html("<p>Email successfully sent.</p>");
                        jQuery('#success').show();
                    } else {
                        jQuery('#error').html("<p>Email not sent!</p>");
                        jQuery('#error').show();
                    }
                }
            } else {
                jQuery.alert({
                    title: 'Alert!',
                    boxWidth: '400px',
                    useBootstrap: false,
                    content: 'Error in sending email.',
                });
            }
        });
    });

    function exportTable(table_id, separator = ',', filename) {
        //alert("apa");
        var rows = document.querySelectorAll('table.' + table_id + ' tr');

        var csv = [];
        for (var i = 0; i < rows.length; i++) {
            var row = [];
            var cols = rows[i].querySelectorAll('td:not(:first-child), th:not(:first-child)');
            for (var j = 0; j < cols.length - 1; j++) {
                // Clean innertext to remove multiple spaces and jumpline (break csv)
                var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ')
                data = data.replace(/[↕]/g, '');
                data = data.replace(/"/g, '""');
                // Push escaped string
                row.push('"' + data + '"');
            }
            csv.push(row.join(separator));
        }
        var csv_string = csv.join('\n');
        // Download it
        //var filename = 'export_' + table_id + '_' + new Date().toLocaleDateString() + '.csv';
        var link = document.createElement('a');
        link.style.display = 'none';
        link.setAttribute('target', '_blank');
        link.setAttribute('href', 'data:application/csv;charset=UTF-8,%EF%BB%BF' + encodeURIComponent(csv_string));
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    jQuery(document).ready(function () {
        jQuery(".export_btn").click(function () {
            var current_date = jQuery('#file_name').val();
            var filename = 'Subscriptions_payment_history_' + current_date + '.csv';
            exportTable('subscriptions_page_payment-history', ',', filename);
        });
        var frm = jQuery('form[method="post"]');
        jQuery(".notice-dismiss").click(function () {
            var pageURL = jQuery(location).attr("href");
            var cleanUri = pageURL.substring(0, pageURL.indexOf("&"));
            window.location = cleanUri;
            jQuery("#setting-error-settings_updated").hide();
        });
    });

    jQuery(document).ready(function () {
        jQuery("#loader").show();
        var page = jQuery('#page_action').val();
        if (page == 'create_order') {
            jQuery("form").on("submit", function () {
                jQuery(".loader").show();
            });
        }
    });

    jQuery('.numberonly').keypress(function (e) {
        var charCode = (e.which) ? e.which : event.keyCode
        if (String.fromCharCode(charCode).match(/[^0-9]/g)) {
            return false;
        }
    });

});
