define([
  'jquery',
  'mageUtils'
], function ($, utils) {
  $.widget('doofinder.searchResult', {
    options: {
      registerClickUrl: null,
      productContainer: null,
      productLink: null
    },

    _create: function () {
      // make sure every required option has a value
      if (this.options.registerClickUrl === null
        || this.options.productContainer === null
        || this.options.productLink === null
      ) {
        return;
      }

      this.watchClick();
    },

    watchClick: function () {
      var self = this;
      var productId = this.element.attr('data-product-id');
      var link = this.element
        .closest(this.options.productContainer)
        .find(this.options.productLink);

      if (!productId || link.length === 0) {
        return;
      }

      link.one('click', function (event) {
        var $this = $(this);

        if ($this.attr('target') !== '_blank' && $this.is('[href]')) {
          event.preventDefault();
          self.registerClick(productId);
          window.location = $this.attr('href');
        } else {
          self.registerClick(productId);
        }
      });
    },

    registerClick: function (productId) {
      var self = this;

      $.ajax({
        url: self.options.registerClickUrl,
        type: 'POST',
        dataType: 'json',
        data: {
          productId: productId,
          query: utils.getUrlParameters(window.location.href).q
        }
      });
    }
  });

  return $.doofinder.searchResult;
});
