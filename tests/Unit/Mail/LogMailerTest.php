<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use Cafeteria\Mail\LogMailer;
use PHPUnit\Framework\TestCase;

final class LogMailerTest extends TestCase
{
    public function test_send_writes_metadata_without_body_content(): void
    {
        $logPath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'cafeteria-mail-' . bin2hex(random_bytes(8)) . '.log';

        $mailer = new LogMailer($logPath);

        try {
            $mailer->send(
                'user@example.test',
                'Password reset',
                'secret reset body with token=abc123',
            );

            $contents = file_get_contents($logPath);

            self::assertIsString($contents);
            self::assertStringContainsString('to=user@example.test', $contents);
            self::assertStringContainsString('subject=Password reset', $contents);
            self::assertStringNotContainsString('abc123', $contents);
        } finally {
            if (is_file($logPath)) {
                unlink($logPath);
            }
        }
    }
}
