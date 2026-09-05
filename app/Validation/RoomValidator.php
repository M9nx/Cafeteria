<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\CreateRoomRequest;
use Cafeteria\DTO\UpdateRoomRequest;

final class RoomValidator
{
    /**
     * @return list<string>
     */
    public function validateCreate(
        CreateRoomRequest $request
    ): array {
        return $this->validateName($request->name);
    }

    /**
     * @return list<string>
     */
    public function validateUpdate(
        UpdateRoomRequest $request
    ): array {
        return $this->validateName($request->name);
    }

    /**
     * @return list<string>
     */
    private function validateName(string $name): array
    {
        $errors = [];
        $name = trim($name);

        if ($name === '') {
            $errors[] = 'Room name is required.';
        }

        if (mb_strlen($name) > 100) {
            $errors[] = 'Room name must not exceed 100 characters.';
        }

        return $errors;
    }
}
