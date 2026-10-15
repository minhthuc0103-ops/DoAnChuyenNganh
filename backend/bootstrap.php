<?php
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/Controllers/' . $class . '.php',
        __DIR__ . '/Models/'      . $class . '.php',
        __DIR__ . '/Helpers/'     . $class . '.php',
        __DIR__ . '/config/'          . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

if (!function_exists('e')) {
    /** Shortcut htmlspecialchars — dùng trong Views: <?= e($var) ?> */
    function e(?string $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('normalize_room_images')) {
    function normalize_room_images(?string $value): array {
        $items = [];

        $walk = function ($data, int $depth = 0) use (&$walk, &$items): void {
            if ($depth > 5 || $data === null || $data === '') {
                return;
            }

            if (is_array($data)) {
                foreach ($data as $item) {
                    $walk($item, $depth + 1);
                }
                return;
            }

            $src = trim((string)$data, " \t\n\r\0\x0B\"'");
            $decoded = json_decode($src, true);
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== $src) {
                $walk($decoded, $depth + 1);
                return;
            }

            $src = str_replace('\\/', '/', $src);
            if (preg_match('#^public/uploads/phong/[^<>:"|?*]+\.(jpe?g|png|webp|gif)$#i', $src) && file_exists($src)) {
                $items[] = str_replace('\\', '/', $src);
            }
        };

        $walk($value);
        return array_values(array_unique($items));
    }
}
