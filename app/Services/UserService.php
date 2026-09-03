<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Upload\SafeUploader;
use Cafeteria\DTO\CreateUserRequest;
use Cafeteria\DTO\UpdateUserRequest;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Repositories\Contracts\AdminUserRepositoryInterface;
use Cafeteria\Validation\UserValidator;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

final class UserService
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $users,
        private readonly UserValidator $validator,
        private readonly AdminPolicy $policy,
        private readonly SafeUploader $uploader,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function list(
        AuthenticatedUser $admin,
        int $page = 1,
        int $perPage = 15
    ): array {
        $this->authorize($admin);

        return $this->users->paginate($page, $perPage);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(
        AuthenticatedUser $admin,
        int $id
    ): ?array {
        $this->authorize($admin);

        return $this->users->findById($id);
    }

    public function create(
        AuthenticatedUser $admin,
        CreateUserRequest $request
    ): int {
        $this->authorize($admin);

        $errors = $this->validator->validateCreate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $email = strtolower(trim($request->email));

        $attributes = [
            'name' => trim($request->name),
            'email' => $email,
            'password_hash' => password_hash(
                $request->password,
                PASSWORD_DEFAULT
            ),
            'role' => strtoupper(trim($request->role)),
            'room_id' => $request->roomId,
            'extension' => $request->extension !== null
                ? trim($request->extension)
                : null,
            'is_active' => 1,
        ];

        if (
            $request->image !== null
            && $request->image !== []
        ) {
            $filename = $this->uploader->upload($request->image);

            $attributes['profile_image_path'] =
                'storage/uploads/profiles/' . $filename;
        }

        try {
            return $this->users->create($attributes);
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Email already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function update(
        AuthenticatedUser $admin,
        int $id,
        UpdateUserRequest $request
    ): bool {
        $this->authorize($admin);

        $errors = $this->validator->validateUpdate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $user = $this->users->findById($id);

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $attributes = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'role' => strtoupper(trim($request->role)),
            'room_id' => $request->roomId,
            'extension' => $request->extension !== null
                ? trim($request->extension)
                : null,
        ];

        if (
            $request->password !== null
            && $request->password !== ''
        ) {
            $attributes['password_hash'] = password_hash(
                $request->password,
                PASSWORD_DEFAULT
            );
        }

        if (
            $request->image !== null
            && $request->image !== []
        ) {
            $filename = $this->uploader->upload($request->image);

            $attributes['profile_image_path'] =
                'storage/uploads/profiles/' . $filename;
        }

        try {
            return $this->users->update($id, $attributes);
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Email already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function deactivate(
        AuthenticatedUser $admin,
        int $id
    ): bool {
        $this->authorize($admin);

        if ($id === $admin->id()) {
            throw new RuntimeException(
                'You cannot deactivate your own account.'
            );
        }

        $user = $this->users->findById($id);

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $role = strtoupper((string) ($user['role'] ?? ''));

        if (
            $role === 'ADMIN'
            && $this->users->countActiveAdmins() <= 1
        ) {
            throw new RuntimeException(
                'Cannot deactivate the last active admin.'
            );
        }

        return $this->users->deactivate($id);
    }

    private function authorize(
        AuthenticatedUser $admin
    ): void {
        if (!$this->policy->canManageUsers($admin)) {
            throw new RuntimeException('Forbidden.');
        }
    }

    private function isDuplicateKey(
        PDOException $exception
    ): bool {
        return (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}
