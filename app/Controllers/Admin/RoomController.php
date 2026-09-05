<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\DTO\CreateRoomRequest;
use Cafeteria\DTO\UpdateRoomRequest;
use Cafeteria\Services\RoomService;
use InvalidArgumentException;
use RuntimeException;

final class RoomController
{
    use RendersAdminView;

    public function __construct(
        private readonly RoomService $rooms,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $result = $this->rooms->list(
            $user,
            $page,
            $perPage
        );

        return $this->renderAdmin(
            $user,
            'admin.rooms.index',
            'Rooms',
            [
                'rooms' => $result,
                'csrfToken' => $this->csrf->token(),
                'flash' => $this->flash->pullAll(),
            ],
        );
    }

    public function create(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        return $this->renderAdmin(
            $user,
            'admin.rooms.form',
            'Create room',
            [
                'mode' => 'create',
                'room' => null,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $this->verifyCsrf($request);

        $dto = new CreateRoomRequest(
            (string) $request->input('name', '')
        );

        try {
            $this->rooms->create($user, $dto);

            $this->flash->flash(
                'success',
                'Room created successfully.'
            );

            return Response::redirect('/admin/rooms');
        } catch (InvalidArgumentException $exception) {
            return $this->renderAdmin(
                $user,
                'admin.rooms.form',
                'Create room',
                [
                    'mode' => 'create',
                    'room' => null,
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
            );
        }
    }

    public function edit(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $room = $this->rooms->find($user, $id);

        if ($room === null) {
            throw new RuntimeException('Room not found.');
        }

        return $this->renderAdmin(
            $user,
            'admin.rooms.form',
            'Edit room',
            [
                'mode' => 'edit',
                'room' => $room,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function update(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $dto = new UpdateRoomRequest(
            (string) $request->input('name', ''),
            $request->input('is_active') === '1'
            || $request->input('is_active') === 1
            || $request->input('is_active') === true
        );

        try {
            $this->rooms->update(
                $user,
                $id,
                $dto
            );

            $this->flash->flash(
                'success',
                'Room updated successfully.'
            );

            return Response::redirect('/admin/rooms');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return $this->renderAdmin(
                $user,
                'admin.rooms.form',
                'Edit room',
                [
                    'mode' => 'edit',
                    'room' => [
                        'id' => $id,
                        'name' => (string) $request->input('name', ''),
                        'is_active' => $request->input('is_active') === '1'
                            || $request->input('is_active') === 1
                            || $request->input('is_active') === true,
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
        AuthenticatedUser $user,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        try {
            $this->rooms->deactivate(
                $user,
                $id
            );

            $this->flash->flash(
                'success',
                'Room deactivated successfully.'
            );
        } catch (RuntimeException $exception) {
            $this->flash->flash(
                'error',
                $exception->getMessage()
            );
        }

        return Response::redirect('/admin/rooms');
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
}
