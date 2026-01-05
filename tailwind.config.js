/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.php",
        "./assets/js/**/*.js"
    ],
    prefix: 'tw-',
    corePlugins: {
        preflight: false, // Disabilita il reset globale per non rompere l'admin di WP
    },
    theme: {
        extend: {},
    },
    plugins: [],
}
