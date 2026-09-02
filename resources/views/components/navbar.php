<?php

declare(strict_types=1);

/** @var object|null $currentUser Optional user object with isAdmin(): bool */

$isAuthenticated = isset($currentUser);
$isAdmin = $isAuthenticated && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin();
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="/">Cafeteria</a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <?php if ($isAuthenticated): ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/orders/new">New order</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/orders">My orders</a>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/orders">Current queue</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/products">Products</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/users">Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/checks">Reports</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <form action="/logout" method="post" class="d-inline">
                                <?php if (isset($csrfField) && is_string($csrfField)): ?>
                                    <?= $csrfField ?>
                                <?php else: ?>
                                    <!-- CSRF token slot for future auth wiring -->
                                    <input type="hidden" name="_csrf_token" value="">
                                <?php endif; ?>
                                <button type="submit" class="btn btn-outline-light btn-sm">Log out</button>
                            </form>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Log in</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
