(function ($) {
    'use strict';
    $(document).ready(function () {
        function velo_strip_last_level(level) {
            const lastUnderscoreIndex = level.lastIndexOf("_");
            if (lastUnderscoreIndex === -1) return false; // if there are no underscores
            const newLvl = level.substring(0, lastUnderscoreIndex); // remove last part
            const secondLastUnderscoreIndex = newLvl.lastIndexOf("_");
            if (secondLastUnderscoreIndex === -1) return false; // if there is only one underscore
            const finalLvl = newLvl.substring(0, secondLastUnderscoreIndex); // remove second last part
            return finalLvl;
        }

        function velo_get_value_from_object(object, keysString) {
            let keys = keysString.split('_');
            let current = object;

            $.each(keys, function (index, key_value) {
                console.log(key_value);
                if (current[key_value] !== undefined) {
                    current = current[key_value];
                } else {
                    throw new Error(`Key "${key_value}" not found in the object.`);
                }
            });

            return current;
        }

        function velo_set_html_data_for_element(element, input_data, level = '0') {
            element.html(''); // Clear element

            // Get value from object based on the level
            const data = velo_get_value_from_object(input_data, level);

            // Check if we got some data back
            if (data) {
                // Add back button if level is not zero. Also check if there is a back level
                if (level !== '0' && velo_strip_last_level(level) !== false) {
                    element.html('<button class="velo-frontend-back-button" data-level="' + velo_strip_last_level(level) + '">' + velo_product_selector.velo_back_text + '</button>');
                }

                // Check if there is a text
                if (data["text"]) {
                    element.append('<h2>' + data["text"] + '</h2>');
                }

                // Just append 'velo-choices-wrapper' to the element
                element.append('<div class="velo-choices-wrapper"></div>');

                // Check if there are any nested elements
                if (data["nestedData"]) {
                    const new_level = level + "_nestedData";
                    $.each(data["nestedData"], function (index, nestedItem) {
                        if (nestedItem["type"]) {
                            const content = nestedItem["answer"];
                            if (nestedItem["type"] === 'final-redirect') {
                                element.find('.velo-choices-wrapper').append('<a href="' + nestedItem["text"] + '" target="_blank" class="velo-inner-choice final-redirect" data-level="' + new_level + '_' + index + '">' + content + '</a>');
                            } else if (nestedItem["type"] === 'final-value') {
                                element.find('.velo-choices-wrapper').append('<div class="velo-inner-choice final-value" data-value="' + nestedItem["text"] + '" data-level="' + new_level + '_' + index + '">' + content + '</div>');
                            } else {
                                element.find('.velo-choices-wrapper').append('<div class="velo-inner-choice" data-level="' + new_level + '_' + index + '">' + content + '</div>');
                            }

                        }
                    });
                }
            } else {
                element.html('No data found.');
            }
        }

        // Set data with AJAX for final value items
        function velo_set_html_data_for_element_final_value(element, item_value, level = '0') {
            // Start with a loading screen
            element.html('<div class="velo-loading"><div></div><div></div><div></div></div>');

            // Get 'product selector' select with AJAX based on the item value
            $.ajax({
                url: velo_product_selector.ajax_url,
                type: 'POST',
                data: {
                    action: 'velo_get_html_data_for_final_item',
                    nonce: velo_product_selector.velo_frontend_ajax_nonce,
                    item_value: item_value,
                },
                success: function (response) {
                    if (response.data && response.data.data) {
                        // Add back button if level is not zero. Also check if there is a back level
                        if (level !== '0' && velo_strip_last_level(level) !== false) {
                            element.html('<button class="velo-frontend-back-button" data-level="' + velo_strip_last_level(level) + '">' + velo_product_selector.velo_back_text + '</button>');
                        }

                        // Set HTML for the velo element
                        element.append(response.data.data);
                    } else {
                        element.html('No data found.');
                    }
                },
                error: function (xhr, status, error) {
                    const response_decoded = JSON.parse(xhr.responseText);
                    if (response_decoded.data && response_decoded.data.data) {
                        element.html(response_decoded.data.data);
                    } else {
                        element.html('No data found.');
                    }
                    console.log('VELO AJAX Error:');
                    console.log(xhr.responseText);
                    console.log(status);
                    console.log(error);
                }
            });
        }

        // On click of choice item
        $('body').on('click', '.velo-inner-choice, button.velo-frontend-back-button', function () {
            const velo_element = $(this);
            const velo_parent = velo_element.closest('.velo-wrapper');
            const velo_level = velo_element.attr('data-level');
            const item_value = velo_element.attr('data-value');

            // Get data from the windows variable
            const velo_data = window["velo_selector_" + velo_parent.attr('data-id')];

            // Check if this an final item
            if (velo_element.hasClass('final-redirect')) {
                // Do notting for redirects
            } else if (velo_element.hasClass('final-value')) {
                // AJAX call to get the right new data [TODO]
                velo_set_html_data_for_element_final_value(velo_parent, item_value, velo_level);
            } else {
                // Set HTML for the velo element
                velo_set_html_data_for_element(velo_parent, velo_data, velo_level);
            }
        });

        $('.velo-wrapper').each(function () {
            const velo_element = $(this);
            if (velo_element) {
                const velo_id = velo_element.attr('data-id');
                // Get 'product selector' select with AJAX
                $.ajax({
                    url: velo_product_selector.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'velo_get_product_selector_data',
                        nonce: velo_product_selector.velo_frontend_ajax_nonce,
                        selector_id: velo_id,
                    },
                    success: function (response) {
                        if (response.data && response.data.data) {
                            // Save data in the windows variable
                            window["velo_selector_" + velo_id] = response.data.data;

                            // Set HTML for the velo element
                            velo_set_html_data_for_element(velo_element, window["velo_selector_" + velo_id], '0');
                        } else {
                            velo_element.html('No data found.');
                        }
                    },
                    error: function (xhr, status, error) {
                        const response_decoded = JSON.parse(xhr.responseText);
                        if (response_decoded.data && response_decoded.data.data) {
                            velo_element.html(response_decoded.data.data);
                        } else {
                            velo_element.html('No data found.');
                        }
                        console.log('VELO AJAX Error:');
                        console.log(xhr.responseText);
                        console.log(status);
                        console.log(error);
                    }
                });
            }
        });
    })
})(jQuery);
