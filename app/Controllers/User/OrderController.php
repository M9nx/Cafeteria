<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\User;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Services\OrderService;
use InvalidArgumentException;
use RuntimeException;

final class OrderController
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function create(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        return Response::html(
            $this->render('user/orders/form.php', [
                'csrfToken' => $this->csrf->token(),
                'errors' => [],
                'old' => [],
            ])
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $this->verifyCsrf($request);

        $data = $request->body();
        $orderRequest = PlaceOrderRequest::fromArray($data);

        try {
            $this->orders->place($user, $orderRequest);

            return Response::redirect('/');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return Response::html(
                $this->render('user/orders/form.php', [
                    'csrfToken' => $this->csrf->token(),
                    'errors' => [$exception->getMessage()],
                    'old' => $data,
                ]),
                422
            );
        }
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(CsrfTokenManager::FIELD_NAME);

        if (!$this->csrf->validate($token)) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }

    private function render(string $template, array $data = []): string
    {
        $path = dirname(__DIR__, 3) . '/resources/views/' . $template;

        if (!is_file($path)) {
            throw new RuntimeException("View template not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}