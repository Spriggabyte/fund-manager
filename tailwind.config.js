import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Foord brand palette (brand guidelines, Sept 2026). Anchors from the guide
// are marked; the other steps are interpolated so the usual 50–900 scale works.
const navy = {
    50: '#f2f4f5',
    100: '#e1e6e9',
    200: '#c3ccd2',
    300: '#9aa8b1',
    400: '#6b7d88',
    500: '#4c5d67',
    600: '#3e4a50', // 90% dark navy
    700: '#29363d', // DARK NAVY (primary)
    800: '#222d33',
    900: '#1b2429',
    950: '#131a1e',
};

const naartjie = {
    50: '#fcf2f0',
    100: '#f8e1dd',
    200: '#f0c3bc',
    300: '#e7a49d', // 53% naartjie
    400: '#dd7e75', // 75% naartjie
    500: '#d9675a',
    600: '#d25347', // NAARTJIE (primary)
    700: '#b4433a',
    800: '#923a33',
    900: '#78332e',
    950: '#411815',
};

// Neutral colours; `gray` is overridden with these so existing gray-* utilities
// pick up the brand greys without touching every view.
const neutral = {
    50: '#f9f9f9',
    100: '#f4f4f4', // very light grey
    200: '#e6e6e6',
    300: '#cccccc', // light grey
    400: '#9a9a9a', // medium grey
    500: '#767676',
    600: '#535353', // dark grey
    700: '#424242',
    800: '#313131', // off-black
    900: '#29363d', // headings in dark navy
    950: '#1b2429',
};

// Accent colours (graphs & tables).
const sky = {
    50: '#f1f5f8',
    100: '#e2eaf0',
    200: '#bdceda', // 50% light blue
    300: '#9fb7c8',
    400: '#7a9cb4', // LIGHT BLUE
    500: '#6789a3',
    600: '#577590',
    700: '#4a6679',
    800: '#3d5363',
    900: '#324452',
};

const mushroom = {
    50: '#fbf8f1',
    100: '#f6efdf',
    200: '#eee2c8',
    300: '#e2cea4', // MUSHROOM
    400: '#d4b87f',
    500: '#c29f5c',
    600: '#a8844a',
    700: '#86683c',
    800: '#6b5333',
    900: '#57452c',
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                navy,
                naartjie,
                sky,
                mushroom,
                gray: neutral,
            },
            fontFamily: {
                sans: ['"Avenir Next LT Pro"', 'Calibri', ...defaultTheme.fontFamily.sans],
                serif: ['Merriweather', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
