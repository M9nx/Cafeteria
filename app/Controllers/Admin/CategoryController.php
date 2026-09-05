<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\DTO\CreateCategoryRequest;
use Cafeteria\DTO\UpdateCategoryRequest;
use Cafeteria\Services\CategoryService;
use InvalidArgumentException;
use RuntimeException;

final class CategoryController
{
    use RendersAdminView;

    public function __construct(
        private readonly CategoryService $categories,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $result = $this->categories->list(
            $user,
            $page,
            $perPage
        );

        return $this->renderAdmin(
            $user,
            'admin.categories.index',
            'Categories',
            [
                'categories' => $result,
                'csrfToken' => $this->csrf->token(),
                'flash' => $this->flash->pullAll(),
            ],
        );
    }

    public function create(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        return $this->renderAdmin(
            $user,
            'admin.categories.form',
            'Create category',
            [
                'mode' => 'create',
                'category' => null,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $this->verifyCsrf($request);

        $dto = new CreateCategoryRequest(
            (string) $request->input('name', '')
        );

        try {
            $this->categories->create($user, $dto);

            $this->flash->flash(
                'success',
                'Category created successfully.'
            );

            return Response::redirect('/admin/categories');
        } catch (InvalidArgumentException $exception) {
            return $this->renderAdmin(
                $user,
                'admin.categories.form',
                'Create category',
                [
                    'mode' => 'create',
                    'category' => null,
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
            );
        }
    }

    public function edit(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $category = $this->categories->find($user, $id);

        if ($category === null) {
            throw new RuntimeException('Category not found.');
        }

        return $this->renderAdmin(
            $user,
            'admin.categories.form',
            'Edit category',
            [
                'mode' => 'edit',
                'category' => $category,
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function update(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $dto = new UpdateCategoryRequest(
            (string) $request->input('name', ''),
            $request->input('is_active') === '1'
            || $request->input('is_active') === 1
            || $request->input('is_active') === true
        );

        try {
            $this->categories->update(
                $user,
                $id,
                $dto
            );

            $this->flash->flash(
                'success',
                'Category updated successfully.'
            );

            return Response::redirect('/admin/categories');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return $this->renderAdmin(
                $user,
                'admin.categories.form',
                'Edit category',
                [
                    'mode' => 'edit',
                    'category' => [
                        'id' => $id,
                        'name' => (string) $request->input('name', ''),
                        'is_active' => $request->input('is_active') === '1'
                            || $request->input('is_active') === 1
                            || $request->input('is_active') === true,
                    ],
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
            );
        }
    }

    public function deactivate(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        try {
            $this->categories->deactivate(
                $user,
                $id
            );

            $this->flash->flash(
                'success',
                'Category deactivated successfully.'
            );
        } catch (RuntimeException $exception) {
            $this->flash->flash(
                'error',
                $exception->getMessage()
            );
        }

        return Response::redirect('/admin/categories');
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(
            CsrfTokenManager::FIELD_NAME
        );

        if (!$this->csrf->validate(
            is_string($token) ? $token : null
        )) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }
}
