<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    /** Render a view inside a layout. $view like 'orders/index'. */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/admin'): void
    {
        $content = self::partial($view, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        $data['content'] = $content;
        echo self::partial($layout, $data);
    }

    public static function partial(string $view, array $data = []): string
    {
        $file = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $view");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string)ob_get_clean();
    }
}
