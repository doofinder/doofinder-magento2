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
                createSearchEnginesUrl: '${ $.createSearchEnginesUrl }',
                createIndicesUrl: '${ $.createIndicesUrl }',
                processSearchEnginesUrl: '${ $.processSearchEnginesUrl }',
                createDisplayLayersUrl: '${ $.createDisplayLayersUrl }'
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
                  self.installingLoop();
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
                //create search engines
                $.ajax({
                    url: self.urls.createSearchEnginesUrl,
                    method: 'GET',
                    cache: false,
                    data: {
                        'form_key': window.FORM_KEY
                    },
                    beforeSend: function () {
                        addAjaxMessage($.mage.__('Creating search engines...'));
                    }
                }).done(function () {
                    //create indices
                    $.ajax({
                        url: self.urls.createIndicesUrl,
                        method: 'GET',
                        cache: false,
                        data: {
                            'form_key': window.FORM_KEY
                        },
                        beforeSend: function () {
                            addAjaxMessage($.mage.__('Creating index to search on your site...'));
                        }
                    }).done(function () {
                        //process search engines
                        $.ajax({
                            url: self.urls.processSearchEnginesUrl,
                            method: 'GET',
                            cache: false,
                            data: {
                                'form_key': window.FORM_KEY
                            },
                            beforeSend: function () {
                                addAjaxMessage($.mage.__('Processing search engines...'));
                            }
                        }).done(function () {
                            //create display layers
                            $.ajax({
                                url: self.urls.createDisplayLayersUrl,
                                method: 'GET',
                                cache: false,
                                data: {
                                    'form_key': window.FORM_KEY
                                },
                                beforeSend: function () {
                                    addAjaxMessage($.mage.__('Creating display layers...'));
                                }
                            }).done(function () {
                               addAjaxMessage($.mage.__(
                                 'The setup wizard has successfully finished but search engine cannot be used until ' +
                                 'index processing has completed.<br/>Now, you can check process task status or go ' +
                                 'to configuration page.'));
                               $('.setup-finish-buttons').removeClass('hidden');
                            }).fail(ajaxRequestFail);
                        }).fail(ajaxRequestFail);
                    }).fail(ajaxRequestFail);
                }).fail(ajaxRequestFail);
            }).fail(ajaxRequestFail);
        }
    });

    function ajaxRequestFail(jqXHR, status, error) {
        $('body').trigger('processStop');
        let failStep = jqXHR.responseText;
        alert({
            content: $.mage.__(
              'Installation has failed in step "' + failStep + '". ' +
              'Please, uninstall and install again the extension following documentation instructions. ' +
              'After installation is complete run again this Initial Setup.'
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
