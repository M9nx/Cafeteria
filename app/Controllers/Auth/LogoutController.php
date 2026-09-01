<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Services\AuthService;

final class LogoutController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function logout(Request $request): Response
    {
        if (!$this->csrf->validate(
            $request->input(CsrfTokenManager::FIELD_NAME)
        )) {
            return Response::html('Invalid CSRF token.', 403);
        }

        $this->auth->logout();

        return Response::redirect('/login');
    }
}