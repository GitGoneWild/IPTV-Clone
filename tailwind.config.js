/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'homelab': {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                    950: '#2e1065',
                },
                'gh': {
                    'bg': '#0d1117',
                    'bg-secondary': '#161b22',
                    'bg-tertiary': '#21262d',
                    'border': '#30363d',
                    'border-muted': '#21262d',
                    'text': '#c9d1d9',
                    'text-muted': '#8b949e',
                    'accent': '#58a6ff',
                    'accent-emphasis': '#1f6feb',
                    'success': '#3fb950',
                    'warning': '#d29922',
                    'danger': '#f85149',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
