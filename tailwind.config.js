const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    // content: [
    //     './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    //     './storage/framework/views/*.php',
    //     './resources/views/**/*.blade.php',
    // ],

    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],

    theme: {
        fontFamily: {
          sans: ['Roboto', 'sans-serif'],
        },
        extend: {
          colors: {
            'theme-orange': '#F6B334',
            'theme-red': '#E63434',
            'theme-black': '#222325',
            'theme-gray': '#404145',
          },
        },
      },

    plugins: [require('@tailwindcss/forms')],
};
