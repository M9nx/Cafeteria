<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\AuthMiddleware;
use Cafeteria\Core\Session\SessionManager;
use Cafeteria\Repositories\Contracts\AuthUserRepositoryInterface;
use Cafeteria\Domain\Users\User;
use RuntimeException;

final class AuthService
{
    public function __construct(
        private readonly AuthUserRepositoryInterface $users,
        private readonly SessionManager $session,
    ) {
    }

    public function login(string $email, string $password): bool
    {
        $email = strtolower(trim($email));

        $record = $this->users->findActiveByEmail($email);

        if (
            $record === null
            || !isset($record['user'], $record['password_hash'])
            || !$record['user'] instanceof User
            || !is_string($record['password_hash'])
            || !password_verify($password, $record['password_hash'])
        ) {
            throw new RuntimeException('Invalid email or password.');
        }

        $user = $record['user'];

        $this->session->regenerate(true);

        $authenticatedUser = new AuthenticatedUser(
            id: $user->id,
            email: $user->email,
            name: $user->name,
            role: $user->role,
            profileImagePath: $user->profileImagePath,
        );

        $this->remember($authenticatedUser);

        return true;
    }

    public function remember(AuthenticatedUser $user): void
    {
        $this->session->set(
            AuthMiddleware::SESSION_USER_KEY,
            $user->toSessionArray(),
        );
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    public function currentUser(): ?AuthenticatedUser
    {
        $user = $this->session->get(
            AuthMiddleware::SESSION_USER_KEY
        );

        if (!is_array($user)) {
            return null;
        }

        return AuthenticatedUser::fromSession($user);
    }
}