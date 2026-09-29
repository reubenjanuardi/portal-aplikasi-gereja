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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],

                // "Kotak Persembahan" — the engraved-document world.
                // The Fallback faces are metric-matched local aliases declared in
                // resources/css/app.css, so a missing file does not reflow the page.
                // Display: a Didone, the face of banknotes and certificates.
                display: [
                    '"Bodoni Moda"',
                    '"Bodoni Moda Fallback"',
                    'Georgia',
                    'serif',
                ],
                // Workhorse grotesque with true tabular figures: the column instrument.
                figure: [
                    'Archivo',
                    '"Archivo Fallback"',
                    ...defaultTheme.fontFamily.sans,
                ],
                // Letterpress label furniture, uppercase and widely tracked.
                label: [
                    '"Archivo Narrow"',
                    'Archivo',
                    '"Archivo Fallback"',
                    ...defaultTheme.fontFamily.sans,
                ],
            },

            colors: {
                // Paper: a cool stock, not a warm cream.
                paper: {
                    DEFAULT: '#F6F5F1',
                    deep: '#ECE9E1',
                },
                // Ink: the GPIB seal's own blue, carried into the page.
                ink: {
                    DEFAULT: '#0A1D3D',
                    lift: '#132A52',
                },
                // Brass: the single accent — communion ware, ledger foil.
                // DEFAULT reads on ink; deep is the text weight on paper; rule is
                // the hairline weight for non-text boundaries on paper.
                brass: {
                    DEFAULT: '#C0973A',
                    deep: '#8A6624',
                    rule: '#A97F2E',
                },
                // Secondary text, tinted from the ink rather than a neutral gray.
                slate: '#5A6A8C',
                mist: '#93A3C0',
            },

            fontVariantNumeric: {
                tabular: ['tnum'],
            },

            letterSpacing: {
                label: '0.18em',
                'label-tight': '0.12em',
            },

            maxWidth: {
                rule: '78rem',
            },
        },
    },

    plugins: [forms],
};
