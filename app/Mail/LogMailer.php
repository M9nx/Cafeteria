<?php

declare(strict_types=1);

namespace Cafeteria\Mail;

use RuntimeException;

final class LogMailer implements MailerInterface
{
    public function __construct(
        private readonly string $logPath,
    ) {
    }

    public function send(
        string $to,
        string $subject,
        string $body,
    ): void {
        unset($body);

        $directory = dirname($this->logPath);

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                "Unable to create mail log directory: {$directory}"
            );
        }

        $entry = sprintf(
            "[%s] to=%s subject=%s\n",
            gmdate('Y-m-d\TH:i:s\Z'),
            $to,
            $subject,
        );

        if (file_put_contents(
            $this->logPath,
            $entry,
            FILE_APPEND | LOCK_EX,
        ) === false) {
            throw new RuntimeException(
                "Unable to write mail log: {$this->logPath}"
            );
        }
    }
}
