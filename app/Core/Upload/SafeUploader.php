<?php

declare(strict_types=1);

namespace Cafeteria\Core\Upload;

use RuntimeException;

final class SafeUploader
{
    private const MAX_SIZE = 2 * 1024 * 1024;

    /**
     * @var array<string, string>
     */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly string $directory,
    ) {
    }

    /**
     * @param array<string, mixed> $file
     */
    public function upload(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Invalid uploaded file.');
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_SIZE) {
            throw new RuntimeException('Image must not exceed 2 MB.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);

        if (!isset(self::ALLOWED_MIMES[$mime])) {
            throw new RuntimeException('Unsupported image type.');
        }

        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
                throw new RuntimeException('Unable to create upload directory.');
            }
        }

        $extension = self::ALLOWED_MIMES[$mime];

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $destination = rtrim($this->directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new RuntimeException('Unable to store uploaded image.');
        }

        return $filename;
    }
}