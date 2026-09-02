'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const orderForm = document.getElementById('order-form');
    const orderItems = document.getElementById('order-items');
    const orderTotal = document.getElementById('order-total');

    if (!orderForm || !orderItems || !orderTotal) {
        return;
    }

    const cart = new Map();

    const formatMoney = (value) => {
        return Number(value).toFixed(2);
    };

    const renderCart = () => {
        orderItems.innerHTML = '';

        let total = 0;

        cart.forEach((item, productId) => {
            const quantity = Math.max(1, Number(item.quantity));
            const price = Number(item.price);
            const lineTotal = price * quantity;

            total += lineTotal;

            const wrapper = document.createElement('div');
            wrapper.className = 'card mb-3';

            wrapper.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h6 mb-1">${item.name}</h2>
                            <p class="text-muted small mb-2">
                                ${formatMoney(price)}
                            </p>
                        </div>

                        <strong>
                            ${formatMoney(lineTotal)}
                        </strong>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3">
                        <button
                            type="button"
                            class="btn btn-outline-secondary quantity-decrease"
                            data-product-id="${productId}"
                            aria-label="Decrease ${item.name} quantity"
                        >
                            −
                        </button>

                        <span
                            class="px-2"
                            aria-label="Quantity"
                        >
                            ${quantity}
                        </span>

                        <button
                            type="button"
                            class="btn btn-outline-secondary quantity-increase"
                            data-product-id="${productId}"
                            aria-label="Increase ${item.name} quantity"
                        >
                            +
                        </button>
                    </div>

                    <input
                        type="hidden"
                        name="items[][product_id]"
                        value="${productId}"
                    >

                    <input
                        type="hidden"
                        name="items[][quantity]"
                        value="${quantity}"
                    >
                </div>
            `;

            orderItems.appendChild(wrapper);
        });

        if (cart.size === 0) {
            orderItems.innerHTML = `
                <p class="text-muted mb-0">
                    Your cart is empty.
                </p>
            `;
        }

        orderTotal.textContent = formatMoney(total);
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

    renderCart();
});