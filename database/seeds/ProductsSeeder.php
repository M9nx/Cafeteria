<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;
use RuntimeException;

final class ProductsSeeder
{
    /** @var list<array{name:string,category:string,price:string,image:string}> */
    private const PRODUCTS = [
        ['name' => 'Tea', 'category' => 'Hot Drinks', 'price' => '10.00', 'image' => '/assets/images/products/tea.jpg'],
        ['name' => 'Coffee', 'category' => 'Hot Drinks', 'price' => '15.00', 'image' => '/assets/images/products/coffee.jpg'],
        ['name' => 'Hot Chocolate', 'category' => 'Hot Drinks', 'price' => '18.00', 'image' => '/assets/images/products/hot-chocolate.jpg'],
        ['name' => 'Cola', 'category' => 'Cold Drinks', 'price' => '20.00', 'image' => '/assets/images/products/cola.jpg'],
        ['name' => 'Orange Juice', 'category' => 'Cold Drinks', 'price' => '22.00', 'image' => '/assets/images/products/orange-juice.jpg'],
        ['name' => 'Iced Latte', 'category' => 'Cold Drinks', 'price' => '25.00', 'image' => '/assets/images/products/iced-latte.jpg'],
        ['name' => 'Water', 'category' => 'Cold Drinks', 'price' => '8.00', 'image' => '/assets/images/products/water.jpg'],
        ['name' => 'Chips', 'category' => 'Snacks', 'price' => '12.50', 'image' => '/assets/images/products/chips.jpg'],
        ['name' => 'Cookies', 'category' => 'Snacks', 'price' => '14.00', 'image' => '/assets/images/products/cookies.jpg'],
        ['name' => 'Sandwich', 'category' => 'Snacks', 'price' => '35.00', 'image' => '/assets/images/products/sandwich.jpg'],
        ['name' => 'Croissant', 'category' => 'Bakery', 'price' => '16.00', 'image' => '/assets/images/products/croissant.jpg'],
        ['name' => 'Donut', 'category' => 'Bakery', 'price' => '12.00', 'image' => '/assets/images/products/donut.jpg'],
    ];

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'products';
    }

    public function run(): void
    {
        foreach (self::PRODUCTS as $product) {
            $categoryId = $this->categoryId($product['category']);

            $exists = $this->connection->prepare(
                'SELECT id FROM products
                 WHERE category_id = :category_id AND name = :name AND deleted_at IS NULL
                 LIMIT 1'
            );
            $exists->execute([
                'category_id' => $categoryId,
                'name' => $product['name'],
            ]);

            $existingId = $exists->fetchColumn();

            if ($existingId !== false) {
                $update = $this->connection->prepare(
                    'UPDATE products
                     SET image_path = :image_path,
                         price = :price,
                         is_available = 1,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id'
                );
                $update->execute([
                    'image_path' => $product['image'],
                    'price' => $product['price'],
                    'id' => $existingId,
                ]);

                continue;
            }

            $insert = $this->connection->prepare(
                'INSERT INTO products
                    (category_id, name, price, image_path, is_available, deleted_at)
                 VALUES
                    (:category_id, :name, :price, :image_path, 1, NULL)'
            );
            $insert->execute([
                'category_id' => $categoryId,
                'name' => $product['name'],
                'price' => $product['price'],
                'image_path' => $product['image'],
            ]);
        }
    }

    private function categoryId(string $name): int
    {
        $stmt = $this->connection->prepare(
            'SELECT id FROM categories WHERE name = :name LIMIT 1'
        );
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            throw new RuntimeException("Missing category for product seed: {$name}");
        }

        return (int) $id;
    }
}
