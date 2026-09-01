<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\ResetPasswordRequest;
use Cafeteria\Services\PasswordResetService;
use Cafeteria\Validation\PasswordResetValidator;
use RuntimeException;

final class ResetPasswordController
{
    public function __construct(
        private readonly PasswordResetService $passwordReset,
        private readonly PasswordResetValidator $validator,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function show(Request $request): Response
    {
        $token = trim((string) $request->input('token', ''));

        if ($token === '') {
            return Response::html('Invalid or expired reset token.', 400);
        }

        return View::render(
            'auth.reset-password',
            [
                'csrfToken' => $this->csrf->token(),
                'token' => $token,
                'errors' => [],
            ],
            'layouts.guest',
        );
    }

    public function reset(Request $request): Response
    {
        $resetRequest = ResetPasswordRequest::fromArray($request->body());

        $errors = $this->validator->validateReset($resetRequest);

        if (!$this->csrf->validate(
            $request->input(CsrfTokenManager::FIELD_NAME)
        )) {
            $errors['_csrf_token'] = 'Invalid CSRF token.';
        }

        if ($errors !== []) {
            return View::render(
                'auth.reset-password',
                [
                    'csrfToken' => $this->csrf->token(),
                    'token' => $resetRequest->token,
                    'errors' => $errors,
                ],
                'layouts.guest',
            );
        }

        try {
            $this->passwordReset->resetPassword(
                $resetRequest->token,
                $resetRequest->password,
            );
        } catch (RuntimeException) {
            return View::render(
                'auth.reset-password',
                [
                    'csrfToken' => $this->csrf->token(),
                    'token' => $resetRequest->token,
                    'errors' => [
                        'general' => 'Invalid or expired reset token.',
                    ],
                ],
                'layouts.guest',
            );
        }

        return Response::redirect('/login');
    }
}