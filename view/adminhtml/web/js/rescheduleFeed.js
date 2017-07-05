/*global define Form:false*/
define([
    "jquery",
    "prototype"
], function($) {
    var reschedule = function(config, node) {

        var add_message = function(text) {
            var html = '' +
                '<div class="message-system-inner">' +
                '    <ul class="message-system-list">' +
                '        <li class="message message-notice">' + text + '</li>' +
                '    </ul>' +
                '</div>';

            $('#system_messages').append(html);
        };

        var changed = false;

        new Form.Observer(node, 0.3, function(form) {
            if (changed) return;
            add_message('Configuration has changed. The feed generation will be rescheduled after saving.');
            form.insert('<input type="hidden" name="reset" value="1"/>');
            changed = true;
        });
    };

    return reschedule;
});
