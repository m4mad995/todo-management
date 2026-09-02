import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                canvas: 'var(--color-canvas)',
                surface: 'var(--color-surface)',
                border: 'var(--color-border)',
                matrix: {
                    doFirst: { light: 'var(--matrix-doFirst-light)', DEFAULT: 'var(--matrix-doFirst)', border: 'var(--matrix-doFirst-border)', text: 'var(--matrix-doFirst-text)', hover: 'var(--matrix-doFirst-hover)' },
                    schedule: { light: 'var(--matrix-schedule-light)', DEFAULT: 'var(--matrix-schedule)', border: 'var(--matrix-schedule-border)', text: 'var(--matrix-schedule-text)', hover: 'var(--matrix-schedule-hover)' },
                    delegate: { light: 'var(--matrix-delegate-light)', DEFAULT: 'var(--matrix-delegate)', border: 'var(--matrix-delegate-border)', text: 'var(--matrix-delegate-text)', hover: 'var(--matrix-delegate-hover)' },
                    drop: { light: 'var(--matrix-drop-light)', DEFAULT: 'var(--matrix-drop)', border: 'var(--matrix-drop-border)', text: 'var(--matrix-drop-text)', hover: 'var(--matrix-drop-hover)' },
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-dark': '0 1px 3px 0 rgb(0 0 0 / 0.2), 0 1px 2px -1px rgb(0 0 0 / 0.2)',
                'card-hover': '0 4px 6px -1px rgb(0 0 0 / 0.06), 0 2px 4px -2px rgb(0 0 0 / 0.06)',
                'card-hover-dark': '0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.3)',
                'elevated': '0 10px 15px -3px rgb(0 0 0 / 0.06), 0 4px 6px -4px rgb(0 0 0 / 0.06)',
                'elevated-dark': '0 10px 15px -3px rgb(0 0 0 / 0.3), 0 4px 6px -4px rgb(0 0 0 / 0.3)',
            },
            borderRadius: {
                'card': '10px',
                'btn': '8px',
            },
            animation: {
                'fade-in': 'fadeIn 0.2s ease-out',
                'slide-up': 'slideUp 0.2s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [forms],
};
