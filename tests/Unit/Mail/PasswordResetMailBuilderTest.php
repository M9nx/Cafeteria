<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use Cafeteria\Mail\PasswordResetMailBuilder;
use PHPUnit\Framework\TestCase;

final class PasswordResetMailBuilderTest extends TestCase
{
    public function test_build_returns_multipart_ready_content_with_escaped_html(): void
    {
        $builder = new PasswordResetMailBuilder();

        $message = $builder->build(
            'Cafeteria',
            'M9nx Test',
            'http://127.0.0.1:8003/reset-password?token=<script>alert(1)</script>',
            60,
        );

        self::assertStringContainsString('Cafeteria — reset your password', $message['subject']);
        self::assertStringContainsString('Hello M9nx Test,', $message['text']);
        self::assertStringContainsString('valid for 60 minutes', $message['text']);
        self::assertStringContainsString('Reset your password', $message['html']);
        self::assertStringContainsString(
            'http://127.0.0.1:8003/reset-password?token=&lt;script&gt;alert(1)&lt;/script&gt;',
            $message['html'],
        );
        self::assertStringNotContainsString('<script>alert(1)</script>', $message['html']);
    }
}
