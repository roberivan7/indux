<?php
$icon = '<link rel="icon" type="image/png" href="../pagina_de_vendas/img/icon_indux.png">';

if (!function_exists('lucideIcon')) {
    function lucideIcon(string $name, string $class = ''): string {
        $icons = [
            'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
            'bell' => '<path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M4 17h16"/><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>',
            'book-open' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V5a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3v15a3 3 0 0 0-3-3Z"/><path d="M21 18a1 1 0 0 0 1-1V5a2 2 0 0 0-2-2h-5a3 3 0 0 0-3 3v15a3 3 0 0 1 3-3Z"/>',
            'box' => '<path d="m21 8-9 5-9-5"/><path d="M3 8l9-5 9 5v8l-9 5-9-5Z"/><path d="M12 13v8"/>',
            'chart-no-axes-combined' => '<path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.708 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            'circle-alert' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'circle-help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 1 1 5.83 1c0 2-3 2-3 4"/><path d="M12 17h.01"/>',
            'circle-x' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
            'clock-3' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16.5 12"/>',
            'droplet' => '<path d="M12 22a7 7 0 0 0 7-7c0-4-7-13-7-13S5 11 5 15a7 7 0 0 0 7 7Z"/>',
            'factory' => '<path d="M12 16h.01"/><path d="M16 16h.01"/><path d="M3 21h18"/><path d="M5 21V10l5 4V10l5 4V4h4l2 17"/>',
            'gauge' => '<path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>',
            'key-round' => '<path d="M2.586 17.414A2 2 0 0 0 2 18.828V21h2.172a2 2 0 0 0 1.414-.586l8.704-8.704"/><circle cx="16" cy="8" r="5"/>',
            'lightbulb' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.74.66-1.27 1.16-1.75A6 6 0 1 0 7.75 12.25c.48.47.97 1 1.16 1.75Z"/>',
            'lock-keyhole' => '<circle cx="12" cy="16" r="1"/><rect width="18" height="12" x="3" y="10" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/>',
            'map-pin' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'message-circle' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/>',
            'pause' => '<rect width="4" height="16" x="6" y="4" rx="1"/><rect width="4" height="16" x="14" y="4" rx="1"/>',
            'pencil' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'play' => '<polygon points="6 3 20 12 6 21 6 3"/>',
            'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'radio' => '<path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/><path d="M7.8 16.2a6 6 0 0 1 0-8.5"/><circle cx="12" cy="12" r="2"/><path d="M16.2 7.8a6 6 0 0 1 0 8.5"/><path d="M19.1 4.9C23 8.8 23 15.1 19.1 19"/>',
            'rotate-cw' => '<path d="M21 12a9 9 0 1 1-2.64-6.36L21 8"/><path d="M21 3v5h-5"/>',
            'save' => '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"/><circle cx="12" cy="12" r="3"/>',
            'shield-x' => '<path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3Z"/><path d="m9.5 9.5 5 5"/><path d="m14.5 9.5-5 5"/>',
            'thermometer' => '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>',
            'trash-2' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 15H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            'triangle-alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'user-round' => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
            'users-round' => '<path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 21a8 8 0 0 0-5-7.5"/><path d="M16 3.3a5 5 0 0 1 0 9.4"/>',
            'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5-5l2.1 2.1-2.8 2.8-2.1-2.1a4 4 0 0 0 5 5l-7.6 7.6a2 2 0 0 0 2.8 2.8l7.6-7.6a4 4 0 0 0 5-5l-2.1 2.1-2.8-2.8Z"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'zap' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9-11a.5.5 0 0 1 .87.45l-1.4 5.61A1 1 0 0 0 12.66 8H20a1 1 0 0 1 .78 1.63l-9 11a.5.5 0 0 1-.87-.45l1.4-5.61A1 1 0 0 0 11.34 14Z"/>',
        ];

        $body = $icons[$name] ?? $icons['circle-help'];
        $classes = trim('lucide-icon lucide-' . preg_replace('/[^a-z0-9-]/', '', $name) . ' ' . $class);

        return '<svg class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
    }
}

if (!function_exists('renderLucideTokens')) {
    function renderLucideTokens(string $html): string {
        return preg_replace_callback(
            '/\{\{lucide:([a-z0-9-]+)\}\}/',
            static fn(array $match): string => lucideIcon($match[1]),
            $html
        );
    }
}

if (!defined('LUCIDE_OUTPUT_BUFFER_ACTIVE')) {
    define('LUCIDE_OUTPUT_BUFFER_ACTIVE', true);
    ob_start('renderLucideTokens');
}
