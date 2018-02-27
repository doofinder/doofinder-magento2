define(["jquery"], function($) { // eslint-disable-line no-undef
  $.widget('banner.js', {
    _create: function() {
      this.setLocation();
      this.watchClick();
    },

    setLocation: function() {
      $(".search-result-banner").insertAfter($(this.options.bannerPlacement));
    },

    watchClick: function() {
      var self = this;
      $(".search-result-banner a").one("click", function(event) {
        var $this = $(this),
          bannerId = $this.attr("data-banner-id");
        if ($this.attr("target") !== "_blank") {
          event.preventDefault();
          self.registerClick(bannerId);
          window.location = $this.attr("href");
        } else {
          self.registerClick(bannerId);
        }
      });
    },

    registerClick: function(bannerId) {
      var self = this;
      $.ajax({
        url: self.options.ajaxUrl,
        type: "POST",
        dataType: "json",
        data: {
          bannerId: bannerId
        }
      });
    }
  });

  return $.banner.js;
});
