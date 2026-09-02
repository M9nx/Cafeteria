<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\PlaceOrderRequest;

final class PlaceOrderValidator
{
    private const MAX_NOTES_LENGTH = 1000;

    /**
     * @return array<string, string>
     */
    public function validate(PlaceOrderRequest $request): array
    {
        $errors = [];

        if ($request->roomId < 1) {
            $errors['room_id'] = 'Please select a valid room.';
        }

        if ($request->items === []) {
            $errors['items'] = 'Your cart must contain at least one item.';
        }

        if (
            $request->notes !== null
            && mb_strlen($request->notes) > self::MAX_NOTES_LENGTH
        ) {
            $errors['notes'] = sprintf(
                'Notes must not exceed %d characters.',
                self::MAX_NOTES_LENGTH
            );
        }

        foreach ($request->items as $index => $item) {
            if ($item->productId < 1) {
                $errors["items.{$index}.product_id"] =
                    'Each cart item must reference a product.';
            }

            if ($item->quantity < 1) {
                $errors["items.{$index}.quantity"] =
                    'Quantity must be at least 1.';
            }
        }

        return $errors;
    }
}
