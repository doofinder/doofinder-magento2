/*global define Ajax:false*/
define([
    "jquery",
    "prototype"
], function() {
    var generate = function(config, node) {

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
                        console.error(e);
                    }
                }
            });
        })
    };

    return generate;
});
