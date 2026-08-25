/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    safelist: [
        // Dynamic color classes used in Blade templates via variables like bg-{{ $color }}-100
        // Status colors: amber (pending), green (confirmed/paid), blue (completed), red (cancelled/failed), gray (default)
        // Extra: purple (certificates/roles), orange (ledger), teal (audit)
        { pattern: /^bg-(amber|green|blue|red|gray|purple|orange|teal)-(50|100|200)$/ },
        { pattern: /^text-(amber|green|blue|red|gray|purple|orange|teal)-(600|700|800)$/ },
        { pattern: /^border-(amber|green|blue|red|gray|purple|orange|teal)-(200|300|400)$/ },
        // Status bar top strips used in booking/payment cards: h-1 bg-{color}-400
        { pattern: /^bg-(amber|green|blue|red|gray|purple|orange|teal)-400$/ },
        // Badge backgrounds with text
        'badge-pending', 'badge-paid', 'badge-confirmed', 'badge-completed',
        'badge-cancelled', 'badge-failed', 'badge-refunded', 'badge-voided',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                parish: {
                    blue:  '#1a3a6b',
                    gold:  '#c9a227',
                    light: '#e8f0fe',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
