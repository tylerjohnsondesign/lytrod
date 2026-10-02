jQuery(document).ready(function($) {
    jQuery('.upgrade-link').on('click', function(e) {
        e.preventDefault();
		var $this = jQuery(this);
        var subscriptionId = jQuery(this).data('subscription-id');
		var confirmation = confirm("Do you want to upgrade your subscription?");
        if (confirmation) {
			jQuery.ajax({
				url: myPluginData.ajax_url,
				type: 'POST',
				data: {
					action: 'duplicate_subscription',
					subscription_id: subscriptionId
				},
				success: function(response) {
					if (response.success) {
						location.reload(); // Reload the page to reflect changes
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
				}
			});
		}
    });
	var baseCheckoutUrl = myPluginData.site_url+'/checkout';
	jQuery('.variation-select-option').on('change', function() {
        var variationId = $(this).val();
        var product_id = $(this).data('prod-id');
        var subscription_id = $(this).data('subscription-id');
        var nonce = $(this).data('nonce');
        var item = $(this).data('item-id');
        var addToCartLink = $(this).parent().parent().find('.add-to-cart-custom');
        if(variationId) {
			jQuery('.error-message').remove();
		}
		if (addToCartLink.length) {
            var completeUrl = baseCheckoutUrl + '?add-to-cart=' + product_id + '&variation_id='+variationId+'&switch-subscription='+subscription_id+'&item='+item+'&_wcsnonce='+nonce+'&redirect_to_checkout=true';
            addToCartLink.attr('href', completeUrl);
        }
    });

	$('.add-to-cart-custom').on('click', function(e) {
        var $link = $(this);
        var variationSelect = $('.variation-select-option');
        var variationId = variationSelect.val();

        // Check if the selected variation is empty
        if (!variationId) {
            e.preventDefault(); // Prevent the default action
            var errorMessage = $('<div class="error-message" style="color:red;">Please select a Option</div>');

            // Remove any existing error messages
            variationSelect.next('.error-message').remove();
            // Append the error message below the dropdown
            variationSelect.after(errorMessage);
            return; // Exit the function
        }
        // Redirect to checkout
        window.location.href = $link.attr('href');
    });
});



