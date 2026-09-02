<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Upload\SafeUploader;
use Cafeteria\DTO\CreateProductRequest;
use Cafeteria\DTO\UpdateProductRequest;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Repositories\Contracts\CategoryRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Validation\ProductValidator;
use InvalidArgumentException;
use RuntimeException;

final class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly ProductValidator $validator,
        private readonly AdminPolicy $policy,
        private readonly SafeUploader $uploader,
    ) {
    }

    public function list(
        AuthenticatedUser $user,
        int $page = 1,
        int $perPage = 15
    ): array {
        $this->authorize($user);

        return $this->products->paginate($page, $perPage);
    }

    public function find(
        AuthenticatedUser $user,
        int $id
    ): ?array {
        $this->authorize($user);

        return $this->products->findById($id);
    }

    public function create(
        AuthenticatedUser $user,
        CreateProductRequest $request
    ): int {
        $this->authorize($user);

        $errors = $this->validator->validateCreate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $this->validateCategory($request->categoryId);

        $attributes = [
            'category_id' => $request->categoryId,
            'name' => trim($request->name),
            'price' => $request->price,
            'is_available' => $request->isAvailable,
        ];

        if ($request->image !== null && $request->image !== []) {
            $filename = $this->uploader->upload($request->image);

            $attributes['image_path'] =
                'storage/uploads/products/' . $filename;
        }

        return $this->products->create($attributes);
    }

    public function update(
        AuthenticatedUser $user,
        int $id,
        UpdateProductRequest $request
    ): bool {
        $this->authorize($user);

        $product = $this->products->findById($id);

        if ($product === null || $product['deleted_at'] !== null) {
            throw new RuntimeException('Product not found.');
        }

        $errors = $this->validator->validateUpdate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $this->validateCategory($request->categoryId);

        $attributes = [
            'category_id' => $request->categoryId,
            'name' => trim($request->name),
            'price' => $request->price,
            'is_available' => $request->isAvailable,
        ];

        if ($request->image !== null && $request->image !== []) {
            $filename = $this->uploader->upload($request->image);

            $attributes['image_path'] =
                'storage/uploads/products/' . $filename;
        }

        return $this->products->update($id, $attributes);
    }

    public function setAvailability(
        AuthenticatedUser $user,
        int $id,
        bool $isAvailable
    ): bool {
        $this->authorize($user);

        $product = $this->products->findById($id);

        if ($product === null || $product['deleted_at'] !== null) {
            throw new RuntimeException('Product not found.');
        }

        return $this->products->update(
            $id,
            ['is_available' => $isAvailable]
        );
    }

    public function deactivate(
        AuthenticatedUser $user,
        int $id
    ): bool {
        $this->authorize($user);

        $product = $this->products->findById($id);

        if ($product === null || $product['deleted_at'] !== null) {
            throw new RuntimeException('Product not found.');
        }

        return $this->products->softDelete($id);
    }

    private function authorize(AuthenticatedUser $user): void
    {
        if (!$this->policy->canManageProducts($user)) {
            throw new RuntimeException('Forbidden.');
        }
    }

    private function validateCategory(int $categoryId): void
    {
        $category = $this->categories->findById($categoryId);

        if ($category === null) {
            throw new InvalidArgumentException(
                'Selected category does not exist.'
            );
        }

        if (isset($category['is_active']) && !$category['is_active']) {
            throw new InvalidArgumentException(
                'Selected category is inactive.'
            );
        }
    }
}