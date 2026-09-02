<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\CreateProductRequest;
use Cafeteria\DTO\UpdateProductRequest;

final class ProductValidator
{
    /**
     * @return list<string>
     */
    public function validateCreate(
        CreateProductRequest $request
    ): array {
        return $this->validate(
            $request->name,
            $request->categoryId,
            $request->price,
            $request->image
        );
    }

    /**
     * @return list<string>
     */
    public function validateUpdate(
        UpdateProductRequest $request
    ): array {
        return $this->validate(
            $request->name,
            $request->categoryId,
            $request->price,
            $request->image
        );
    }

    /**
     * @param array<string, mixed>|null $image
     *
     * @return list<string>
     */
    private function validate(
        string $name,
        int $categoryId,
        string $price,
        ?array $image
    ): array {
        $errors = [];

        $name = trim($name);

        if ($name === '') {
            $errors[] = 'Product name is required.';
        }

        if (mb_strlen($name) > 150) {
            $errors[] = 'Product name must not exceed 150 characters.';
        }

        if ($categoryId < 1) {
            $errors[] = 'A valid category is required.';
        }

        if (!preg_match('/^\d+\.\d{2}$/', $price)) {
            $errors[] = 'Price must have exactly two decimal places.';
        } elseif ((float) $price <= 0) {
            $errors[] = 'Price must be greater than zero.';
        }

        $this->validateImage($image, $errors);

        return $errors;
    }

    /**
     * @param array<string, mixed>|null $image
     * @param list<string> $errors
     */
    private function validateImage(
        ?array $image,
        array &$errors
    ): void {
        if ($image === null || $image === []) {
            return;
        }

        $error = (int) ($image['error'] ?? UPLOAD_ERR_NO_FILE);

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
        } elseif ($size > 2 * 1024 * 1024) {
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