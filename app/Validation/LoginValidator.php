<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\LoginRequest;

final class LoginValidator
{
    /**
     * @return array<string, string>
     */
    public function validate(LoginRequest $request): array
    {
        $errors = [];

        if ($request->email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($request->password === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }
}