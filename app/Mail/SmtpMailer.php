<?php

declare(strict_types=1);

namespace Cafeteria\Mail;

use RuntimeException;

final class SmtpMailer implements MailerInterface
{
    /**
     * @param array{
     *     from: string,
     *     host?: string,
     *     port?: int,
     *     username?: string,
     *     password?: string,
     *     encryption?: string
     * } $config
     */
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function send(
        string $to,
        string $subject,
        string $body,
        ?string $htmlBody = null,
    ): void {
        $host = trim((string) ($this->config['host'] ?? ''));

        if ($host === '') {
            throw new RuntimeException('SMTP host is not configured.');
        }

        $port = (int) ($this->config['port'] ?? 587);

        if ($port < 1) {
            throw new RuntimeException('SMTP port is not configured.');
        }

        $from = trim((string) ($this->config['from'] ?? ''));

        if ($from === '') {
            throw new RuntimeException('MAIL_FROM is not configured.');
        }

        $encryption = strtolower(
            trim((string) ($this->config['encryption'] ?? 'tls'))
        );

        $transport = $encryption === 'ssl'
            ? "ssl://{$host}:{$port}"
            : "tcp://{$host}:{$port}";

        $socket = @stream_socket_client(
            $transport,
            $errorCode,
            $errorMessage,
            10,
            STREAM_CLIENT_CONNECT,
        );

        if (!is_resource($socket)) {
            throw new RuntimeException(
                'Unable to connect to SMTP server.'
            );
        }

        try {
            $this->expectResponse($socket, [220]);
            $this->command($socket, 'EHLO cafeteria.local', [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);

                if (!stream_socket_enable_crypto(
                    $socket,
                    true,
                    STREAM_CRYPTO_METHOD_TLS_CLIENT,
                )) {
                    throw new RuntimeException(
                        'Unable to enable SMTP TLS.'
                    );
                }

                $this->command($socket, 'EHLO cafeteria.local', [250]);
            }

            $username = trim((string) ($this->config['username'] ?? ''));
            $password = (string) ($this->config['password'] ?? '');

            if ($username !== '') {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command(
                    $socket,
                    base64_encode($username),
                    [334],
                );
                $this->command(
                    $socket,
                    base64_encode($password),
                    [235],
                );
            }

            $this->command($socket, "MAIL FROM:<{$from}>", [250]);
            $this->command($socket, "RCPT TO:<{$to}>", [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = $this->buildMessage(
                $from,
                $to,
                $subject,
                $body,
                $htmlBody,
            );

            fwrite($socket, $message . "\r\n.\r\n");
            $this->expectResponse($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    /**
     * @param resource $socket
     * @param list<int> $expectedCodes
     */
    private function command(
        mixed $socket,
        string $command,
        array $expectedCodes,
    ): void {
        fwrite($socket, $command . "\r\n");
        $this->expectResponse($socket, $expectedCodes);
    }

    /**
     * @param resource $socket
     * @param list<int> $expectedCodes
     */
    private function expectResponse(
        mixed $socket,
        array $expectedCodes,
    ): void {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;

            if (
                isset($line[3])
                && $line[3] === ' '
            ) {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('SMTP server returned no response.');
        }

        $code = (int) substr($response, 0, 3);

        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('SMTP command failed.');
        }
    }

    private function buildMessage(
        string $from,
        string $to,
        string $subject,
        string $body,
        ?string $htmlBody,
    ): string {
        $headers = [
            "From: {$from}",
            "To: {$to}",
            "Subject: {$subject}",
            'MIME-Version: 1.0',
        ];

        if ($htmlBody === null || trim($htmlBody) === '') {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';

            return implode("\r\n", array_merge($headers, ['', $body, '']));
        }

        $boundary = 'cafeteria_' . bin2hex(random_bytes(12));

        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $parts = [
            implode("\r\n", $headers),
            '',
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $body,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $htmlBody,
            '--' . $boundary . '--',
            '',
        ];

        return implode("\r\n", $parts);
    }
}
