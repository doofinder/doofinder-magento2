require(["jquery", "mage/url", "Magento_Customer/js/customer-data"], function (
  $,
  urlBuilder,
  customerData
) {
  $(document).ready(function () {
    document.addEventListener("doofinder.cart.add", function (event) {
      const { item_id, amount, statusPromise } = event.detail;
      addProductToCart(item_id, amount, statusPromise);
    });
  });

  class DoofinderAddToCartError extends Error {
    constructor(reason, status = "") {
      const message = "Error adding an item to the cart. Reason: " + reason + ". Status code: " + status;
      super(message);
      this.name = "DoofinderAddToCartError";
    }
  } 

  function addProductToCart(item_id, amount, statusPromise) {
    amount = !amount ? 1 : parseInt(amount);
    item_id = parseInt(item_id);
    /*
     * To show the ajax loader mask, we need to set the doofinder layer z-index
     * to a value below the loader mask (9999)
     */
    $(".dfd-fullscreen").css("z-index", 9998);

    $.ajax({
      method: "post",
      data: {
        id: item_id,
        qty: amount,
      },
      url: urlBuilder.build("doofinderfeed/Product/AddToCart"),
      success: function (data) {
        if (data.hasOwnProperty("product_url")) {
          statusPromise.reject(new DoofinderAddToCartError("Configurable product. Redirecting to product URL", 200));
          window.location = data.product_url;
          return;          
        }
        statusPromise.resolve("The item has been successfully added to the cart.");
      },
      complete: function () {
        customerData.invalidate(["cart"]);
        customerData.reload(["cart"], true);
      },
      error: function(xhr, ajaxOptions, thrownError) {
        statusPromise.reject(new DoofinderAddToCartError(thrownError, xhr.status));
      },
      showLoader: true,
    });
  }
});
