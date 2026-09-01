<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\CreateCategoryRequest;
use Cafeteria\DTO\UpdateCategoryRequest;

final class CategoryValidator
{
    /**
     * @return list<string>
     */
    public function validateCreate(
        CreateCategoryRequest $request
    ): array {
        $errors = [];

        $name = trim($request->name);

        if ($name === '') {
            $errors[] = 'Category name is required.';
        }

        if (mb_strlen($name) > 120) {
            $errors[] = 'Category name must not exceed 120 characters.';
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    public function validateUpdate(
        UpdateCategoryRequest $request
    ): array {
        $errors = [];

        $name = trim($request->name);

        if ($name === '') {
            $errors[] = 'Category name is required.';
        }

        if (mb_strlen($name) > 120) {
            $errors[] = 'Category name must not exceed 120 characters.';
        }

        return $errors;
    }
}