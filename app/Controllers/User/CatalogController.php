<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\User;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\View\View;
use Cafeteria\Repositories\Contracts\CategoryRepositoryInterface;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;

final class CatalogController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly OrderQueryRepositoryInterface $orders,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $availablePage = max(1, (int) $request->input('page', 1));
        $curatedPage = max(1, (int) $request->input('cpage', 1));
        $categoryId = (int) $request->input('category', 0);
        $categoryId = $categoryId > 0 ? $categoryId : null;

        $available = $this->products->paginateAvailable(
            $availablePage,
            4,
            null
        );

        $curated = $this->products->paginateAvailable(
            $curatedPage,
            3,
            $categoryId
        );

        $categories = $this->categories->listActive();
        $sectionData = [
            'available' => $available,
            'curated' => $curated,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'availablePage' => (int) ($available['page'] ?? $availablePage),
            'curatedPage' => (int) ($curated['page'] ?? $curatedPage),
        ];

        if ($this->wantsAjax($request)) {
            return Response::json([
                'available' => View::renderToString(
                    'user.catalog.partials.available',
                    $sectionData
                ),
                'curated' => View::renderToString(
                    'user.catalog.partials.curated',
                    $sectionData
                ),
                'url' => $this->buildCatalogUrl(
                    $categoryId,
                    (int) ($available['page'] ?? $availablePage),
                    (int) ($curated['page'] ?? $curatedPage),
                ),
            ]);
        }

        $flash = $this->flash->pullAll();

        if ((string) $request->input('ordered', '') === '1') {
            $flash['success'] = 'Order placed successfully. Your cart was cleared.';
        }

        return View::render(
            'user.catalog.index',
            array_merge($sectionData, [
                'title' => 'Catalogue',
                'currentUser' => $user,
                'latestOrder' => $this->orders->findLatestForUser($user->id()),
                'flash' => $flash,
                'userName' => $user->name(),
            ]),
            'layouts.app',
        );
    }

    private function wantsAjax(Request $request): bool
    {
        $requestedWith = strtolower((string) $request->header('X-Requested-With', ''));

        return $requestedWith === 'xmlhttprequest'
            || (string) $request->input('ajax', '') === '1';
    }

    private function buildCatalogUrl(
        ?int $categoryId,
        int $availablePage,
        int $curatedPage,
    ): string {
        $params = [];

        if ($categoryId !== null && $categoryId > 0) {
            $params['category'] = $categoryId;
        }

        if ($availablePage > 1) {
            $params['page'] = $availablePage;
        }

        if ($curatedPage > 1) {
            $params['cpage'] = $curatedPage;
        }

        return $params === [] ? '/' : ('/?' . http_build_query($params));
    }
}
