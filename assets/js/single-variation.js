/**
 * Auto-select single variation
 */
jQuery(function($) {
    'use strict';

    // Only run if we have the variation data
    if (typeof simple_woo_events_single_variation === 'undefined') {
        return;
    }

    // Function to hide elements for single variation products
    function hideElementsForSingleVariation() {
        // Hide the variations table
        $('table.variations').hide();
        
        // Hide any alert messages (including "Sorry, this product is unavailable")
        $('p[role="alert"]').hide();
    }

    $(document).ready(function() {
        // Get the form
        var $form = $('form.variations_form');
        
        if ($form.length === 0) {
            return;
        }

        // Wait for the variation form to be initialized
        $form.on('wc_variation_form', function() {
            // Get the variation data
            var variationId = simple_woo_events_single_variation.variation_id;
            var attributes = simple_woo_events_single_variation.attributes;
            
            // Make sure we have valid data
            if (!variationId || !attributes) {
                return;
            }
            
            // Hide elements immediately
            hideElementsForSingleVariation();
            
            // Set the variation ID
            $form.find('input[name="variation_id"]').val(variationId);
            
            // Set attribute values for hidden inputs
            $.each(attributes, function(name, value) {
                if (value && value !== '') {
                    var $attributeField = $form.find('[name="' + name + '"]');
                    if ($attributeField.length) {
                        $attributeField.val(value).trigger('change');
                    }
                }
            });
            
            // Short delay to ensure all attributes are set
            setTimeout(function() {
                // Trigger the found_variation event
                $form.trigger('found_variation', [
                    {
                        variation_id: variationId,
                        attributes: attributes
                    }
                ]);
                
                // Add a class to the form to indicate it's a single variation
                $form.addClass('single-variation-auto-selected');
                
                // Show the add to cart button
                $form.find('.single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed');
                
                // Hide elements again after variation is found
                hideElementsForSingleVariation();
            }, 100);
        });
        
        // Also handle when WooCommerce might recheck availability
        $form.on('check_variations update_variation_values found_variation hide_variation show_variation', function() {
            hideElementsForSingleVariation();
        });
        
        // Run on a timer to ensure elements are hidden even if dynamically added
        setInterval(hideElementsForSingleVariation, 250);
    });
});
