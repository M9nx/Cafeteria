<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $cartItems
 */

$cartItems = $cartItems ?? [];

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<section
    class="card cart-summary"
    aria-labelledby="cart-heading"
>
    <div class="card-body">
        <h2 id="cart-heading" class="h5 card-title">
            Your order
        </h2>

        <div
            id="cart-items"
            class="mb-3"
            aria-live="polite"
        >
            <p class="text-muted mb-0">
                Your cart is empty.
            </p>
        </div>

        <div class="border-top pt-3">
            <div class="d-flex justify-content-between fw-semibold">
                <span>Total</span>
                <span id="cart-total">0.00</span>
            </div>
        </div>
    </div>
</section>