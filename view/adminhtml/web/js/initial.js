// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function(Component, $, _) {
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
            hasApiKey: '${ $.hasApiKey }'
        },
        changeSector: async function(value) {
            $(this.elements.sectorSelector).prop("disabled", true);
            toggleSpinner(true);

            try {
                await ajax_post(this.urls.saveSector, value);
                if (!Boolean(this.isIntegrationCreated)) await this.createIntegration();
            } catch (error) {
                addMessage(error);
                alert(error);
            }

            toggleSpinner(false);
            $(this.elements.sectorSelector).prop("disabled", false);
        },
        createIntegration: async function() {
            let integrationData = this.formData;
            integrationData.current_password = "";
            integrationData.all_resources = 0;
            integrationData.resource = this.resource;

            let data = await ajax_post(this.urls.save, integrationData);
            return await this.createIntegrationTokens(data);
        },
        createIntegrationTokens: async function(data) {
          const self = this;
          if (data._redirect) {
              throw $.mage.__('The integration was not created due to some error. It may be that the Doofinder integration already exists.');
          } else if (data.integrationId) {
              // Replace placeholders in URL
              const manageTokensCallback = async(result, callbackFunction) => {
                  if (result && result._redirect)
                      throw $.mage.__('An error occurred during the activation process. Please try to activate the integration manually in the integrations section.');
                  else
                      await callbackFunction();
              };

              // Activating and giving permissions
              let dataResult = await ajax_post(this.urls.saveConfig, { 'id': data.integrationId });
              let result = await ajax_get(this.urls.permissions.replace(':id', data.integrationId));

              if (dataResult.result === false) addMessage(dataResult.error);

              manageTokensCallback(result, _ => { ajax_get(self.urls.tokens.replace(':id', data.integrationId)) });
              manageTokensCallback(result, _ => { ajax_get(self.urls.accessToken.replace(':id', data.integrationId)) });

              $(this.elements.buttonRegister).prop("disabled", false);
              $(this.elements.buttonLogin).prop("disabled", false);
          }
        },
        initialize: function() {
            this._super();

            const self = this;
            $(this.elements.sectorSelector).change(function() { if (this.value) self.changeSector(this.value) });

            if (!Boolean(this.hasApiKey)) {
                // If the integration already exists we activate login/register buttons and disable the create integration one
                if (Boolean(this.isIntegrationCreated)) {
                    const text = $.mage.__('The integration was already created and activated.');
                    addMessage(text, 'success');
                    $(this.elements.buttonRegister).prop("disabled", false);
                    $(this.elements.buttonLogin).prop("disabled", false);
                }
            } else {
                $('.steps-col').hide();
                $('.ajax-steps-col').show();
                $('#message-ajax-steps').text($.mage.__('The setup process was correctly finished.'));
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

    function toggleSpinner(enable) {
      enable ? $('body').trigger('processStart'): $('body').trigger('processStop');
    }
});