# Manual UI Checklist — Catalogue and Order Flow

## Catalogue

- [ ] Available products are displayed as responsive Bootstrap cards.
- [ ] Each product card shows the product name and price.
- [ ] Product category is shown when available.
- [ ] Product image is shown when an image path is available.
- [ ] Clicking "Add to cart" adds the product with quantity 1.
- [ ] Adding the same product again increases its quantity.

## Cart

- [ ] Cart starts empty.
- [ ] Increasing quantity updates the displayed quantity.
- [ ] Decreasing quantity updates the displayed quantity.
- [ ] Decreasing quantity from 1 removes the item.
- [ ] Each cart item shows its line total.
- [ ] The displayed order total updates after every cart change.
- [ ] Cart remains usable on mobile screen widths.

## Order form

- [ ] Delivery room is required.
- [ ] Notes are optional.
- [ ] Selected cart items are submitted with product IDs and quantities.
- [ ] The order total shown in the browser is clearly a preview.
- [ ] The form can be submitted using keyboard navigation.
- [ ] Form controls have visible labels and accessible names.

## Validation and security

- [ ] Empty cart cannot be submitted as a valid order.
- [ ] Server-side validation remains authoritative for products, quantities, room, and prices.
- [ ] Dynamic product and form values are escaped in the rendered HTML.
- [ ] No repository or SQL access is performed by the views.
- [ ] CSRF protection is included in the order form.

## Responsive checks

- [ ] Catalogue cards remain readable on mobile.
- [ ] Cart/order summary does not obstruct the form on mobile.
- [ ] Buttons remain reachable and usable on small screens.
- [ ] Keyboard focus remains visible.