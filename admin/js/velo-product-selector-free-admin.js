(function ($) {
    'use strict';

    $(document).ready(function () {
        // Check if we got the 'velo-product-selector-select' div on the admin settings page, then automatically load the 'product selector' select
        if ($('.main-settings-wrap .velo-product-selector-select').length) {
            // Loading bar
            $('.velo-product-selector-select').html('<progress class="uk-progress velo-product-selector-select-progressbar" value="10" max="100"></progress>');
            velo_start_progress_bar($('body').find('.velo-product-selector-select-progressbar'));

            // Get 'product selector' select with AJAX
            $.ajax({
                url: velo_product_selector.ajax_url,
                type: 'POST',
                data: {
                    action: 'velo_product_selector_select_and_create',
                    nonce: velo_product_selector.ajax_settings_nonce,
                },
                success: function (response) {
                    if (response.data && response.data.html) {
                        // Smooth show all data
                        const element_to_fill = $('body').find('.velo-product-selector-select');
                        const html = response.data.html;
                        const bar_element = $('body').find('.velo-product-selector-select-progressbar');
                        velo_smooth_show(element_to_fill, html, bar_element)
                            .then(() => {
                                console.log('Element ready.');
                            })
                            .catch((error) => {
                                console.log('Something went wrong with the smooth transition.');
                                console.error(error);
                            });
                    } else {
                        $('.velo-product-selector-select').html(response.data);
                    }
                },
                error: function (xhr, status, error) {
                    const response_decoded = JSON.parse(xhr.responseText);
                    if (response_decoded.data && response_decoded.data) {
                        $('.velo-product-selector-select').html(response_decoded.data);
                    } else {
                        $('.velo-product-selector-select').text('Ajax call failed.');
                    }
                    console.log(status);
                    console.log(error);
                }
            });
        }

        // Create product selector button pop-up
        $('body').on('click', '.create-product-selector-pup-up', function (event) {
            event.preventDefault();
            event.target.blur();

            // Close all open dialogs first
            if ($('body').find('.uk-modal').length > 0) {
                $('body').find('.uk-modal').each(function () {
                    UIkit.modal($(this)).hide();
                });
            }

            // Open new dialog
            UIkit.modal.dialog('<p class="uk-modal-body velo-create-selector-modal"></p>');

            // Loading bar
            $('body').find('.velo-create-selector-modal').html('<progress class="uk-progress velo-create-selector-modal-progressbar" value="10" max="100"></progress>');
            velo_start_progress_bar($('body').find('.velo-create-selector-modal-progressbar'));

            // Create a 'product selector' with AJAX
            $.ajax({
                url: velo_product_selector.ajax_url,
                type: 'POST',
                data: {
                    action: 'velo_get_form_to_create_selector',
                    nonce: velo_product_selector.ajax_settings_nonce,
                },
                success: function (response) {
                    if (response.data && response.data.html) {
                        // Smooth show all data
                        const element_to_fill = $('body').find('.velo-create-selector-modal');
                        const html = response.data.html;
                        const bar_element = $('body').find('.velo-create-selector-modal-progressbar');
                        velo_smooth_show(element_to_fill, html, bar_element)
                            .then(() => {
                                console.log('Element ready.');
                            })
                            .catch((error) => {
                                console.log('Something went wrong with the smooth transition.');
                                console.error(error);
                            });
                    } else {
                        $('body').find('.velo-create-selector-modal').html(response.data);
                    }
                },
                error: function (xhr, status, error) {
                    const response_decoded = JSON.parse(xhr.responseText);
                    if (response_decoded.data && response_decoded.data) {
                        $('body').find('.velo-create-selector-modal').html(response_decoded.data);
                    } else {
                        $('body').find('.velo-create-selector-modal').text('Ajax call failed.');
                    }
                    console.log(status);
                    console.log(error);
                }
            });
        });

        // Do create the product selector button click
        $('body').on('click', '.create-product-selector', function (event) {
            const selector_name = $('body').find('#velo-selector-name').val();

            if (selector_name !== '') {
                $.ajax({
                    url: velo_product_selector.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'velo_create_selector',
                        nonce: velo_product_selector.ajax_settings_nonce,
                        name: selector_name,
                    },
                    success: function (response) {
                        if (response.data && response.data.html) {
                            // Smooth show all data
                            const element_to_fill = $('body').find('.velo-create-selector-modal');
                            const html = response.data.html;
                            const bar_element = $('body').find('.velo-create-selector-modal-progressbar');
                            velo_smooth_show(element_to_fill, html, bar_element)
                                .then(() => {
                                    console.log('Element ready.');
                                })
                                .catch((error) => {
                                    console.log('Something went wrong with the smooth transition.');
                                    console.error(error);
                                });

                            // Trigger refresh
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else {
                            $('body').find('.velo-create-selector-modal').html(response.data);
                        }
                    },
                    error: function (xhr, status, error) {
                        const response_decoded = JSON.parse(xhr.responseText);
                        if (response_decoded.data && response_decoded.data) {
                            $('body').find('.velo-create-selector-modal').html(response_decoded.data);
                        } else {
                            $('body').find('.velo-create-selector-modal').text('Ajax call failed.');
                        }
                        console.log(status);
                        console.log(error);
                    }
                });
            } else {
                if (selector_name === '') {
                    $('body').find('#velo-selector-name').addClass('uk-form-danger');
                }

                $('body').find('.velo-create-selector-modal').prepend('<div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p>Not all mandatory fields have been completed. Please check your fields and try again.</p></div>');
            }
        });

        // Select product selector to edit
        $('body').on('click', '.edit-single-product-selector', function (event) {
            const selected_product_selector_id = $('body').find('.velo-product-selector-select select').val();

            // Loading bar
            $('body').find('.velo-selector-editor').html('<progress class="uk-progress velo-selector-editor-progressbar uk-margin-top" value="10" max="100"></progress>');
            velo_start_progress_bar($('body').find('.velo-selector-editor-progressbar'));

            if (selected_product_selector_id !== '') {
                $.ajax({
                    url: velo_product_selector.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'velo_get_single_product_selector_editor',
                        nonce: velo_product_selector.ajax_settings_nonce,
                        product_selector_id: selected_product_selector_id
                    },
                    success: function (response) {
                        if (response.data && response.data.html) {
                            // Smooth show all data
                            const element_to_fill = $('body').find('.velo-selector-editor');
                            const html = response.data.html;
                            const bar_element = $('body').find('.velo-selector-editor-progressbar');
                            velo_smooth_show(element_to_fill, html, bar_element)
                                .then(() => {
                                    console.log('Element ready.');

                                    // Setup sortable
                                    $('body').find('#velo-sortable-list').velosortable(
                                        {
                                            group: 'nested',
                                            animation: 150,
                                            fallbackOnBody: true,
                                            swapThreshold: 0.65
                                        }
                                    );

                                    // Setup nested sortable for all nested sortable elements
                                    $('body').find('.velo-nested-sortable').each(function () {
                                        $(this).velosortable(
                                            {
                                                group: 'nested',
                                                animation: 150,
                                                fallbackOnBody: true,
                                                swapThreshold: 0.65
                                            }
                                        );
                                    });

                                })
                                .catch((error) => {
                                    console.log('Something went wrong with the smooth transition.');
                                    console.error(error);
                                });
                        } else {
                            $('body').find('.velo-selector-editor').html(response.data);
                        }
                    },
                    error: function (xhr, status, error) {
                        const response_decoded = JSON.parse(xhr.responseText);
                        if (response_decoded.data && response_decoded.data) {
                            $('body').find('.velo-selector-editor').html(response_decoded.data);
                        } else {
                            $('body').find('.velo-selector-editor').text('Ajax call failed.');
                        }
                        console.log(status);
                        console.log(error);
                    }
                });
            }
        });

        // Save edited product selector
        $('body').on('click', '.velo-save-edited-product-selector', function (event) {

            // Get sortable list
            let sortableList = $('body').find('#velo-sortable-list');

            if (sortableList.length > 0) {
                // Get all data from sortable list
                const sortableID = sortableList.attr('data-id');
                const json_sortableList = velo_save_nested_sortable_data(sortableList);

                if (json_sortableList !== '' && json_sortableList.length > 0) {
                    // Loading spinner
                    $('body').find('.velo-save-edited-product-selector').append('<div uk-spinner></div>');

                    $.ajax({
                        url: velo_product_selector.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'velo_save_edited_product_selector',
                            nonce: velo_product_selector.ajax_settings_nonce,
                            json_data: json_sortableList,
                            product_selector_id: sortableID
                        },
                        success: function (response) {
                            if (response.data) {
                                // Smooth show all data
                                $('body').find('.velo-save-edited-product-selector .uk-spinner').remove();
                                showSuccessNotification('Product selector saved successfully.');
                            } else {
                                $('body').find('.velo-save-edited-product-selector .uk-spinner').remove();
                                alert('Something went wrong. Please try again.');
                            }
                        },
                        error: function (xhr, status, error) {
                            $('body').find('.velo-save-edited-product-selector .uk-spinner').remove();
                            alert('Something went wrong. Please try again.');
                            console.log(xhr);
                            console.log(status);
                            console.log(error);
                        }
                    });
                } else {
                    // Close all open dialogs first
                    if ($('body').find('.uk-modal').length > 0) {
                        $('body').find('.uk-modal').each(function () {
                            UIkit.modal($(this)).hide();
                        });
                    }

                    // Open new dialog
                    UIkit.modal.dialog('<p class="uk-modal-body velo-create-selector-modal">Product selector is empty. You have to add at least one item to save the product selector.<br><button class="uk-button uk-button-default uk-modal-close">Ok</button></p>');
                }
            }
        });

        // Dalete a poroduct selector!
        $('body').on('click', '.velo-delete-edited-product-selector', function (event) {
            // Find modal
            if ($('body').find('#confirmation-full-product-selector-remove-modal').length > 0) {
                $('body').find('#confirmation-full-product-selector-remove-modal').each(function () {
                    UIkit.modal($(this)).show();
                });
            }

            // Click on the confirmation button
            $('body').on('click', '#confirm-full-product-selector-remove-btn', function () {
                // Get sortable list
                let sortableList = $('body').find('#velo-sortable-list');

                if (sortableList.length > 0) {
                    // Get the ID of the product selector
                    const sortableID = sortableList.attr('data-id');

                    // Loading spinner
                    $('body').find('.velo-delete-edited-product-selector').append('<div uk-spinner></div>');

                    $.ajax({
                        url: velo_product_selector.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'velo_delete_product_selector',
                            nonce: velo_product_selector.ajax_settings_nonce,
                            product_selector_id: sortableID
                        },
                        success: function (response) {
                            if (response.data) {
                                $('body').find('.velo-delete-edited-product-selector .uk-spinner').remove();
                            } else {
                                $('body').find('.velo-delete-edited-product-selector .uk-spinner').remove();
                                alert('Something went wrong. Please try again.');
                            }

                            // Trigger refresh
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        },
                        error: function (xhr, status, error) {
                            $('body').find('.velo-delete-edited-product-selector .uk-spinner').remove();
                            alert('Something went wrong. Please try again.');
                            console.log(xhr);
                            console.log(status);
                            console.log(error);

                            // Trigger refresh
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        }
                    });
                }

                // Find modal and hide it
                if ($('body').find('#confirmation-full-product-selector-remove-modal').length > 0) {
                    $('body').find('#confirmation-full-product-selector-remove-modal').each(function () {
                        UIkit.modal($(this)).hide();
                    });
                }
            });
        });

        // Create question/question-answer functionality in the product selector editor
        $('body').on('click', '.create-question-button, .create-question-answer-button', function (event) {
            // Check if this about a new item or existing item
            let is_question_answer = false;
            if ($(this).hasClass('create-question-answer-button')) {
                is_question_answer = true;
            }

            if (is_question_answer === true) {
                const parent_data_id = $('body').find('#velo_element_data_id').val();
                const parentElement = $('body').find('.velo-nested-wrapper[data-velo-id="' + parent_data_id + '"]');

                const question_val = $('body').find('#velo-edit-text-field').val();
                const answer_val = $('body').find('#velo-edit-answer-field').val();
                if (question_val === '' || answer_val === '') {
                    // Add error class
                    $('body').find('#velo-edit-text-field').addClass('uk-form-danger');
                    $('body').find('#velo-edit-answer-field').addClass('uk-form-danger');
                } else {
                    // Remove error class
                    $('body').find('#velo-edit-text-field').removeClass('uk-form-danger');
                    $('body').find('#velo-edit-answer-field').removeClass('uk-form-danger');

                    // Reset question value field
                    $('body').find('#velo-edit-text-field').val('');
                    $('body').find('#velo-edit-answer-field').val('');

                    // Get sortable list
                    let sortableList = $('body').find('#velo-sortable-list');

                    // Remove placeholder element if exists
                    if ($('body').find('.placeholder-sortable-list').length > 0) {
                        $('body').find('.placeholder-sortable-list').remove();
                    }

                    // Get the image id
                    const image_id = $('body').find('#velo_image_id').val();

                    // Create new item
                    const newItem = $('<div class="velo-nested-wrapper" data-type="nested" data-image="' + image_id + '" data-title="' + question_val + '" data-answer="' + answer_val + '"><span class="item-answer"><strong>Answer:</strong> ' + answer_val + '</span> | <span class="item-title"><strong>Question:</strong> ' + question_val + '</span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span></div>');
                    const nestedSortable = $('<div class="velo-nested-sortable"></div>');
                    nestedSortable.velosortable(
                        {
                            group: 'nested',
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65
                        }
                    );

                    // Create new sortable inside the new item
                    newItem.append(nestedSortable);

                    // Add new item to the existing sortable element
                    parentElement.children('.velo-nested-sortable').first().append(newItem);
                    sortableList.velosortable('refresh');

                    // Show save button
                    $('body').find('.velo-save-edited-product-selector').removeClass('velo-display-none');

                    // Remove the create first question wrapper
                    $('body').find('.velo-create-first-question-wrapper').remove();

                    // Find modal and hide it
                    if ($('body').find('#editor-item-edit-modal').length > 0) {
                        $('body').find('#editor-item-edit-modal').each(function () {
                            UIkit.modal($(this)).hide();
                        });
                    }

                    // Prevent the default behavior of the event
                    event.preventDefault();
                }
            } else {
                const question_val = $('body').find('#velo-question-field').val();
                if (question_val === '') {
                    // Add error class
                    $('body').find('#velo-question-field').addClass('uk-form-danger');
                } else {
                    // Remove error class
                    $('body').find('#velo-question-field').removeClass('uk-form-danger');

                    // Reset question value field
                    $('body').find('#velo-question-field').val('');

                    // Get sortable list
                    let sortableList = $('body').find('#velo-sortable-list');

                    // Remove placeholder element if exists
                    if ($('body').find('.placeholder-sortable-list').length > 0) {
                        $('body').find('.placeholder-sortable-list').remove();
                    }

                    // Create sortable list if not exists
                    if (sortableList.length === 0) {
                        sortableList = $('<div id="velo-sortable-list" class="velo-sortable-list"></div>');
                        $('body').find('.velo-product-selector-select').append(sortableList);
                        sortableList.velosortable();
                    }

                    // Create new item
                    const newItem = $('<div class="velo-nested-wrapper" data-type="nested-question" data-image="" data-title="' + question_val + '" data-answer=""><span class="item-title"><strong>Question:</strong> ' + question_val + '</span> <span class="item-answer"></span> <span class="uk-icon-link velo-add-sub-item-product-editor" uk-icon="plus-circle"></span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-edit-item-product-editor" uk-icon="file-edit"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span></div>');
                    const nestedSortable = $('<div class="velo-nested-sortable"></div>');
                    nestedSortable.velosortable(
                        {
                            group: 'nested',
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65
                        }
                    );

                    // Create new sortable inside the new item
                    newItem.append(nestedSortable);

                    // Add new item to the existing sortable element
                    sortableList.append(newItem);
                    sortableList.velosortable('refresh');

                    // Show save button
                    $('body').find('.velo-save-edited-product-selector').removeClass('velo-display-none');

                    // Remove the create first question wrapper
                    $('body').find('.velo-create-first-question-wrapper').remove();

                    // Prevent the default behavior of the event
                    event.preventDefault();
                }
            }
        });

        // Create final item (product, post, page) functionality in the product selector editor
        $('body').on('click', '.create-velo-autocomplete-value-button', function (event) {
            const parent_data_id = $('body').find('#velo_element_data_id').val();
            const parentElement = $('body').find('.velo-nested-wrapper[data-velo-id="' + parent_data_id + '"]');

            // Get selected item
            const selected_item_val = $('body').find('#velo-autocomplete-search-field').val();

            // Get answer val
            const answer_val = $('body').find('#velo-autocomplete-answer-field').val();

            // Check if empty
            if (selected_item_val === '' || answer_val === '') {
                // Add error class
                if (selected_item_val === '') {
                    $('body').find('#velo-autocomplete-search-field').addClass('uk-form-danger');
                }
                if (answer_val === '') {
                    $('body').find('#velo-autocomplete-answer-field').addClass('uk-form-danger');
                }
            } else {
                // Get sortable list
                let sortableList = $('body').find('#velo-sortable-list');

                // Remove placeholder element if exists
                if ($('body').find('.placeholder-sortable-list').length > 0) {
                    $('body').find('.placeholder-sortable-list').remove();
                }

                // Remove error class
                $('body').find('#velo-autocomplete-search-field').removeClass('uk-form-danger');
                $('body').find('#velo-autocomplete-answer-field').removeClass('uk-form-danger');

                // Reset question value field
                $('body').find('#velo-autocomplete-search-field').val('');
                $('body').find('#velo-autocomplete-answer-field').val('');

                // Get the image id
                const image_id = $('body').find('#velo_image_id').val();

                // Create new item
                const newItem = $('<div class="velo-nested-wrapper" data-type="final-value" data-image="' + image_id + '" data-title="' + selected_item_val + '" data-answer="' + answer_val + '"><span class="item-answer"><strong>Answer:</strong> ' + answer_val + '</span> | <span class="item-title"><strong>Value:</strong> ' + selected_item_val + '</span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span></div>');

                // Add new item to the existing sortable element
                parentElement.children('.velo-nested-sortable').first().append(newItem);
                sortableList.velosortable('refresh');

                // Show save button
                $('body').find('.velo-save-edited-product-selector').removeClass('velo-display-none');

                // Find modal and hide it
                if ($('body').find('#editor-item-edit-modal').length > 0) {
                    $('body').find('#editor-item-edit-modal').each(function () {
                        UIkit.modal($(this)).hide();
                    });
                }
            }
        });

        // Create redirect URL functionality in the product selector editor
        $('body').on('click', '.create-redirect-url-button', function (event) {
            const parent_data_id = $('body').find('#velo_element_data_id').val();
            const parentElement = $('body').find('.velo-nested-wrapper[data-velo-id="' + parent_data_id + '"]');

            const redirect_url_val = $('body').find('#velo-redirect-url-field').val();
            const redirect_answer_val = $('body').find('#velo-redirect-answer-field').val();
            if (redirect_url_val === '' || redirect_answer_val === '') {
                // Add error class
                if (redirect_url_val === '') {
                    $('body').find('#velo-redirect-url-field').addClass('uk-form-danger');
                }
                if (redirect_answer_val === '') {
                    $('body').find('#velo-redirect-answer-field').addClass('uk-form-danger');
                }
            } else {
                // Remove error class
                $('body').find('#velo-redirect-url-field').removeClass('uk-form-danger');
                $('body').find('#velo-redirect-answer-field').removeClass('uk-form-danger');

                // Reset question value field
                $('body').find('#velo-redirect-url-field').val('');
                $('body').find('#velo-redirect-answer-field').val('');

                // Get sortable list
                let sortableList = $('body').find('#velo-sortable-list');

                // Get the image id
                const image_id = $('body').find('#velo_image_id').val();

                // Create new item
                const newItem = $('<div class="velo-nested-wrapper" data-type="final-redirect" data-image="' + image_id + '" data-title="' + redirect_url_val + '" data-answer="' + redirect_answer_val + '"><span class="item-answer"><strong>Answer:</strong> ' + redirect_answer_val + '</span> | <span class="item-title"><strong>Redirect:</strong> ' + redirect_url_val + '</span> <span class="uk-icon-link velo-add-copy-item-product-editor" uk-icon="copy"></span> <span class="uk-icon-link velo-remove-item-product-editor" uk-icon="trash"></span></div>');

                // Add new item to the existing sortable element
                parentElement.children('.velo-nested-sortable').first().append(newItem);
                sortableList.velosortable('refresh');

                // Show save button
                $('body').find('.velo-save-edited-product-selector').removeClass('velo-display-none');

                // Find modal and hide it
                if ($('body').find('#editor-item-edit-modal').length > 0) {
                    $('body').find('#editor-item-edit-modal').each(function () {
                        UIkit.modal($(this)).hide();
                    });
                }

                // Prevent the default behavior of the event
                event.preventDefault();
            }
        });

        // Function to create an autocomplete for the search field in the product selector editor
        function velo_set_autocomplete_for_search_field(search_element) {
            // Empty element first
            search_element.html('');

            // Then fill the element with the select2 autocomplete multi-select
            search_element.select2({
                ajax: {
                    url: velo_product_selector.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            action: 'velo_search_posts_callback',
                            nonce: velo_product_selector.ajax_settings_nonce,
                            query: params.term
                        };
                    },
                    processResults: function (response_data) {
                        if (response_data.data) {
                            const formattedData = response_data.data.map(function (item) {
                                const resultName = item.title + ' (' + item.type + ')';
                                const resultValue = item.id + '_' + item.type;
                                return {
                                    id: resultValue,
                                    text: resultName
                                };
                            });
                            return {
                                results: formattedData
                            };
                        }
                    }
                },
                minimumInputLength: 1,
                multiple: true
            });
        }

        // Remove item functionality in the product selector editor
        $('body').on('click', '.velo-remove-item-product-editor', function () {
            // Find modal
            if ($('body').find('#confirmation-editor-item-remove-modal').length > 0) {
                $('body').find('#confirmation-editor-item-remove-modal').each(function () {
                    UIkit.modal($(this)).show();
                });
            }

            // Get the parent element to be removed
            const parentElement = $(this).closest('.velo-nested-wrapper');

            // Click on the confirmation button
            $('body').on('click', '#confirm-editor-item-remove-btn', function () {
                parentElement.remove(); // Remove the parent element on confirmation

                // Find modal and hide it
                if ($('body').find('#confirmation-editor-item-remove-modal').length > 0) {
                    $('body').find('#confirmation-editor-item-remove-modal').each(function () {
                        UIkit.modal($(this)).hide();
                    });
                }
            });
        });

        // Edit item functionality in the product selector editor
        $('body').on('click', '.velo-edit-item-product-editor, .velo-add-sub-item-product-editor', function () {
            // Find modal
            if ($('body').find('#editor-item-edit-modal').length > 0) {
                $('body').find('#editor-item-edit-modal').each(function () {
                    UIkit.modal($(this)).show();
                });
            }

            // Get the parent element to be removed
            const parentElement = $(this).closest('.velo-nested-wrapper');

            // Get UNIX timestamp
            const unixTime = Date.now();

            // Set UNIX timestamp as the data ID
            parentElement.attr('data-velo-id', unixTime);

            // Set the data ID in the hidden input
            $('#velo_element_data_id').val(unixTime);

            // Check if this about a new item or existing item
            let is_new_item = false;
            if ($(this).hasClass('velo-add-sub-item-product-editor')) {
                is_new_item = true;
            }

            if (is_new_item === true) {
                // Set the title of the item in the text field
                $('body').find('#velo-edit-text-field').val('');

                // Set the answer of the item in the text field
                $('body').find('#velo-edit-answer-field').val('');

                // Set the image ID in the hidden input
                $('body').find('#velo_image_id').val('');

                // Clear (old) image preview html
                $('body').find('#velo-preview-image').html('');
            } else {
                // Set the title of the item in the text field
                $('body').find('#velo-edit-text-field').val(parentElement.attr('data-title'));

                // Set the answer of the item in the text field
                $('body').find('#velo-edit-answer-field').val(parentElement.attr('data-answer'));

                // Set the image ID in the hidden input
                $('body').find('#velo_image_id').val(parentElement.attr('data-image'));

                // Clear (old) image preview html
                $('body').find('#velo-preview-image').html('');

                // Set the image preview
                if (parentElement.attr('data-image') != '') {
                    velo_refresh_image_view_editor(parentElement.attr('data-image'));
                }
            }

            // Set the image ID in the hidden input
            const velo_edit_type = parentElement.attr('data-type').trim();

            if (is_new_item === true) {
                // Empty (old) search
                $('body').find('#velo-autocomplete-search-field').val('');

                // Change modal title
                $('body').find('#editor-item-edit-modal .uk-modal-title').text('Create New Item');

                // Set new or edit type
                $('body').find('#velo_new_or_edit').val('new');

                // Show
                $('body').find('.velo-choose-answer-or-final-item').show();

                // Hide
                $('body').find('#editor-item-edit-modal .uk-modal-footer').hide();
                $('body').find('.velo-create-answer-question').hide();
                $('body').find('.velo-add-final-step-posts').hide();
                $('body').find('.velo-add-final-step-redirect-url').hide();
                $('body').find('.velo-media-preview-and-upload').hide();
                $('body').find('.velo-all-add-new-buttons').hide();
            } else if (velo_edit_type === "nested-question") {
                // Change modal title
                $('body').find('#editor-item-edit-modal .uk-modal-title').text('Edit Question');

                // Set new or edit type
                $('body').find('#velo_new_or_edit').val('edit');

                // Set element type (nested, nested-question or final)
                $('body').find('#velo_element_type').val('nested-question');

                // Show
                $('body').find('.velo-create-answer-question').show();
                $('body').find('#editor-item-edit-modal .uk-modal-footer').show();
                $('body').find('.velo-all-edit-and-add-fields').show();

                // Hide
                $('body').find('label[for="velo-edit-answer-field"]').hide();
                $('body').find('#velo-edit-answer-field').hide();
                $('body').find('.velo-media-preview-and-upload').hide();
                $('body').find('.velo-choose-answer-or-final-item').hide();
                $('body').find('.velo-all-add-new-buttons').hide();
                $('body').find('.velo-add-final-step-posts').hide();
                $('body').find('.velo-add-final-step-redirect-url').hide();
            } else {
                // Change modal title
                $('body').find('#editor-item-edit-modal .uk-modal-title').text('Edit Answer/Question');

                // Set new or edit type
                $('body').find('#velo_new_or_edit').val('edit');

                // Set element type (nested, nested-question or final)
                $('body').find('#velo_element_type').val('nested');

                // Show
                $('body').find('.velo-create-answer-question').show();
                $('body').find('.velo-all-edit-and-add-fields').show();
                $('body').find('#editor-item-edit-modal .uk-modal-footer').show();
                $('body').find('label[for="velo-edit-answer-field"]').show();
                $('body').find('#velo-edit-answer-field').show();
                $('body').find('.velo-media-preview-and-upload').show();

                // Hide
                $('body').find('.velo-choose-answer-or-final-item').hide();
                $('body').find('.velo-all-add-new-buttons').hide();
                $('body').find('.velo-add-final-step-posts').hide();
                $('body').find('.velo-add-final-step-redirect-url').hide();
            }
        });

        // Check for the inner click in the pop-up for answer/question or final item
        $('body').on('click', '.velo-choose-in-pop-up-answer-question, .velo-choose-in-pop-up-final-item', function () {

            // Show
            $('body').find('label[for="velo-edit-answer-field"]').show();
            $('body').find('#velo-edit-answer-field').show();
            $('body').find('.velo-media-preview-and-upload').show();
            $('body').find('.velo-all-add-new-buttons').show();

            // Hide
            $('body').find('.velo-choose-answer-or-final-item').hide();
            $('body').find('#editor-item-edit-modal .uk-modal-footer').hide();
            $('body').find('.create-redirect-url-button').hide();

            if ($(this).hasClass('velo-choose-in-pop-up-answer-question')) {
                // Change title and set element type
                $('body').find('#editor-item-edit-modal .uk-modal-title').text('Create Answer/Question');
                $('body').find('#velo_element_type').val('nested');

                // Show
                $('body').find('.create-question-answer-button').show();
                $('body').find('.velo-create-answer-question').show();

                // Hide
                $('body').find('.create-velo-autocomplete-value-button').hide();
            } else {
                // Change title and set element type
                $('body').find('#editor-item-edit-modal .uk-modal-title').text('Create Final Item');
                $('body').find('#velo_element_type').val('final');

                // Empty and init autocomplete
                $('body').find('#velo-autocomplete-search-field').html('');
                setTimeout(() => velo_set_autocomplete_for_search_field($('body').find('#velo-autocomplete-search-field')), 500);

                // Show
                $('body').find('.create-velo-autocomplete-value-button').show();
                $('body').find('.velo-add-final-step-posts').show();

                // Hide
                $('body').find('.velo-create-answer-question').hide();
                $('body').find('.create-question-answer-button').hide();
            }
        });

        // Click on the save button
        $('body').on('click', '#confirm-editor-item-edit-save-btn', function () {
            const parent_data_id = $('body').find('#velo_element_data_id').val();
            const parentElement = $('body').find('.velo-nested-wrapper[data-velo-id="' + parent_data_id + '"]');

            const velo_element_type = $('body').find('#velo_element_type').val();

            if (velo_element_type === 'nested') {
                parentElement.attr('data-title', $('body').find('#velo-edit-text-field').val());
                parentElement.attr('data-answer', $('body').find('#velo-edit-answer-field').val());
                parentElement.attr('data-image', $('body').find('#velo_image_id').val());
                parentElement.children('.item-title').first().html('<strong>Question:</strong> ' + $('body').find('#velo-edit-text-field').val());
                parentElement.children('.item-answer').first().html('<strong>Answer:</strong> ' + $('body').find('#velo-edit-answer-field').val());
            } else {
                parentElement.attr('data-title', $('body').find('#velo-edit-text-field').val());
                parentElement.attr('data-answer', '');
                parentElement.attr('data-image', $('body').find('#velo_image_id').val());
                parentElement.children('.item-title').first().html('<strong>Question:</strong> ' + $('body').find('#velo-edit-text-field').val());
            }

            // Find modal and hide it
            if ($('body').find('#editor-item-edit-modal').length > 0) {
                $('body').find('#editor-item-edit-modal').each(function () {
                    UIkit.modal($(this)).hide();
                });
            }
        });

        // Remove image from question
        $('body').on('click', '#velo_remove_image_from_question', function (event) {
            // Prevent the default behavior of the event
            event.preventDefault();

            // Empty hidden image id input
            $('body').find('#velo_image_id').val('');

            // Remove image preview
            $('body').find('#velo-preview-image').html('');
        });

        // Copy shortcode to clipboard
        $('body').on('click', '.velo-shortcode-preview', function () {
            const pure_shortcode_txt = $(this).parent().find('.velo-pure-shortcode').text();
            navigator.clipboard.writeText(pure_shortcode_txt).then(function () {
                console.log('Copying to clipboard was successful!');
                show_success_copied_shortcode();
            }, function (err) {
                console.error('Could not copy text: ', err);
            });
        });

        // On copy button click
        $('body').on('click', '.velo-add-copy-item-product-editor', function (event) {
            const clone = $(this).closest('.velo-nested-wrapper').clone();
            $(this).closest('.velo-nested-sortable').prepend(clone);
        });

        // Foreach item, get image
        function set_images_in_backend() {
            // First check if we got some nested wrappers
            if ($('body').find('.velo-nested-wrapper').length > 0 && window.VeloBackendImagesLoading !== true) {
                let all_images_arr = [];
                $('body').find('.velo-nested-wrapper').each(function () {
                    if ($(this).attr('data-image') != '') {
                        if ($(this).find('> img').length === 0) {
                            all_images_arr.push($(this).attr('data-image'));
                        }
                    }
                });

                if (all_images_arr.length > 0) {
                    window.VeloBackendImagesLoading = true; // Global variable to prevent multiple requests
                    $.ajax({
                        url: velo_product_selector.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'velo_get_all_images_for_backend',
                            nonce: velo_product_selector.ajax_settings_nonce,
                            images: all_images_arr
                        },
                        success: function (response) {
                            window.VeloBackendImagesLoading = false; // Global variable to prevent multiple requests
                            if (response.data) {
                                if (response.data.images) {
                                    for (let i = 0; i < response.data.images.length; i++) {
                                        const image_id = response.data.images[i].id;
                                        const image_url = response.data.images[i].url;
                                        $('body').find('.velo-nested-wrapper[data-image="' + image_id + '"]').each(function () {
                                            if ($(this).find('> img').length === 0) {
                                                $(this).prepend('<img src="' + image_url + '" alt="Image" class="velo-mini-image-backend">');
                                            }
                                        });
                                    }
                                } else {
                                    console.log('No data received.');
                                }
                            } else {
                                console.log('No data received.');
                            }
                        },
                        error: function (xhr, status, error) {
                            window.VeloBackendImagesLoading = false; // Global variable to prevent multiple requests
                            alert('Something went wrong. Please try again.');
                            console.log(xhr);
                            console.log(status);
                            console.log(error);
                        }
                    });
                }
            }
        }

        // Try to refresh images every 2 seconds
        setInterval(set_images_in_backend, 2000);

        // Show success text for copying the shortcode
        function show_success_copied_shortcode() {
            $('body').find('.velo-pure-shortcode').hide();
            $('body').find('.velo-copy-success').show();

            setTimeout(function () {
                $('body').find('.velo-pure-shortcode').show();
                $('body').find('.velo-copy-success').hide();
            }, 2000);
        }

        // Function to get all data from the sortable list
        function velo_save_nested_sortable_data(element) {
            const nestedItems = element.children('.velo-nested-wrapper');

            const data = [];

            nestedItems.each(function () {
                const text = $(this).attr('data-title').trim();
                const answer = $(this).attr('data-answer').trim();
                const type = $(this).attr('data-type').trim();
                const image = $(this).attr('data-image').trim();
                const nestedSortables = $(this).children('.velo-nested-sortable');
                if (nestedSortables.length > 0) {
                    nestedSortables.each(function () {
                        const nestedData = velo_save_nested_sortable_data($(this));
                        data.push({ text: text, type: type, image: image, answer: answer, nestedData: nestedData });
                    });
                } else {
                    data.push({ text: text, type: type, image: image, answer: answer });
                }
            });

            return data;
        }

        // Smooth transition of data in HTML element
        function velo_smooth_show(fill_element, html, bar_element) {
            return new Promise((resolve, reject) => {
                // Update the UI with the updated progress value
                bar_element.val(100);

                // Fade out
                fill_element.fadeOut(500);

                setTimeout(() => {
                    // Put HTML in
                    fill_element.html(html);

                    // Fade in
                    fill_element.fadeIn(100);

                    // Resolve the promise
                    resolve();
                }, 500);
            });
        }

        // Move the progress bar
        function velo_start_progress_bar(bar_element) {
            const animate = setInterval(function () {
                bar_element.val(bar_element.val() + 10);
                if (bar_element.val() >= 90) {
                    clearInterval(animate);
                }
            }, 1000);
        }

        // Backend switch final step posts etc / redirect URL
        $('body').on('click', '.velo-switch-to-url-input-final', function (event) {
            $('body').find('.velo-add-final-step-posts').hide();
            $('body').find('.create-velo-autocomplete-value-button').hide();
            $('body').find('.velo-add-final-step-redirect-url').show();
            $('body').find('.create-redirect-url-button').show();

            // Prevent the default behavior of the event
            event.preventDefault();
        });

        // Backend switch final step posts etc / redirect URL
        $('body').on('click', '.velo-switch-back-final', function (event) {
            $('body').find('.velo-add-final-step-redirect-url').hide();
            $('body').find('.create-redirect-url-button').hide();
            $('body').find('.velo-add-final-step-posts').show();
            $('body').find('.create-velo-autocomplete-value-button').show();

            // Prevent the default behavior of the event
            event.preventDefault();
        });

        // Pop-up image selector
        $('body').on('click', 'input#velo_media_manager', function (event) {
            // Prevent default behavior of button
            event.preventDefault();

            // Default variable for the image frame
            let image_frame;

            // Define image_frame as wp.media object
            image_frame = wp.media({
                title: 'Select Media',
                multiple: false, // Set to false to allow only single selection
                library: {
                    type: 'image', // Set to 'image' to only allow selection of images
                }
            });

            // On WP image/media frame open
            image_frame.on('open', function () {
                // On open, get the id from the hidden input
                // and select the appropriate images in the media manager
                let selection = image_frame.state().get('selection');
                let ids = $('body').find('input#velo_image_id').val().split(',');
                ids.forEach(function (id) {
                    let attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            // On WP image/media frame close
            image_frame.on('close', function () {
                // On close, get selections and save to the hidden input
                // plus other AJAX stuff to refresh the image preview
                let selection = image_frame.state().get('selection');
                let gallery_ids = new Array();
                let my_index = 0;
                selection.each(function (attachment) {
                    gallery_ids[my_index] = attachment['id'];
                    my_index++;
                });
                let ids = gallery_ids.join(",");
                if (ids.length === 0) return true;//if closed withput selecting an image
                $('body').find('input#velo_image_id').val(ids);
                velo_refresh_image_view_editor(ids);
            });

            // Open WP image/media frame
            image_frame.open();
        });

        // Ajax request to refresh/view the image preview
        function velo_refresh_image_view_editor(image_id) {
            $.ajax({
                url: velo_product_selector.ajax_url,
                type: 'POST',
                data: {
                    action: 'velo_get_image_for_editor',
                    nonce: velo_product_selector.ajax_settings_nonce,
                    image_id: image_id
                },
                success: function (response) {
                    if (response.data) {
                        if (response.data.image_html) {
                            $('body').find('#velo-preview-image').html(response.data.image_html);
                            $('body').find('#velo-preview-image').append('<button type="button" class="uk-button uk-button-danger" id="velo_remove_image_from_question"><span class="uk-icon-link" uk-icon="trash"></span></button>');
                        } else {
                            alert('No image selected.');
                        }
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    alert('Something went wrong. Please try again.');
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        }

        // Success message in top center screen
        function showSuccessNotification(message) {
            UIkit.notification({
                message: message,
                status: 'success',
                pos: 'top-center',
                timeout: 2000
            });
        }

    });
})(jQuery);
