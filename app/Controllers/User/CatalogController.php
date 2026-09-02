<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\User;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\View\View;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;

final class CatalogController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly OrderQueryRepositoryInterface $orders,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $products = $this->products->paginateAvailable(
            $page,
            $perPage
        );

        $latestOrder = $this->orders->findLatestForUser(
            $user->id()
        );

        return View::render(
            'user.catalog',
            [
                'products' => $products,
                'latestOrder' => $latestOrder,
            ],
            'layouts.app',
        );
    }
}