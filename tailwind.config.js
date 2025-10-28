/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './app/**/*.php',
        './resources/views/**/*.blade.php',
        './resources/css/**/*.css',
    ],
    theme: {
        extend: {
            colors: {
                'primary': {
                    50: '#fee2e2',
                    100: '#fecaca',
                    200: '#fca5a5',
                    300: '#f87171',
                    400: '#ef4444',
                    500: '#dc2626',
                    600: '#991b1b',
                    700: '#7f1d1d',
                    800: '#5f0f0f',
                    900: '#450a0a',
                },
                'dark': {
                    50: '#fafafa',
                    100: '#f5f5f5',
                    200: '#e5e5e5',
                    300: '#d4d4d4',
                    400: '#a3a3a3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#262626',
                    900: '#171717',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'sm': '0 1px 2px rgba(0, 0, 0, 0.1)',
                'md': '0 4px 6px rgba(0, 0, 0, 0.15)',
                'lg': '0 10px 25px rgba(0, 0, 0, 0.2)',
                'xl': '0 20px 50px rgba(0, 0, 0, 0.25)',
                'red': '0 4px 15px rgba(220, 38, 38, 0.3)',
                'red-lg': '0 8px 25px rgba(220, 38, 38, 0.15)',
            },
            animation: {
                'blob': 'blob 7s infinite',
                'shimmer': 'shimmer 2s infinite',
            },
            keyframes: {
                blob: {
                    '0%, 100%': {
                        transform: 'translate(0, 0) scale(1)',
                    },
                    '33%': {
                        transform: 'translate(30px, -50px) scale(1.1)',
                    },
                    '66%': {
                        transform: 'translate(-20px, 20px) scale(0.9)',
                    },
                },
                shimmer: {
                    '0%': {
                        backgroundPosition: '-1000px 0',
                    },
                    '100%': {
                        backgroundPosition: '1000px 0',
                    },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('daisyui'),
    ],
    daisyui: {
        themes: [
            {
                light: {
                    "primary": "#dc2626",
                    "secondary": "#059669",
                    "accent": "#f59e0b",
                    "neutral": "#1f2937",
                    "base-100": "#ffffff",
                    "info": "#3b82f6",
                    "success": "#10b981",
                    "warning": "#f59e0b",
                    "error": "#ef4444",
                },
            },
            "dark",
        ],
        darkTheme: "dark",
        base: true,
        styled: true,
        utils: true,
    },
}
