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

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                canvas: '#F8FAFC',
                surface: '#FFFFFF',
                border: '#E5E7EB',
                matrix: {
                    doFirst: { light: '#FEF2F2', DEFAULT: '#FEE2E2', border: '#FECACA', text: '#DC2626', hover: '#FEE2E2' },
                    schedule: { light: '#EFF6FF', DEFAULT: '#DBEAFE', border: '#BFDBFE', text: '#2563EB', hover: '#DBEAFE' },
                    delegate: { light: '#FFFBEB', DEFAULT: '#FEF3C7', border: '#FDE68A', text: '#D97706', hover: '#FEF3C7' },
                    drop: { light: '#F9FAFB', DEFAULT: '#F3F4F6', border: '#E5E7EB', text: '#6B7280', hover: '#F3F4F6' },
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-hover': '0 4px 6px -1px rgb(0 0 0 / 0.06), 0 2px 4px -2px rgb(0 0 0 / 0.06)',
                'elevated': '0 10px 15px -3px rgb(0 0 0 / 0.06), 0 4px 6px -4px rgb(0 0 0 / 0.06)',
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
