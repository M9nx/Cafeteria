<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\DTO\CreateRoomRequest;
use Cafeteria\DTO\UpdateRoomRequest;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Repositories\Contracts\RoomRepositoryInterface;
use Cafeteria\Validation\RoomValidator;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

final class RoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $rooms,
        private readonly RoomValidator $validator,
        private readonly AdminPolicy $policy,
    ) {
    }

    /**
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function list(
        AuthenticatedUser $user,
        int $page = 1,
        int $perPage = 15
    ): array {
        $this->authorize($user);

        return $this->rooms->paginate(
            $page,
            $perPage
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(
        AuthenticatedUser $user,
        int $id
    ): ?array {
        $this->authorize($user);

        return $this->rooms->findById($id);
    }

    public function create(
        AuthenticatedUser $user,
        CreateRoomRequest $request
    ): int {
        $this->authorize($user);

        $errors = $this->validator->validateCreate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $name = trim($request->name);

        try {
            return $this->rooms->create($name);
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Room name already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function update(
        AuthenticatedUser $user,
        int $id,
        UpdateRoomRequest $request
    ): bool {
        $this->authorize($user);

        $errors = $this->validator->validateUpdate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $room = $this->rooms->findById($id);

        if ($room === null) {
            throw new RuntimeException(
                'Room not found.'
            );
        }

        $name = trim($request->name);

        try {
            return $this->rooms->update(
                $id,
                $name,
                $request->isActive
            );
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Room name already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function deactivate(
        AuthenticatedUser $user,
        int $id
    ): bool {
        $this->authorize($user);

        $room = $this->rooms->findById($id);

        if ($room === null) {
            throw new RuntimeException(
                'Room not found.'
            );
        }

        return $this->rooms->deactivate($id);
    }

    private function authorize(
        AuthenticatedUser $user
    ): void {
        if (!$this->policy->canManageRooms($user)) {
            throw new RuntimeException(
                'Forbidden.'
            );
        }
    }

    private function isDuplicateKey(
        PDOException $exception
    ): bool {
        return (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}
