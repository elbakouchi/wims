const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  darkMode: false,
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],

  theme: {
    extend: {
      screens: {
        print: { raw: 'print' },
      },
      fontFamily: {
        sans: ['Nunito', ...defaultTheme.fontFamily.sans],
      },
    },
  },

  // variants: {
  //   extend: {
  //     opacity: ['disabled'],
  //     zIndex: ['focus', 'hover'],
  //     width: ['hover', 'group-hover'],
  //     margin: ['hover', 'group-hover'],
  //     padding: ['hover', 'group-hover'],
  //     display: ['hover', 'group-hover'],
  //     overflow: ['hover', 'group-hover'],
  //   },
  // },

  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography'), require('@tailwindcss/line-clamp')],
};
