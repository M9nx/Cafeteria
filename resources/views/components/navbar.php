<?php

declare(strict_types=1);

/** @var object|null $currentUser Optional user object with isAdmin(): bool */

$isAuthenticated = isset($currentUser);
$isAdmin = $isAuthenticated && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin();

$userName = $isAuthenticated && method_exists($currentUser, 'name')
    ? (string) $currentUser->name()
    : '';
$userEmail = $isAuthenticated && method_exists($currentUser, 'email')
    ? (string) $currentUser->email()
    : '';
$userInitials = $userName !== ''
    ? strtoupper(substr($userName, 0, 2))
    : '??';

$profileImagePath = $isAuthenticated && method_exists($currentUser, 'profileImagePath')
    ? $currentUser->profileImagePath()
    : null;
$userAvatarUrl = null;
if (is_string($profileImagePath) && $profileImagePath !== '') {
    $normalizedPath = str_replace('\\', '/', $profileImagePath);
    if (str_starts_with($normalizedPath, 'storage/uploads/profiles/')) {
        $userAvatarUrl = \Cafeteria\Support\PublicFileUrl::fromStoredPath($profileImagePath);
    }
}

$e = static fn (mixed $value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<header class="site-header" data-site-header>
    <nav
        id="mainNavbar"
        class="navbar navbar-expand-lg site-navbar app-navbar"
        aria-label="Main navigation"
    >
        <div class="site-navbar-brand">
            <a class="navbar-brand" href="/" aria-label="Fondo2na Cafeteria home">
                <img
                    class="site-navbar-logo"
                    src="/assets/images/brand/fondo2na-logo.png"
                    alt="Fondo2na Cafeteria"
                    width="48"
                    height="48"
                >
                <span class="site-navbar-brand-text">
                    <span class="site-navbar-title">Fondo2na</span>
                    <span class="site-navbar-tag">Cafeteria</span>
                </span>
            </a>
        </div>

        <button
            class="navbar-toggler site-navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbarCollapse"
            aria-controls="mainNavbarCollapse"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            Menu
        </button>

        <div class="collapse navbar-collapse" id="mainNavbarCollapse">
            <?php if ($isAuthenticated): ?>
                <ul class="navbar-nav site-navbar-links me-auto">
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
                            <a class="nav-link" href="/admin/categories">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/rooms">Rooms</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/checks">Reports</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="site-navbar-actions">
                    <div class="site-navbar-user" title="<?= $e($userEmail) ?>">
                        <?php if ($userAvatarUrl !== null): ?>
                            <span class="site-navbar-avatar site-navbar-avatar--image" aria-hidden="true">
                                <img
                                    src="<?= $e($userAvatarUrl) ?>"
                                    alt=""
                                    width="28"
                                    height="28"
                                >
                            </span>
                        <?php else: ?>
                            <span class="site-navbar-avatar" aria-hidden="true"><?= $e($userInitials) ?></span>
                        <?php endif; ?>
                        <span class="site-navbar-user-name"><?= $e($userName) ?></span>
                    </div>

                    <form
                        action="/logout"
                        method="post"
                        class="site-navbar-logout"
                        data-confirm="Are you sure you want to log out?"
                        data-confirm-title="Log out"
                        data-confirm-label="Log out"
                        data-confirm-tone="danger"
                    >
                        <?php if (isset($csrfField) && is_string($csrfField)): ?>
                            <?= $csrfField ?>
                        <?php else: ?>
                            <input type="hidden" name="_csrf_token" value="">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-sm site-navbar-logout-btn">
                            Log out
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <ul class="navbar-nav ms-auto site-navbar-links">
                    <li class="nav-item">
                        <a class="nav-link site-navbar-cta" href="/login">Log in</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
</header>
