/*! 
jQuery Ebizcharge Validate Forms v1.0 
| (c) CBS Foundation and other contributors
| CBS/license 
*/
jQuery(document).ready(function ($) {

	$('form[id="method-card"]').validate(validateCardFormAdd());
	$('form[id="method-bank"]').validate(validateAchForm());

	function validateCardFormAdd() {
		return {
			rules: {
				ccholder: {required: true},
				ccnum: {required: true, number: true, minlength: 15, maxlength: 16},
				cardtype: {required: true},
				expmonth: {required: true},
				expyear: {required: true},
				cvv: {
					required: true,
					number: true,
					minlength: function () {
						var cardtype = $("#cardtype").val();
						if (cardtype === "American Express") {
							return 4;
						} else {
							return 3;
						}
					},
					maxlength: function () {
						var cardtype = $("#cardtype").val();
						if (cardtype === "American Express") {
							return 4;
						} else {
							return 3;
						}
					}
				},
				avs_street: {required: true},
				avs_zip: {required: true, number: true}

			},
			messages: {
				ccholder: 'Name on Card is required',
				ccnum: 'Credit Card# required, 15-16 digits',
				cardtype: 'Credit Card Type is required',
				expmonth: 'Expiration Month is required',
				expyear: 'Expiration Year is required',
				cvv: {
					required: 'CVV is required, 3-4 digits',
					number: '3-4 digits only',
					minlength: function () {
						var cardtype = $("#cardtype").val();
						if (cardtype === "American Express") {
							return 'CVV is required 4 digits';
						} else {
							return 'CVV is required 3 digits';
						}
					},
					maxlength: function () {
						var cardtype = $("#cardtype").val();
						if (cardtype === "American Express") {
							return 'CVV is required 4 digits';
						} else {
							return 'CVV is required 3 digits';
						}
					}
				},
				avs_street: 'Billing Address is required',
				avs_zip: 'Zip Code is required, digits only'
			}
		};
	}

	function validateAchForm() {
		return {
			rules: {
				acholder: {required: true},
				actype: {required: true},
				acnum: {required: true, number: true, minlength: 5, maxlength: 17},
				routingnum: {required: true, number: true, minlength: 9, maxlength: 9}
			},
			messages: {
				acholder: 'Account Holder is required',
				actype: 'Account Type is required',
				acnum: 'Account Number is required, 5-17 digits',
				routingnum: 'Routing Number is required, 9 digits only'
			}
		};
	}

	// for checking valid expiry dates
	$('#method-card').submit(function (e) {
		var validity = expiryDateValidation('#expmonth', '#expyear');
		var expiryMonth = $("#expmonth").val();
		let expmonth = true;

		if (expiryMonth !== '' && validity < 0) {
			$("#expmonth").focus();
			$("#expmonth-error").show();
			$('#expmonth-error').html("Expiration Month is invalid");
			expmonth = false;
		}
		return expmonth;
	});

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

	function expiryDateValidation(idMonth, idYear) {
		let expiryMonth = $(idMonth).val();
		let expiryYear = $(idYear).val();
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

	$("input[id^='save-button-']").click(function () {
		let expiry_date = true;
		let avs_address = true;
		let zip_code = true;

		var method_number = $(this).attr('id').split('-').pop();
		var expiryDate = jQuery("#edit-cc-exp-" + method_number).val();
		var avsAddress = jQuery("#edit-cc-address-" + method_number).val();
		var zipCode = jQuery("#edit-cc-zip-" + method_number).val();

		let currentDate = new Date();
		let currentMonth = currentDate.getMonth() + 1;
		if (currentMonth < 10) {
			currentMonth = "0" + currentMonth;
		}
		let currentYear = currentDate.getFullYear();
		let currentMonthYear = currentMonth + "/" + currentYear;

		var validity = validateMonthYear(expiryDate, currentMonthYear);

		if (expiryDate === '') {
			jQuery("#edit-cc-exp-" + method_number).focus();
			jQuery("#expiration-error-" + method_number).show();
			jQuery('#expiration-error-' + method_number).html("Expiration Date is required");
			expiry_date = false;
		} else if (validity < 0) {
			jQuery("#edit-cc-exp-" + method_number).focus();
			jQuery("#expiration-error-" + method_number).show();
			jQuery('#expiration-error-' + method_number).html("Expiration Date is invalid");
			expiry_date = false;
		} else {
			jQuery("#expiration-error-" + method_number).hide();
			jQuery('#expiration-error-' + method_number).html("");
		}

		if (avsAddress === '') {
			jQuery("#edit-cc-address-" + method_number).focus();
			jQuery("#avs-error-" + method_number).show();
			jQuery('#avs-error-' + method_number).html("Billing Street is required");
			avs_address = false;
		} else {
			jQuery("#avs-error-" + method_number).hide();
			jQuery("#avs-error-" + method_number).html("");
		}

		if (zipCode === '') {
			jQuery("#edit-cc-zip-" + method_number).focus();
			jQuery("#zip-error-" + method_number).show();
			jQuery('#zip-error-' + method_number).html("Zip/Postal Code is required");
			zip_code = false;
		} else {
			jQuery("#zip-error-" + method_number).hide();
			jQuery("#zip-error-" + method_number).html("");
		}

		return expiry_date && avs_address && zip_code;
	});

	$("input[id^='edit-cc-exp-']").keyup(function (e) {
		var method_number = $(this).attr('id').split('-').pop();

		$("#edit-cc-exp-" + method_number).attr('maxlength', '7');
		var key = e.keyCode || e.charCode;
		if (key != 8) {
			var curr_val = $(this).val();
			var expiry_date = curr_val.replace(/^(\d\d)(\d*)?\//g, '$1/$2').replace(/^(\d\d)(\d*)?$/g, '$1/$2').replace(/[^\d\/]/g, '');
			$("#edit-cc-exp-" + method_number).val(expiry_date);
		}
	});

	$("button[id^='cancel-button-']").click(function () {
		var method_number = $(this).attr('id').split('-').pop();
		jQuery("#expiration-error-" + method_number).hide();
		jQuery('#expiration-error-' + method_number).html("");
		jQuery("#avs-error-" + method_number).hide();
		jQuery("#avs-error-" + method_number).html("");
		jQuery("#zip-error-" + method_number).hide();
		jQuery("#zip-error-" + method_number).html("");
	});

});