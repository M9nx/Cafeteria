<?php
declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\DTO\CreateCategoryRequest;
use Cafeteria\DTO\UpdateCategoryRequest;
use Cafeteria\Policies\AdminPolicy;
use Cafeteria\Repositories\Contracts\CategoryRepositoryInterface;
use Cafeteria\Validation\CategoryValidator;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly CategoryValidator $validator,
        private readonly AdminPolicy $policy,
    ) {
    }

    /**
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function list(
        AuthenticatedUser $user,
        int $page = 1,
        int $perPage = 15
    ): array {
        $this->authorize($user);

        return $this->categories->paginate(
            $page,
            $perPage
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(
        AuthenticatedUser $user,
        int $id
    ): ?array {
        $this->authorize($user);

        return $this->categories->findById($id);
    }

    public function create(
        AuthenticatedUser $user,
        CreateCategoryRequest $request
    ): int {
        $this->authorize($user);

        $errors = $this->validator->validateCreate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $name = trim($request->name);

        try {
            return $this->categories->create($name);
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Category name already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function update(
        AuthenticatedUser $user,
        int $id,
        UpdateCategoryRequest $request
    ): bool {
        $this->authorize($user);

        $errors = $this->validator->validateUpdate($request);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $category = $this->categories->findById($id);

        if ($category === null) {
            throw new RuntimeException(
                'Category not found.'
            );
        }

        $name = trim($request->name);

        try {
            return $this->categories->update(
                $id,
                $name,
                $request->isActive
            );
        } catch (PDOException $exception) {
            if ($this->isDuplicateKey($exception)) {
                throw new InvalidArgumentException(
                    'Category name already exists.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public function deactivate(
        AuthenticatedUser $user,
        int $id
    ): bool {
        $this->authorize($user);

        $category = $this->categories->findById($id);

        if ($category === null) {
            throw new RuntimeException(
                'Category not found.'
            );
        }

        return $this->categories->deactivate($id);
    }

    private function authorize(
        AuthenticatedUser $user
    ): void {
        if (!$this->policy->canManageCategories($user)) {
            throw new RuntimeException(
                'Forbidden.'
            );
        }
    }

    private function isDuplicateKey(
        PDOException $exception
    ): bool {
        return (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}

