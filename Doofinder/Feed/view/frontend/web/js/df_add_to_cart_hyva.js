class DoofinderAddToCartError extends Error {
  constructor(reason, status = "") {
    const message =
      "Error adding an item to the cart. Reason: " +
      reason +
      ". Status code: " +
      status;
    super(message);
    this.name = "DoofinderAddToCartError";
  }
}

document.addEventListener("doofinder.cart.add", function (event) {
  const { item_id, amount, statusPromise } = event.detail;
  addToCart(item_id, amount, statusPromise);
});

function addToCart(item_id, amount, statusPromise) {
  amount = !amount ? 1 : parseInt(amount);
  item_id = parseInt(item_id);

  const params = new URLSearchParams({
    form_key: hyva.getFormKey(),
    id: item_id,
    qty: amount,
  });

  fetch(`${BASE_URL}doofinderfeed/Product/AddToCart?${params.toString()}`, {
    method: "POST",
  })
    .then((response) => {
      if (!response.ok) {
        return response.text().then((error) => {
          throw new Error(error);
        });
      }

      return response.json();
    })
    .then((data) => {
      if (data.product_url) {
        statusPromise.reject(
          new DoofinderAddToCartError(
            "Configurable product. Redirecting to product URL",
            200,
          ),
        );
        window.location = data.product_url;
        return;
      }

      statusPromise.resolve(
        "The item has been successfully added to the cart.",
      );
      dispatchEvent(new Event("reload-customer-section-data"));
    })
    .catch((error) => {
      statusPromise.reject(new DoofinderAddToCartError(error));
    });
}
