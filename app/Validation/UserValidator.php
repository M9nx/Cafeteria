<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\CreateUserRequest;
use Cafeteria\DTO\UpdateUserRequest;

final class UserValidator
{
    /**
     * @return list<string>
     */
    public function validateCreate(CreateUserRequest $request): array
    {
        $errors = [];

        $this->validateCommon(
            $request->name,
            $request->email,
            $request->role,
            $request->roomId,
            $request->extension,
            $errors
        );

        if ($request->password === '') {
            $errors[] = 'Password is required.';
        } elseif (strlen($request->password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        $this->validateImage($request->image, $errors);

        return $errors;
    }

    /**
     * @return list<string>
     */
    public function validateUpdate(UpdateUserRequest $request): array
    {
        $errors = [];

        $this->validateCommon(
            $request->name,
            $request->email,
            $request->role,
            $request->roomId,
            $request->extension,
            $errors
        );

        if ($request->password !== null && $request->password !== '') {
            if (strlen($request->password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
        }

        $this->validateImage($request->image, $errors);

        return $errors;
    }

    /**
     * @param list<string> $errors
     */
    private function validateCommon(
        string $name,
        string $email,
        string $role,
        ?int $roomId,
        ?string $extension,
        array &$errors
    ): void {
        $name = trim($name);
        $email = trim($email);
        $role = strtoupper(trim($role));

        if ($name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($name) > 120) {
            $errors[] = 'Name must not exceed 120 characters.';
        }

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (mb_strlen($email) > 254) {
            $errors[] = 'Email must not exceed 254 characters.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Invalid email address.';
        }

        if (!in_array($role, ['USER', 'ADMIN'], true)) {
            $errors[] = 'Invalid user role.';
        }

        if ($roomId !== null && $roomId <= 0) {
            $errors[] = 'Invalid room.';
        }

        if ($extension !== null) {
            $extension = trim($extension);

            if ($extension === '') {
                $errors[] = 'Extension cannot be empty.';
            } elseif (mb_strlen($extension) > 20) {
                $errors[] = 'Extension must not exceed 20 characters.';
            }
        }
    }

    /**
     * @param array<string, mixed>|null $image
     * @param list<string> $errors
     */
    private function validateImage(?array $image, array &$errors): void
    {
        if ($image === null || $image === []) {
            return;
        }

        $error = $image['error'] ?? UPLOAD_ERR_NO_FILE;

        if (!is_int($error)) {
            $error = (int) $error;
        }

        if ($error === UPLOAD_ERR_NO_FILE) {
            return;
        }

        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
            return;
        }

        $size = (int) ($image['size'] ?? 0);

        if ($size <= 0) {
            $errors[] = 'Invalid image size.';
        }

        if ($size > 2 * 1024 * 1024) {
            $errors[] = 'Image must not exceed 2 MB.';
        }

        $tmpName = (string) ($image['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $errors[] = 'Invalid uploaded image.';
            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($mime, $allowedMimes, true)) {
            $errors[] = 'Unsupported image type.';
        }
    }
}