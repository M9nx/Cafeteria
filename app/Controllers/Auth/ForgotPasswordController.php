<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Auth;

use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\ForgotPasswordRequest;
use Cafeteria\Services\PasswordResetService;
use Cafeteria\Validation\PasswordResetValidator;

final class ForgotPasswordController
{
    public function __construct(
        private readonly PasswordResetService $passwordReset,
        private readonly PasswordResetValidator $validator,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function show(Request $request): Response
    {
        return View::render(
            'auth.forgot-password',
            [
                'csrfToken' => $this->csrf->token(),
                'errors' => [],
                'email' => '',
                'message' => $this->flash->pull('success'),
            ],
            'layouts.guest',
        );
    }

    public function requestReset(Request $request): Response
    {
        $forgotRequest = ForgotPasswordRequest::fromArray($request->body());

        $errors = $this->validator->validateForgot($forgotRequest);

        if (!$this->csrf->validate(
            $request->input(CsrfTokenManager::FIELD_NAME)
        )) {
            $errors['_csrf_token'] = 'Invalid CSRF token.';
        }

        if ($errors !== []) {
            return View::render(
                'auth.forgot-password',
                [
                    'csrfToken' => $this->csrf->token(),
                    'errors' => $errors,
                    'email' => $forgotRequest->email,
                    'message' => null,
                ],
                'layouts.guest',
            );
        }

        $this->passwordReset->requestReset($forgotRequest->email);

        $this->flash->flash(
            'success',
            'If an account exists for this email, a password reset link has been sent.',
        );

        return Response::redirect('/forgot-password');
    }
}