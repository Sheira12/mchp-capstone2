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
        // Dynamic color classes used in Blade templates
        { pattern: /bg-(amber|green|blue|red|purple|gray|orange)-(50|100|200)/ },
        { pattern: /text-(amber|green|blue|red|purple|gray|orange)-(600|700|800)/ },
        { pattern: /border-(amber|green|blue|red|purple|gray|orange)-(200|300)/ },
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
