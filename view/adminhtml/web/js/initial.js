// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/lib/spinner',
    'underscore',
    'mage/translate'
], function (Component, $, Spinner, _) {
    'use strict';

    return Component.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            elements: {
                buttonId: '${ $.buttonId }',
                buttonRegister: '${ $.buttonRegister }',
                buttonLogin: '${ $.buttonLogin }',
            },
            urls: {
                save: '${ $.submit_url }',
                save_config: '${ $.save_config_url }',
                permissions: '${ $.permissionsDialogUrl }',
                tokens: '${ $.tokensDialogUrl }',
                accessToken: '${ $.accessTokenUrl }'
            },
            resource: [
                'Magento_Catalog::catalog',
                'Magento_Catalog::catalog_inventory',
                'Magento_Catalog::products',
                'Magento_Catalog::categories',
                'Magento_Backend::stores',
                'Magento_Backend::stores_settings',
                'Magento_Backend::store',
                'Magento_CatalogInventory::cataloginventory',
                'Magento_Backend::stores_attributes',
                'Magento_Catalog::attributes_attributes',
                'Magento_Catalog::sets',
            ],
            formData: {
                email: '${ $.email }',
                name: '${ $.name }'
            },
            isIntegrationCreated: '${ $.isIntegrationCreated }',
            hasApiKey: '${ $.hasApiKey }',
            installingLoopStatus: '${ $.installingLoopStatus }'
        },
        createIntegration: function () {
            let initial = this,
                integrationId = null;

            Spinner.show();

            let integrationData = this.formData;

            integrationData.current_password = "";
            integrationData.all_resources = 0;
            integrationData.resource = this.resource;
            $.ajax({
                url: this.urls.save,
                method: 'POST',
                data: integrationData
            }).done(function (data) {
                //view: module-integration/view/adminhtml/web/js/integration.js
                const messageBox = $('.messages');

                if (data._redirect) {
                    const text = $.mage.__('The integration was not created due to some error. It may be that the Doofinder integration already exists.');
                    addMessage(text);
                } else if (data.integrationId) {
                    $.ajax({
                        url: initial.urls.save_config,
                        method: 'POST',
                        data: { 'id': data.integrationId }
                    }).done(function (data) {
                        if (data.result === false) {
                            addMessage(data.error);
                        }
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        console.error('Error (' + textStatus + '): ' + errorThrown);
                    });

                    // Activating and giving permissions

                    // Replace placeholders in URL
                    integrationId = data.integrationId;
                    const ajaxUrl = initial.urls.permissions.replace(':id', integrationId);

                    $.ajax({
                        url: ajaxUrl,
                        cache: false,
                        data: {
                            'form_key': window.FORM_KEY
                        },
                        method: 'GET',

                        /** @inheritdoc */
                        beforeSend: function () {
                            // Show the spinner
                            $('body').trigger('processStart');
                        },
                        /** @inheritdoc */
                        success: function (result) {
                            var redirect = result._redirect;

                            if (redirect) {
                                const text = $.mage.__('An error occurred during the activation process. Please try to activate the integration manually in the integrations section.');
                                messageBox.append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + text + '</div></div>');

                                return;
                            }

                            // Replace placeholders in URL
                            const ajaxUrl = initial.urls.tokens.replace(':id', integrationId);
                            $.ajax({
                                url: ajaxUrl,
                                cache: false,
                                data: {
                                    'form_key': window.FORM_KEY
                                },
                                method: 'GET',

                                /** @inheritdoc */
                                beforeSend: function () {
                                    // Show the spinner
                                    $('body').trigger('processStart');
                                },
                                /** @inheritdoc */
                                success: function (result) {
                                    var redirect = result._redirect;

                                    if (redirect) {
                                        const text = $.mage.__('An error occurred during the activation process. Please try to activate the integration manually in the integrations section.');
                                        messageBox.append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + text + '</div></div>');

                                        return;
                                    }
                                  // Replace placeholders in URL
                                  const ajaxUrl = initial.urls.accessToken.replace(':id', integrationId);
                                  $.ajax({
                                    url: ajaxUrl,
                                    cache: false,
                                    data: {
                                      'form_key': window.FORM_KEY
                                    },
                                    method: 'GET',
                                    beforeSend: function () {
                                      // Show the spinner
                                      $('body').trigger('processStart');
                                    }
                                  }).done(function (result) {
                                    let redirect = result._redirect;
                                    if (redirect) {
                                      const text = $.mage.__('An error occurred retrieving integration access token. Please, reload the page and continue process.');
                                      messageBox.append('<div class="message message-error error"><div data-ui-id="messages-message-error">' + text + '</div></div>');
                                      return;
                                    }
                                    // We replace the integration id param in the route path of the linking account process
                                    $(document).trigger('changeOnIntegrationId', {'accessToken': result.accessToken});
                                    const text = $.mage.__('The integration was created and activated correctly.');
                                    messageBox.append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + text + '</div></div>');

                                    // We activate login/register buttons
                                    $(initial.elements.buttonRegister).prop("disabled", false);
                                    $(initial.elements.buttonLogin).prop("disabled", false);
                                    $(initial.elements.buttonId).prop("disabled", true);
                                  }).fail(function (jqXHR, status, error) {
                                    alert({
                                      content: $.mage.__('An error occurred retrieving integration access token. Please, reload the page and continue process.')
                                    });
                                    window.console && console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
                                  }).always(function () {
                                    // Hide the spinner
                                    $('body').trigger('processStop');
                                  });
                                },

                                /** @inheritdoc */
                                error: function (jqXHR, status, error) {
                                    alert({
                                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                                    });
                                    window.console && console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
                                },

                                /** @inheritdoc */
                                complete: function () {
                                    // Hide the spinner
                                    $('body').trigger('processStop');
                                }
                            });
                        },

                        /** @inheritdoc */
                        error: function (jqXHR, status, error) {
                            alert({
                                content: $.mage.__('Sorry, something went wrong. Please try again later.')
                            });
                            window.console && console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
                        },

                        /** @inheritdoc */
                        complete: function () {
                            // Hide the spinner
                            $('body').trigger('processStop');
                        }
                    });
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error('Error (' + textStatus + '): ' + errorThrown);
            }).always(function () {
                Spinner.hide();
            });
            return this;
        },
        initialize: function () {
            this._super();

            let self = this;

            // If we finished the linked account step previously we show an informative message,
            // in other case we show the integration and login/register buttons
            let installingLoopStatus = parseInt(this.installingLoopStatus);
            if(installingLoopStatus === 0 && !Boolean(this.hasApiKey)) {
                // Create integration click event
                $(this.elements.buttonId).click(function (e) {
                    e.preventDefault();
                    self.createIntegration();
                });

                // If the integration already exists we activate login/register buttons and disable the create integration one
                if (Boolean(this.isIntegrationCreated)) {
                    const text = $.mage.__('The integration was already created and activated.');
                    $('.messages').append('<div class="message message-success success"><div data-ui-id="messages-message-success">' + text + '</div></div>');

                    $(this.elements.buttonRegister).prop("disabled", false);
                    $(this.elements.buttonLogin).prop("disabled", false);
                    $(this.elements.buttonId).prop("disabled", true);
                }
            } else if (installingLoopStatus === 0 || installingLoopStatus === 100) {
                $('.steps-col').hide();
                $('.ajax-steps-col').show();
                addAjaxMessage($.mage.__('The setup process was correctly finished.'));
            } else {
              $('.steps-col').hide();
              $('.ajax-steps-col').show();
              let failStep = getFailStep(installingLoopStatus);
              addAjaxMessage(
                $.mage.__(
                  'Installation has failed in step "' + failStep + '". ' +
                  'Please, uninstall and install again the extension following documentation instructions. ' +
                  'After installation is complete run again this Initial Setup.'
                )
              );
            }

            $('.steps-setup').show();
        }
    });

    function addAjaxMessage(text) {
        $('#message-ajax-steps').text(text);
    }

    function addMessage(text, type = 'error') {
        $('.messages').append('<div class="message message-' + type + ' ' + type + '"><div data-ui-id="messages-message-' + type + '">' + text + '</div></div>');
    }

    function getFailStep(installingLoopStatus) {
      let failStep = 'Initial Step';
      if (installingLoopStatus === 1) {
        failStep = 'Checking if API Key is set';
      } else if (installingLoopStatus === 2) {
        failStep = 'Create Search Engines';
      } else if (installingLoopStatus === 3) {
        failStep = 'Create Indices';
      } else if (installingLoopStatus === 4) {
        failStep = 'Process Search Engines';
      } else if (installingLoopStatus === 5) {
        failStep = 'Create Display Layers';
      }
      return failStep;
    }
});
