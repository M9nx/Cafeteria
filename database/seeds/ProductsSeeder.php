<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;
use RuntimeException;

final class ProductsSeeder
{
    /** @var list<array{name:string,category:string,price:string,image:?string}> */
    private const PRODUCTS = [
        ['name' => 'Tea', 'category' => 'Hot Drinks', 'price' => '10.00', 'image' => '/assets/images/products/placeholder.svg'],
        ['name' => 'Coffee', 'category' => 'Hot Drinks', 'price' => '15.00', 'image' => '/assets/images/products/placeholder.svg'],
        ['name' => 'Cola', 'category' => 'Cold Drinks', 'price' => '20.00', 'image' => '/assets/images/products/placeholder.svg'],
        ['name' => 'Chips', 'category' => 'Snacks', 'price' => '12.50', 'image' => '/assets/images/products/placeholder.svg'],
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

            if ($exists->fetchColumn() !== false) {
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
