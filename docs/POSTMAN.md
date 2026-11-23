# Postman quick test

Base URL: `http://localhost:8080/api/v1`

1. Register: `POST /auth/register`
```json
{
  "name": "Demo Customer",
  "email": "customer@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!"
}
```
2. Copy `data.token` and set Bearer Token authorization.
3. Browse: `GET /products`
4. Add cart item: `POST /cart/items`
```json
{ "product_id": 1, "quantity": 2 }
```
5. Create an address through the database or add the address API before checkout.
6. Checkout: `POST /checkout`
```json
{ "address_id": 1, "payment_method": "cod" }
```

All prices and stock are resolved by the backend. Never submit a trusted total from Postman
or the frontend.
