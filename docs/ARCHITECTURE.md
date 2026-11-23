# Architecture notes

- Controllers handle HTTP concerns; services encapsulate checkout/cart business logic.
- Form validation is currently performed with Laravel request validation in controllers.
  Extract complex rules into dedicated Form Request classes as the API expands.
- Sanctum bearer tokens are used for a separate Vue client.
- Admin authorization uses a small role middleware. For larger permission matrices,
  adopt a maintained permissions package and explicit policies.
- Checkout locks product/variant rows inside a transaction to reduce overselling.
- Monetary calculations in checkout are performed in integer cents.
- Order item rows snapshot the item name, SKU, unit price, quantity, and line total.
- Images should be uploaded using validated file requests and stored on a durable disk.
- Payment provider callbacks must verify signatures and use idempotency keys before changing
  payment/order state. Do not accept a payment success flag from the browser.
