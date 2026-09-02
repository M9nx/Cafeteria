<?php

declare(strict_types=1);

namespace Cafeteria\DTO;

final readonly class PlaceOrderRequest
{
    /**
     * @param list<OrderItemInput> $items
     */
    public function __construct(
        public int $roomId,
        public ?string $notes,
        public array $items,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $rawItems = $data['items'] ?? [];

        if (!is_array($rawItems)) {
            $rawItems = [];
        }

        $items = [];

        foreach ($rawItems as $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $items[] = OrderItemInput::fromArray($rawItem);
        }

        $notes = $data['notes'] ?? null;

        if (is_string($notes)) {
            $notes = trim($notes);
            $notes = $notes === '' ? null : $notes;
        } else {
            $notes = null;
        }

        return new self(
            roomId: (int) ($data['room_id'] ?? $data['roomId'] ?? 0),
            notes: $notes,
            items: $items,
        );
    }
}
