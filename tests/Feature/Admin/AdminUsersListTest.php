<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\Support\HttpTestCase;

final class AdminUsersListTest extends HttpTestCase
{
    public function test_admin_users_page_lists_admin_and_regular_users(): void
    {
        $this->loginAsAdmin();

        $html = $this->responseContent($this->get('/admin/users'));

        self::assertStringContainsString('admin@example.test', $html);
        self::assertStringContainsString('user@example.test', $html);
        self::assertStringContainsString('Demo Admin', $html);
        self::assertStringContainsString('Demo User', $html);
        self::assertStringContainsString('Create user', $html);
        self::assertStringNotContainsString('Admin users', $html);
    }
}
