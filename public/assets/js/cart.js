'use strict';

const CART_STORAGE_KEY = 'cafeteria.cart';

document.addEventListener('DOMContentLoaded', () => {
    if (new URLSearchParams(window.location.search).get('ordered') === '1') {
        sessionStorage.removeItem(CART_STORAGE_KEY);
    }

    const orderForm = document.getElementById('order-form');
    const orderItems = document.getElementById('order-items')
        ?? document.getElementById('cart-items');
    const orderTotal = document.getElementById('order-total')
        ?? document.getElementById('cart-total');
    const cartCount = document.getElementById('cart-count');
    const orderItemCount = document.getElementById('order-item-count');
    const checkoutButton = document.getElementById('cart-checkout');

    if (!orderItems || !orderTotal) {
        return;
    }

    const cart = loadCart();

    const formatMoney = (value) => Number(value).toFixed(2);

    const saveCart = () => {
        const payload = {};

        cart.forEach((item, productId) => {
            payload[String(productId)] = item;
        });

        sessionStorage.setItem(CART_STORAGE_KEY, JSON.stringify(payload));
    };

    const updateCount = () => {
        let count = 0;
        cart.forEach((item) => {
            count += Math.max(0, Number(item.quantity) || 0);
        });

        if (cartCount instanceof HTMLElement) {
            cartCount.textContent = String(count);
        }

        if (orderItemCount instanceof HTMLElement) {
            orderItemCount.textContent = `${count} item${count === 1 ? '' : 's'}`;
        }

        if (checkoutButton instanceof HTMLElement) {
            const empty = count === 0;
            checkoutButton.classList.toggle('is-disabled', empty);
            checkoutButton.setAttribute('aria-disabled', empty ? 'true' : 'false');
        }
    };

    const renderCart = () => {
        orderItems.replaceChildren();

        let total = 0;
        let itemIndex = 0;

        cart.forEach((item, productId) => {
            const quantity = Math.max(1, Number(item.quantity));
            const price = Number(item.price);
            const lineTotal = price * quantity;

            total += lineTotal;

            const wrapper = document.createElement('div');
            wrapper.className = orderForm ? 'order-line' : 'cart-line';

            if (orderForm) {
                const top = document.createElement('div');
                top.className = 'order-line-top';

                const details = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'order-line-name';
                title.textContent = item.name;

                const unitPrice = document.createElement('p');
                unitPrice.className = 'order-line-meta';
                unitPrice.textContent = `${formatMoney(price)} × ${quantity}`;

                details.append(title, unitPrice);

                const lineTotalEl = document.createElement('span');
                lineTotalEl.className = 'order-line-total';
                lineTotalEl.textContent = formatMoney(lineTotal);

                top.append(details, lineTotalEl);

                const controls = document.createElement('div');
                controls.className = 'order-line-controls';

                const decreaseButton = document.createElement('button');
                decreaseButton.type = 'button';
                decreaseButton.className = 'btn btn-outline-secondary quantity-decrease';
                decreaseButton.dataset.productId = String(productId);
                decreaseButton.setAttribute('aria-label', `Decrease ${item.name} quantity`);
                decreaseButton.textContent = '−';

                const quantityLabel = document.createElement('span');
                quantityLabel.setAttribute('aria-label', 'Quantity');
                quantityLabel.textContent = String(quantity);

                const increaseButton = document.createElement('button');
                increaseButton.type = 'button';
                increaseButton.className = 'btn btn-outline-secondary quantity-increase';
                increaseButton.dataset.productId = String(productId);
                increaseButton.setAttribute('aria-label', `Increase ${item.name} quantity`);
                increaseButton.textContent = '+';

                controls.append(decreaseButton, quantityLabel, increaseButton);

                const productInput = document.createElement('input');
                productInput.type = 'hidden';
                productInput.name = `items[${itemIndex}][product_id]`;
                productInput.value = String(productId);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${itemIndex}][quantity]`;
                quantityInput.value = String(quantity);

                wrapper.append(top, controls, productInput, quantityInput);
                itemIndex += 1;
            } else {
                const details = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'fw-semibold mb-0';
                title.textContent = item.name;

                const meta = document.createElement('p');
                meta.className = 'text-muted small mb-0';
                meta.textContent = `${formatMoney(price)} × ${quantity}`;

                const controls = document.createElement('div');
                controls.className = 'cart-line-controls';

                const decreaseButton = document.createElement('button');
                decreaseButton.type = 'button';
                decreaseButton.className = 'btn btn-outline-secondary btn-sm quantity-decrease';
                decreaseButton.dataset.productId = String(productId);
                decreaseButton.setAttribute('aria-label', `Decrease ${item.name} quantity`);
                decreaseButton.textContent = '−';

                const quantityLabel = document.createElement('span');
                quantityLabel.textContent = String(quantity);

                const increaseButton = document.createElement('button');
                increaseButton.type = 'button';
                increaseButton.className = 'btn btn-outline-secondary btn-sm quantity-increase';
                increaseButton.dataset.productId = String(productId);
                increaseButton.setAttribute('aria-label', `Increase ${item.name} quantity`);
                increaseButton.textContent = '+';

                controls.append(decreaseButton, quantityLabel, increaseButton);
                details.append(title, meta, controls);

                const lineTotalEl = document.createElement('strong');
                lineTotalEl.textContent = formatMoney(lineTotal);

                wrapper.append(details, lineTotalEl);
            }

            orderItems.append(wrapper);
        });

        if (cart.size === 0) {
            if (orderForm) {
                const empty = document.createElement('div');
                empty.className = 'order-items-empty';
                empty.innerHTML = '<p class="order-items-empty-title">Your cart is empty.</p><p class="order-items-empty-copy">Click “Add to order” on any product to begin.</p>';
                orderItems.append(empty);
            } else {
                const emptyMessage = document.createElement('p');
                emptyMessage.className = 'cart-empty-copy';
                emptyMessage.textContent = 'Your cart is empty.';
                orderItems.append(emptyMessage);
            }
        }

        orderTotal.textContent = formatMoney(total);
        updateCount();
        saveCart();
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.add-to-cart');

        if (!button) {
            return;
        }

        const card = button.closest('.product-card');

        if (!card) {
            return;
        }

        const productId = Number(card.dataset.productId);
        const name = card.dataset.productName || '';
        const price = Number(card.dataset.productPrice);

        if (!Number.isInteger(productId) || productId <= 0) {
            return;
        }

        if (!Number.isFinite(price) || price < 0) {
            return;
        }

        const existing = cart.get(productId);

        if (existing) {
            existing.quantity += 1;
        } else {
            cart.set(productId, {
                name,
                price,
                quantity: 1,
            });
        }

        renderCart();

        const addLabel = button.getAttribute('data-add-label') || 'Add to cart';
        button.classList.add('btn-success');
        button.textContent = 'Added';
        window.setTimeout(() => {
            button.classList.remove('btn-success');
            if (button.getAttribute('data-add-label')) {
                button.innerHTML = `<span aria-hidden="true">+</span><span>${addLabel}</span>`;
            } else {
                button.textContent = addLabel;
            }
        }, 900);
    });

    document.addEventListener('click', (event) => {
        const increaseButton = event.target.closest('.quantity-increase');
        const decreaseButton = event.target.closest('.quantity-decrease');

        if (!increaseButton && !decreaseButton) {
            return;
        }

        const button = increaseButton || decreaseButton;
        const productId = Number(button.dataset.productId);
        const item = cart.get(productId);

        if (!item) {
            return;
        }

        if (increaseButton) {
            item.quantity += 1;
        }

        if (decreaseButton) {
            item.quantity -= 1;

            if (item.quantity <= 0) {
                cart.delete(productId);
            }
        }

        renderCart();
    });

    if (orderForm) {
        orderForm.addEventListener('submit', (event) => {
            if (cart.size === 0) {
                event.preventDefault();
                orderItems.scrollIntoView({ behavior: 'smooth', block: 'center' });

                let notice = document.getElementById('cart-empty-error');

                if (!(notice instanceof HTMLElement)) {
                    notice = document.createElement('div');
                    notice.id = 'cart-empty-error';
                    notice.className = 'app-flash app-flash-danger alert alert-danger mt-3';
                    notice.setAttribute('role', 'alert');
                    notice.textContent = 'Add at least one item before placing an order.';
                    orderForm.prepend(notice);
                }
            }
        });
    }

    renderCart();
});

function loadCart() {
    try {
        const raw = sessionStorage.getItem(CART_STORAGE_KEY);

        if (!raw) {
            return new Map();
        }

        const parsed = JSON.parse(raw);

        if (typeof parsed !== 'object' || parsed === null) {
            return new Map();
        }

        const cart = new Map();

        Object.entries(parsed).forEach(([productId, item]) => {
            if (typeof item !== 'object' || item === null) {
                return;
            }

            cart.set(Number(productId), item);
        });

        return cart;
    } catch {
        return new Map();
    }
}
