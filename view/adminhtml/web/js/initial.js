// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, $, _) {
    'use strict';

    return Component.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            elements: {
                sectorSelector: '${ $.sectorSelector }',
                buttonRegister: '${ $.buttonRegister }',
                buttonLogin: '${ $.buttonLogin }',
            },
            urls: {
                save: '${ $.submit_url }',
                saveConfig: '${ $.save_config_url }',
                permissions: '${ $.permissionsDialogUrl }',
                tokens: '${ $.tokensDialogUrl }',
                saveSector: '${ $.saveSectorUrl }',
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
        _createIntegration: async function (data) {
            const self = this;

            if (data._redirect) {
                throw $.mage.__('The integration was not created due to some error. It may be that the Doofinder integration already exists.');
            } else if (data.integrationId) {
                // Activating and giving permissions
                ajax_post(this.urls.saveConfig, { 'id': data.integrationId })
                    .then(data => { if (data.result === false) addMessage(data.error) })
                    .catch((jqXHR, textStatus, errorThrown) => console.error('Error (' + textStatus + '): ' + errorThrown))

                // Replace placeholders in URL
                const manageTokensCallback = async (redirect, callbackFunction) => {
                    if (redirect)
                        throw $.mage.__('An error occurred during the activation process. Please try to activate the integration manually in the integrations section.'); else
                        await callbackFunction();
                };

                ajax_get(this.urls.permissions.replace(':id', data.integrationId))
                    .then(result => { manageTokensCallback(result._redirect, _ => { ajax_get(self.urls.tokens.replace(':id', data.integrationId))}) })
                    .then(result => { manageTokensCallback(result._redirect, _ => { ajax_get(self.urls.accessToken.replace(':id', data.integrationId))}) })
                    .then(_ => {
                        $(self.elements.buttonRegister).prop("disabled", false);
                        $(self.elements.buttonLogin).prop("disabled", false);
                    })
            }
        },
        changeSector: async function (value) {
            const self = this;

            $(this.elements.sectorSelector).prop("disabled", true);
            $('body').trigger('processStart');
            
            ajax_post(this.urls.saveSector, value)
                .then(_ => { if ($(self.elements.buttonLogin).prop("disabled")) self.createIntegration() })
                .catch(error => {
                    addMessage(error);
                    alert(error); })
                .finally(_ => { 
                    $('body').trigger('processStop');
                    $(self.elements.sectorSelector).prop("disabled", false); })
        },
        createIntegration: function () {
            let integrationData = this.formData;
            integrationData.current_password = "";
            integrationData.all_resources = 0;
            integrationData.resource = this.resource;

            ajax_post(this.urls.save, integrationData)
                .then(data => { this._createIntegration(data) })
        },
        initialize: function () {
            this._super();

            const self = this;
            $(this.elements.sectorSelector).change(function () { if (this.value) self.changeSector(this.value) });

            // If we finished the linked account step previously we show an informative message,
            // in other case we show the integration and login/register buttons
            let installingLoopStatus = parseInt(this.installingLoopStatus);
            if (installingLoopStatus === 0 && !Boolean(this.hasApiKey)) {
                // If the integration already exists we activate login/register buttons and disable the create integration one
                if (Boolean(this.isIntegrationCreated)) {
                    const text = $.mage.__('The integration was already created and activated.');
                    addMessage(text, 'success');
                    $(this.elements.buttonRegister).prop("disabled", false);
                    $(this.elements.buttonLogin).prop("disabled", false);
                }
            } else if (installingLoopStatus === 0 || installingLoopStatus === 100) {
                $('.steps-col').hide();
                $('.ajax-steps-col').show();
                $('#message-ajax-steps').text($.mage.__('The setup process was correctly finished.'));
            } else {
                $('.steps-col').hide();
                $('.ajax-steps-col').show();
                $('#message-ajax-steps').text(
                    $.mage.__(
                        'Installation has failed. ' +
                        'Please, uninstall and install again the extension following documentation instructions. ' +
                        'After installation is complete run again this Initial Setup.'
                    )
                );
            }

            $('.steps-setup').show();
        }
    });

    async function ajax_get(url) {
        return await $.ajax({
            url: url,
            method: 'GET',
            cache: false,
            data: {
                'form_key': window.FORM_KEY
            }
        });
    }

    async function ajax_post(url, data) {
        return await $.ajax({
            url: url,
            method: 'POST',
            cache: false,
            data: data
        });
    } 

    function addMessage(text, type = 'error') {
        $('.messages').append('<div class="message message-' + type + ' ' + type + '"><div data-ui-id="messages-message-' + type + '">' + text + '</div></div>');
    }
});