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
            colors: {
                cream: {
                    50: '#FBF8FF',
                    100: '#F5F0FF',
                    200: '#EDE4FF',
                },
                party: {
                    purple: '#7C3AED',
                    pink: '#DB2777',
                    mint: '#10B981',
                    sky: '#0EA5E9',
                    ink: '#1E1B4B',
                    muted: '#64748B',
                },
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                display: ['Fredoka', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 10px 40px rgba(124, 58, 237, 0.12)',
            },
        },
    },

    plugins: [forms],
};
