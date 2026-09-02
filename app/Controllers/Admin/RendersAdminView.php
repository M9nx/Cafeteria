<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\View\View;

trait RendersAdminView
{
    /**
     * @param array<string, mixed> $data
     */
    protected function renderAdmin(
        AuthenticatedUser $user,
        string $template,
        string $title,
        array $data = [],
    ): Response {
        return View::render(
            $template,
            array_merge($data, [
                'title' => $title,
                'currentUser' => $user,
            ]),
            'layouts.app',
        );
    }
}
