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
            wrapper.className = 'card mb-3';

            const body = document.createElement('div');
            body.className = 'card-body';

            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-start gap-3';

            const details = document.createElement('div');
            const title = document.createElement('h2');
            title.className = 'h6 mb-1';
            title.textContent = item.name;

            const unitPrice = document.createElement('p');
            unitPrice.className = 'text-muted small mb-2';
            unitPrice.textContent = formatMoney(price);

            details.append(title, unitPrice);

            const lineTotalEl = document.createElement('strong');
            lineTotalEl.textContent = formatMoney(lineTotal);

            header.append(details, lineTotalEl);

            const controls = document.createElement('div');
            controls.className = 'd-flex align-items-center gap-2 mt-3';

            const decreaseButton = document.createElement('button');
            decreaseButton.type = 'button';
            decreaseButton.className = 'btn btn-outline-secondary quantity-decrease';
            decreaseButton.dataset.productId = String(productId);
            decreaseButton.setAttribute('aria-label', `Decrease ${item.name} quantity`);
            decreaseButton.textContent = '−';

            const quantityLabel = document.createElement('span');
            quantityLabel.className = 'px-2';
            quantityLabel.setAttribute('aria-label', 'Quantity');
            quantityLabel.textContent = String(quantity);

            const increaseButton = document.createElement('button');
            increaseButton.type = 'button';
            increaseButton.className = 'btn btn-outline-secondary quantity-increase';
            increaseButton.dataset.productId = String(productId);
            increaseButton.setAttribute('aria-label', `Increase ${item.name} quantity`);
            increaseButton.textContent = '+';

            controls.append(decreaseButton, quantityLabel, increaseButton);

            body.append(header, controls);

            if (orderForm) {
                const productInput = document.createElement('input');
                productInput.type = 'hidden';
                productInput.name = `items[${itemIndex}][product_id]`;
                productInput.value = String(productId);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${itemIndex}][quantity]`;
                quantityInput.value = String(quantity);

                body.append(productInput, quantityInput);
                itemIndex += 1;
            }

            wrapper.append(body);
            orderItems.append(wrapper);
        });

        if (cart.size === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'text-muted mb-0';
            emptyMessage.textContent = 'Your cart is empty.';
            orderItems.append(emptyMessage);
        }

        orderTotal.textContent = formatMoney(total);
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
