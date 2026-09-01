<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\ForgotPasswordRequest;
use Cafeteria\DTO\ResetPasswordRequest;

final class PasswordResetValidator
{
    /**
     * @return array<string, string>
     */
    public function validateForgot(ForgotPasswordRequest $request): array
    {
        $errors = [];

        if ($request->email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public function validateReset(ResetPasswordRequest $request): array
    {
        $errors = [];

        if ($request->token === '') {
            $errors['token'] = 'Reset token is required.';
        }

        if ($request->password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($request->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($request->passwordConfirmation === '') {
            $errors['password_confirmation'] = 'Password confirmation is required.';
        } elseif ($request->password !== $request->passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        return $errors;
    }
}