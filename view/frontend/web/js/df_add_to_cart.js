require(["jquery", "mage/url", "Magento_Customer/js/customer-data"], function (
  $,
  urlBuilder,
  customerData
) {
  $(document).ready(function () {
    document.addEventListener("doofinder.cart.add", function (event) {
      const { item_id, amount } = event.detail;
      addProductToCart(item_id, amount);
    });
  });

  function closeDoofinderLayer() {
    $('button[dfd-click="close-layer"').click();
  }

  function addProductToCart(item_id, amount) {
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
          window.location = data.product_url;
          return;          
        } else {
          console.log(`added ${amount} of item #${item_id}`);
          closeDoofinderLayer();
        }
      },
      complete: function () {
        customerData.invalidate(["cart"]);
        customerData.reload(["cart"], true);
      },
      showLoader: true,
    });
  }
});
