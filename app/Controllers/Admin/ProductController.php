<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\DTO\CreateProductRequest;
use Cafeteria\DTO\UpdateProductRequest;
use Cafeteria\Repositories\Contracts\CategoryRepositoryInterface;
use Cafeteria\Services\ProductService;
use InvalidArgumentException;
use RuntimeException;

final class ProductController
{
    public function __construct(
        private readonly ProductService $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $result = $this->products->list(
            $admin,
            $page,
            $perPage
        );

        return Response::html(
            $this->render('admin/products/index.php', [
                'products' => $result,
                'csrfToken' => $this->csrf->token(),
                'flash' => $this->flash->pullAll(),
            ])
        );
    }

    public function create(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        return Response::html(
            $this->render('admin/products/form.php', [
                'mode' => 'create',
                'product' => null,
                'categories' => $this->categories->listActive(),
                'errors' => [],
                'csrfToken' => $this->csrf->token(),
            ])
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $this->verifyCsrf($request);

        $dto = new CreateProductRequest(
            (string) $request->input('name', ''),
            (int) $request->input('category_id', 0),
            (string) $request->input('price', ''),
            (bool) $request->input('is_available', false),
            $request->files()['image'] ?? null
        );

        try {
            $this->products->create(
                $admin,
                $dto
            );

            $this->flash->flash(
                'success',
                'Product created successfully.'
            );

            return Response::redirect('/admin/products');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return Response::html(
                $this->render('admin/products/form.php', [
                    'mode' => 'create',
                    'product' => null,
                    'categories' => $this->categories->listActive(),
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ]),
                422
            );
        }
    }

    public function edit(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $product = $this->products->find(
            $admin,
            $id
        );

        if ($product === null) {
            throw new RuntimeException('Product not found.');
        }

        return Response::html(
            $this->render('admin/products/form.php', [
                'mode' => 'edit',
                'product' => $product,
                'categories' => $this->categories->listActive(),
                'errors' => [],
                'csrfToken' => $this->csrf->token(),
            ])
        );
    }

    public function update(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $dto = new UpdateProductRequest(
            (string) $request->input('name', ''),
            (int) $request->input('category_id', 0),
            (string) $request->input('price', ''),
            (bool) $request->input('is_available', false),
            $request->files()['image'] ?? null
        );

        try {
            $this->products->update(
                $admin,
                $id,
                $dto
            );

            $this->flash->flash(
                'success',
                'Product updated successfully.'
            );

            return Response::redirect('/admin/products');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return Response::html(
                $this->render('admin/products/form.php', [
                    'mode' => 'edit',
                    'product' => [
                        'id' => $id,
                        'name' => $request->input('name', ''),
                        'category_id' => $request->input('category_id', 0),
                        'price' => $request->input('price', ''),
                        'is_available' => (bool) $request->input('is_available', false),
                    ],
                    'categories' => $this->categories->listActive(),
                    'errors' => [$exception->getMessage()],
                    'csrfToken' => $this->csrf->token(),
                ]),
                422
            );
        }
    }

    public function deactivate(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        try {
            $this->products->deactivate(
                $admin,
                $id
            );

            $this->flash->flash(
                'success',
                'Product deactivated successfully.'
            );
        } catch (RuntimeException $exception) {
            $this->flash->flash(
                'error',
                $exception->getMessage()
            );
        }

        return Response::redirect('/admin/products');
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

    /**
     * @param array<string, mixed> $data
     */
    private function render(
        string $view,
        array $data = []
    ): string {
        extract($data);

        ob_start();

        require dirname(__DIR__, 3)
            . '/resources/views/'
            . $view;

        return (string) ob_get_clean();
    }
}