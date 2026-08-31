<?php

declare(strict_types=1);

namespace Cafeteria\Core\Auth;

use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\SessionManager;

final class AuthMiddleware
{
    public const SESSION_USER_KEY = 'auth.user';

    public const SESSION_INTENDED_KEY = 'auth.intended';

    public function __construct(
        private readonly SessionManager $session,
    ) {
    }

    public function __invoke(Request $request): ?Response
    {
        $user = $this->session->get(self::SESSION_USER_KEY);

        if (is_array($user) && isset($user['id'])) {
            return null;
        }

        $path = $request->path();

        if ($this->isSafeInternalPath($path)) {
            $this->session->set(self::SESSION_INTENDED_KEY, $path);
        }

        return Response::redirect('/login');
    }

    public function currentUser(): ?AuthenticatedUser
    {
        $user = $this->session->get(self::SESSION_USER_KEY);

        if (!is_array($user)) {
            return null;
        }

        return AuthenticatedUser::fromSession($user);
    }

    private function isSafeInternalPath(string $path): bool
    {
        if ($path === '' || !str_starts_with($path, '/')) {
            return false;
        }

        if (str_starts_with($path, '//')) {
            return false;
        }

        return !str_contains($path, '://');
    }
}
