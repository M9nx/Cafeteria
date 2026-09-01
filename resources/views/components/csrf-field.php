<?php

declare(strict_types=1);

use Cafeteria\Core\Auth\CsrfTokenManager;

/** @var string|null $csrfToken */

$token = htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8');
?>
<input
    type="hidden"
    name="<?= htmlspecialchars(CsrfTokenManager::FIELD_NAME, ENT_QUOTES, 'UTF-8') ?>"
    value="<?= $token ?>"
>
