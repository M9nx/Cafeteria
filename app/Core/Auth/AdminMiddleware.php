<?php

declare(strict_types=1);

namespace Cafeteria\Core\Auth;

use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\SessionManager;

final class AdminMiddleware
{
    public function __construct(
        private readonly SessionManager $session,
    ) {
    }

    public function __invoke(Request $request): ?Response
    {
        $user = $this->session->get(AuthMiddleware::SESSION_USER_KEY);

        if (!is_array($user)) {
            return Response::redirect('/login');
        }

        $authenticated = AuthenticatedUser::fromSession($user);

        if (!$authenticated->isAdmin()) {
            return Response::html('Forbidden', 403);
        }

        return null;
    }
}
