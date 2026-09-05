<?php

declare(strict_types=1);

namespace Cafeteria\Support;

/**
 * Maps demo seed names to static Unsplash-backed assets under public/assets/images.
 * Used for graduation/demo UI when categories and rooms have no image_path column.
 */
final class DemoImageMap
{
    /** @var array<string, string> */
    private const CATEGORIES = [
        'Hot Drinks' => '/assets/images/categories/hot-drinks.jpg',
        'Cold Drinks' => '/assets/images/categories/cold-drinks.jpg',
        'Snacks' => '/assets/images/categories/snacks.jpg',
        'Bakery' => '/assets/images/categories/bakery.jpg',
    ];

    /** @var array<string, string> */
    private const ROOMS = [
        'Room 101' => '/assets/images/rooms/room-101.jpg',
        'Room 102' => '/assets/images/rooms/room-102.jpg',
        'Reception' => '/assets/images/rooms/reception.jpg',
        'Conference Hall' => '/assets/images/rooms/conference-hall.jpg',
    ];

    public static function category(?string $name): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }

        return self::CATEGORIES[$name] ?? null;
    }

    public static function room(?string $name): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }

        return self::ROOMS[$name] ?? null;
    }
}
