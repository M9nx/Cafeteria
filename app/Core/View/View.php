<?php

declare(strict_types=1);

namespace Cafeteria\Core\View;

use Cafeteria\Core\Http\Response;
use RuntimeException;

final class View
{
    /** @var array<string, mixed> */
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public function __construct(
        private readonly string $basePath,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function renderTemplate(string $template, array $data = [], ?string $layout = null): Response
    {
        $templateFile = $this->resolveTemplate($template);

        if (!is_file($templateFile)) {
            throw new RuntimeException("View template not found: {$template}");
        }

        $content = $this->capture($templateFile, $data);

        if ($layout !== null) {
            $layoutFile = $this->resolveTemplate($layout);
            $data['content'] = $content;
            $content = $this->capture($layoutFile, $data);
        }

        return Response::html($content);
    }

    /** @param array<string, mixed> $data */
    private function capture(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    private function resolveTemplate(string $template): string
    {
        $normalized = str_replace('.', '/', $template);

        return rtrim($this->basePath, '/') . '/' . $normalized . '.php';
    }

    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = [], ?string $layout = null): Response
    {
        $data = array_merge(self::$shared, $data);

        $basePath = dirname(__DIR__, 3) . '/resources/views';
        $view = new self($basePath);

        return $view->renderTemplate($template, $data, $layout);
    }
}
