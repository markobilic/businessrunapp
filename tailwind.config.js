import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [
        require("./vendor/power-components/livewire-powergrid/tailwind.config.js"), 
    ],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./node_modules/flowbite/**/*.js",
    ],

    safelist: [
        'bg-red-500/25',
        'bg-light-green/25',
        'bg-mid-green/25',
        'bg-yellow-green/25',
        'sticky',
        '-top-[0.3px]',
        'top-[39px]',
        'h-[80dvh]',
        'max-h-[80dvh]'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
            gridTemplateColumns: {
                'auto-fit': 'repeat(auto-fit, minmax(150px, 1fr))',
            },
            colors: {
                'dark-green':'#005b42',
                'mid-green':'#009530',
                'light-green':'#3dcd58',
                'soft-green':'#dbffe7',
                'yellow-green':'#c1d812',
            },
        },
    },

    plugins: [forms, require('flowbite/plugin')],

    darkMode: 'selector',
};
