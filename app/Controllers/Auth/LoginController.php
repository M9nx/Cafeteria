<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\LoginRequest;
use Cafeteria\Services\AuthService;
use Cafeteria\Validation\LoginValidator;
use RuntimeException;

final class LoginController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly LoginValidator $validator,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function show(Request $request): Response
    {
        return View::render(
            'auth.login',
            [
                'csrfToken' => $this->csrf->token(),
                'email' => '',
                'errors' => [],
            ],
            'layouts.guest',
        );
    }

    public function login(Request $request): Response
    {
        $loginRequest = LoginRequest::fromArray($request->body());
        $errors = $this->validator->validate($loginRequest);

        if (!$this->csrf->validate(
            $request->input(CsrfTokenManager::FIELD_NAME)
        )) {
            $errors['_csrf_token'] = 'Invalid CSRF token.';
        }

        if ($errors !== []) {
            return View::render(
                'auth.login',
                [
                    'csrfToken' => $this->csrf->token(),
                    'email' => $loginRequest->email,
                    'errors' => $errors,
                ],
                'layouts.guest',
            );
        }

        try {
            $this->auth->login(
                $loginRequest->email,
                $loginRequest->password,
            );
        } catch (RuntimeException) {
            return View::render(
                'auth.login',
                [
                    'csrfToken' => $this->csrf->token(),
                    'email' => $loginRequest->email,
                    'errors' => [
                        'general' => 'Invalid email or password.',
                    ],
                ],
                'layouts.guest',
            );
        }

        return Response::redirect('/');
    }
}