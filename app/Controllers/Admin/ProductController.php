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
    use RendersAdminView;

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

        return $this->renderAdmin(
            $admin,
            'admin.products.index',
            'Products',
            [
                'products' => $result,
                'csrfToken' => $this->csrf->token(),
                'flash' => $this->flash->pullAll(),
            ],
        );
    }

    public function create(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        return $this->renderAdmin(
            $admin,
            'admin.products.form',
            'Create product',
            [
                'mode' => 'create',
                'product' => null,
                'categories' => $this->categories->listActive(),
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $this->verifyCsrf($request);

        $dto = $this->createRequestFrom($request);

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
            return $this->renderAdmin(
                $admin,
                'admin.products.form',
                'Create product',
                [
                    'mode' => 'create',
                    'product' => null,
                    'categories' => $this->categories->listActive(),
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
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

        return $this->renderAdmin(
            $admin,
            'admin.products.form',
            'Edit product',
            [
                'mode' => 'edit',
                'product' => $product,
                'categories' => $this->categories->listActive(),
                'errors' => [],
                'old' => [],
                'csrfToken' => $this->csrf->token(),
            ],
        );
    }

    public function update(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $dto = $this->updateRequestFrom($request);

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
            return $this->renderAdmin(
                $admin,
                'admin.products.form',
                'Edit product',
                [
                    'mode' => 'edit',
                    'product' => [
                        'id' => $id,
                        'name' => $request->input('name', ''),
                        'category_id' => $request->input('category_id', 0),
                        'price' => $request->input('price', ''),
                        'is_available' => $this->availabilityFlag($request),
                    ],
                    'categories' => $this->categories->listActive(),
                    'errors' => [$exception->getMessage()],
                    'old' => $request->body(),
                    'csrfToken' => $this->csrf->token(),
                ],
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

    private function createRequestFrom(Request $request): CreateProductRequest
    {
        return new CreateProductRequest(
            (string) $request->input('name', ''),
            (int) $request->input('category_id', 0),
            $this->normalizedPrice($request),
            $this->availabilityFlag($request),
            $this->uploadedImage($request),
        );
    }

    private function updateRequestFrom(Request $request): UpdateProductRequest
    {
        return new UpdateProductRequest(
            (string) $request->input('name', ''),
            (int) $request->input('category_id', 0),
            $this->normalizedPrice($request),
            $this->availabilityFlag($request),
            $this->uploadedImage($request),
        );
    }

    private function availabilityFlag(Request $request): bool
    {
        return (string) $request->input('is_available', '0') === '1';
    }

    private function normalizedPrice(Request $request): string
    {
        $raw = trim((string) $request->input('price', ''));

        if ($raw === '' || !is_numeric($raw)) {
            return $raw;
        }

        return number_format((float) $raw, 2, '.', '');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function uploadedImage(Request $request): ?array
    {
        $image = $request->files()['image'] ?? null;

        if (!is_array($image)) {
            return null;
        }

        $error = (int) ($image['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $image;
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
