<section
    class="cart-summary"
    aria-labelledby="cart-heading"
>
    <div class="cart-summary-head">
        <h2 id="cart-heading" class="cart-summary-title">
            Your order
        </h2>
        <span class="cart-badge" id="cart-count" aria-live="polite">0</span>
    </div>

    <div
        id="cart-items"
        class="cart-items"
        aria-live="polite"
    >
        <p class="cart-empty-copy">
            Your cart is empty.
        </p>
    </div>

    <div class="cart-summary-footer">
        <div class="cart-total-row">
            <span>Total</span>
            <span id="cart-total" class="cart-total-value">0.00</span>
        </div>

        <p class="cart-hint">
            Preview only. Server validates availability and prices at checkout.
        </p>

        <a
            href="/orders/new"
            id="cart-checkout"
            class="btn product-add-btn cart-checkout is-disabled"
            aria-disabled="true"
        >
            Continue to checkout
        </a>
    </div>
</section>
