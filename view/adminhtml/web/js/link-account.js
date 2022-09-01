// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'mage/translate'
], function (Component, $, Spinner, alert, _) {
    'use strict';

    return Component.extend({
        defaults: {
            elements: {
                buttonRegister: '${ $.buttonRegister }',
                buttonLogin: '${ $.buttonLogin }',
            },
            urls: {
                signupEndpoint: '${ $.signupEndpoint }',
                loginEndpoint: '${ $.loginEndpoint }',
                checkAPIKeyUrl: '${ $.checkAPIKeyUrl }',
                createStoreUrl: '${ $.createStoreUrl }',
                doofinderConnectUrl: '${ $.doofinderConnectUrl}'
            },
            paramsPopup: '${ $.paramsPopup }'
        },

        popupDoofinder: function (type) {
            let self = this;
            const params = '?' + this.paramsPopup;
            let domain;
            if (type === 'signup') {
                domain = this.urls.signupEndpoint;
            } else {
                domain = this.urls.loginEndpoint;
            }
            const winObj = this.popupCenter(domain + params, 'Doofinder');
            // We check if the windows was correctly opened and not blocked by the Browser
            if(winObj !== null && typeof(winObj) !== 'undefined') {
                const loop = setInterval(function () {
                    if (winObj.closed) {
                        clearInterval(loop);
                    }
              }, 1000);
            } else {
                const text = $.mage.__('An error occurred. Probably your browser blocked up the popup window. Please try again after giving us access to show this popup.');
                addMessage(text);
            }
            return this;
        },

        initialize: function () {
            this._super();
            let self = this;
            // Listener for the doofinder response
            self.listen_doofinder_response();
            // login/register click event
            $(this.elements.buttonRegister).click(function (e) {
                e.preventDefault();
                self.popupDoofinder('signup');
            });
            $(this.elements.buttonLogin).click(function (e) {
                e.preventDefault();
                self.popupDoofinder('login');
            });
        },

        popupCenter: function (url, title) {
            const newWindow = window.open(url, title, 'height=' + screen.height + ',width=' + screen.width + ',resizable=yes,scrollbars=yes,toolbar=yes,menubar=yes,location=yes');

            if (window.focus && newWindow !== null && typeof(newWindow) !== 'undefined') newWindow.focus();

            return newWindow;
        },

        installingLoop: function () {
            let self = this;
            $.ajax({
                url: self.urls.checkAPIKeyUrl,
                method: 'GET',
                cache: false,
                data: {
                    'form_key': window.FORM_KEY
                },
                beforeSend: function () {
                    // 2) We hide the login/register buttons and show the new message container
                    $('.steps-col').hide();
                    $('.ajax-steps-col').show();
                    addAjaxMessage($.mage.__('Please be patient, we are autoinstalling your definitive search solution...'));
                }
            }).done(function (result) {
                if (Boolean(result) === false) {
                    const text = $.mage.__('An error occurred during the process. Please try to link an account later.');
                    addMessage(text);
                    $('.steps-col').show();
                    $('.ajax-steps-col').hide();
                    return;
                }
                // create store
                $.ajax({
                    url: self.urls.createStoreUrl,
                    method: 'GET',
                    cache: false,
                    data: {
                        'form_key': window.FORM_KEY
                    },
                    beforeSend: function () {
                        addAjaxMessage($.mage.__('Creating doofinder stores ...'));
                    }
                }).done(function () {
                    addAjaxMessage($.mage.__(
                        'The setup wizard has successfully finished but search engine cannot be used until ' +
                        'index processing has completed.<br/>Now, you can check process task status or go ' +
                        'to configuration page.'));
                      $('.setup-finish-buttons').removeClass('hidden');
                }).fail(ajaxRequestFail);
            }).fail(ajaxRequestFail);
        },

        listen_doofinder_response: function() {
            let self = this;
            window.addEventListener(
                "message",
                (event) => {
                  const doofinder_regex = /.*\.doofinder\.com/gm;
                  console.log(event.data)
                  //Check that the sender is doofinder
                  if (!doofinder_regex.test(event.origin)) return;
                  if (event.data) {
                    const data_received = event.data.split("|");
                    const event_name = data_received[0];
                    const event_data = JSON.parse(atob(data_received[1]));
                    if (event_name === "set_doofinder_data") self.send_connect_data(event_data);
                  }
                },
                false
              );
        },

        send_connect_data: function(data) {
            let self = this;
            $.ajax({
              url: self.urls.doofinderConnectUrl,
              method: "POST",
              data: data,
              dataType: "json",
              cache: false
            }).done(function (response) {
                if(response.result == true) {
                    self.installingLoop();
                } else {
                    $('.steps-col').show();
                    $('.ajax-steps-col').hide();
                    addMessage($.mage.__(response.error));
                }
            }).fail(ajaxRequestFail);
        }
    });

    function ajaxRequestFail(jqXHR, status, error) {
        $('body').trigger('processStop');
        alert({
            content: $.mage.__(
              'Installation has failed. Please take a look into the logs for more information. ' +
              'Maybe you need uninstall and install again the extension following documentation instructions. '
            )
        });
        window.console && console.log(status + ': ' + error + '\nResponse text:\n' + jqXHR.responseText);
    }

    function addAjaxMessage(text) {
        $('#message-ajax-steps').html(text);
    }

    function addMessage(text, type = 'error') {
        $('.messages').append('<div class="message message-' + type + ' ' + type + '"><div data-ui-id="messages-message-' + type + '">' + text + '</div></div>');
    }
});
