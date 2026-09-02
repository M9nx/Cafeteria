<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\PlaceOrderOnBehalfRequest;
use Cafeteria\DTO\PlaceOrderRequest;
use PDO;

final class PlaceOrderOnBehalfValidator
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly PlaceOrderValidator $placeOrderValidator,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function validate(
        PlaceOrderOnBehalfRequest $request
    ): array {
        $errors = [];

        if ($request->userId < 1) {
            $errors['user_id'] = 'Please select a valid customer.';
        } elseif (!$this->customerExistsAndIsActive($request->userId)) {
            $errors['user_id'] =
                'The selected customer does not exist or is inactive.';
        }

        $orderErrors = $this->placeOrderValidator->validate(
            new PlaceOrderRequest(
                roomId: $request->roomId,
                notes: $request->notes,
                items: $request->items,
            )
        );

        foreach ($orderErrors as $key => $message) {
            $errors[$key] = $message;
        }

        if (
            $request->roomId > 0
            && !$this->roomExistsAndIsActive($request->roomId)
        ) {
            $errors['room_id'] = 'Please select a valid active room.';
        }

        return $errors;
    }

    private function customerExistsAndIsActive(int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id
             FROM users
             WHERE id = :id
               AND role = \'USER\'
               AND is_active = 1
             LIMIT 1'
        );

        $statement->execute([
            'id' => $userId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function roomExistsAndIsActive(int $roomId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id
             FROM rooms
             WHERE id = :id
               AND is_active = 1
             LIMIT 1'
        );

        $statement->execute([
            'id' => $roomId,
        ]);

        return $statement->fetchColumn() !== false;
    }
}