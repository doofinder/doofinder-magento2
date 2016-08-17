define([
    "jquery",
    "prototype"
], function($, prototype) {
    var generate = function(config, node) {

        var add_message = function(text) {
            var html = '' +
                '<div class="message-system-inner">' +
                '    <ul class="message-system-list">' +
                '        <li class="message message-notice">' + text + '</li>' +
                '    </ul>' +
                '</div>';

            $('#system_messages').append(html);
          };

          document.observe("dom:loaded", function() {
            if ($('doofinder_feed_feed_feed_cron')) {
                var changed = false;
                new Form.Observer('config-edit-form', 0.3, function(form, value) {
                    if (changed) return;
                    add_message('Configuration has changed. The feed generation will be rescheduled after saving.');
                    form.insert('<input type="hidden" name="reset" value="1"/>');
                    changed = true;
                });
            }
        });

        node.on('click', function() {
            new Ajax.Request(config.ajaxUrl, {
                onComplete: function(response) {
                    try {
                        if (response.responseText.isJSON()) {
                            response = response.responseText.evalJSON();
                        }

                        if(response.message) {
                            alert(response.message);
                        } else {
                            alert('Something went wrong');
                        }
                    } catch (e) {
                        alert('Something went wrong. See console for more information.');
                        console.log(e);
                    }
                }
            });
        })
    };

    return generate;
});
