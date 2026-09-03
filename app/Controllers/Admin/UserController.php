<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\DTO\CreateUserRequest;
use Cafeteria\DTO\UpdateUserRequest;
use Cafeteria\Services\UserService;
use InvalidArgumentException;
use RuntimeException;

final class UserController
{
    use RendersAdminView;

    public function __construct(
        private readonly UserService $users,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $result = $this->users->list(
            $admin,
            $page,
            $perPage
        );

        return $this->renderAdmin(
            $admin,
            'admin.users.index',
            'Admin users',
            [
                'users' => $result,
                'csrfToken' => $this->csrf->token(),
                'flash' => $this->flash->pullAll(),
            ],
        );
    }

    public function create(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        return $this->renderAdmin(
            $admin,
            'admin.users.form',
            'Create admin user',
            [
                'mode' => 'create',
                'user' => null,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $this->verifyCsrf($request);

        $dto = new CreateUserRequest(
            (string) $request->input('name', ''),
            (string) $request->input('email', ''),
            (string) $request->input('role', 'USER'),
            $this->nullableInt($request->input('room_id')),
            $this->nullableString($request->input('extension')),
            (string) $request->input('password', ''),
            $request->input('image')
        );

        try {
            $this->users->create(
                $admin,
                $dto
            );

            $this->flash->flash(
                'success',
                'Admin user created successfully.'
            );

            return Response::redirect('/admin/users');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return $this->renderAdmin(
                $admin,
                'admin.users.form',
                'Create admin user',
                [
                    'mode' => 'create',
                    'user' => null,
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
            );
        }
    }

    public function edit(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $user = $this->users->findById($admin, $id);

        return $this->renderAdmin(
            $admin,
            'admin.users.form',
            'Edit admin user',
            [
                'mode' => 'edit',
                'user' => $user,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function update(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $dto = new UpdateUserRequest(
            (string) $request->input('name', ''),
            (string) $request->input('email', ''),
            (string) $request->input('role', 'USER'),
            $this->nullableInt($request->input('room_id')),
            $this->nullableString($request->input('extension')),
            $this->nullableString($request->input('password')),
            $request->input('image')
        );

        try {
            $this->users->update(
                $admin,
                $id,
                $dto
            );

            $this->flash->flash(
                'success',
                'Admin user updated successfully.'
            );

            return Response::redirect('/admin/users');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return $this->renderAdmin(
                $admin,
                'admin.users.form',
                'Edit admin user',
                [
                    'mode' => 'edit',
                    'user' => [
                        'id' => $id,
                        'name' => $request->input('name', ''),
                        'email' => $request->input('email', ''),
                        'role' => $request->input('role', 'USER'),
                        'room_id' => $request->input('room_id'),
                        'extension' => $request->input('extension'),
                    ],
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
            );
        }
    }

    public function deactivate(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        try {
            $this->users->deactivate(
                $admin,
                $id
            );

            $this->flash->flash(
                'success',
                'Admin user deactivated successfully.'
            );
        } catch (RuntimeException $exception) {
            $this->flash->flash(
                'error',
                $exception->getMessage()
            );
        }

        return Response::redirect('/admin/users');
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(
            CsrfTokenManager::FIELD_NAME
        );

        if (!$this->csrf->validate(
            is_string($token) ? $token : null
        )) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return $value === '' ? null : $value;
    }
}