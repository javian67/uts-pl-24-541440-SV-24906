/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        headline: ['"Archivo Black"', 'sans-serif'],
        body: ['"Work Sans"', 'sans-serif'],
        mono: ['"Space Mono"', 'monospace'],
      },
      colors: {
        raw: {
          black: '#000000',
          white: '#FFFFFF',
          blue: '#0000FF',
          sunken: '#F0F0F0',
          hover: '#E8E8E8',
          disabled: '#F5F5F5',
          borderDisabled: '#CCCCCC',
          success: '#008000',
          warning: '#FFA500',
          error: '#FF0000',
        }
      },
      borderWidth: {
        '1': '1px',
        '3': '3px',
        '5': '5px',
      },
      borderRadius: {
        DEFAULT: '0px',
        none: '0px',
        sm: '0px',
        md: '0px',
        lg: '0px',
        xl: '0px',
        '2xl': '0px',
        '3xl': '0px',
        full: '0px',
      },
      boxShadow: {
        DEFAULT: 'none',
        sm: 'none',
        md: 'none',
        lg: 'none',
        xl: 'none',
        '2xl': 'none',
        inner: 'none',
        none: 'none',
      }
    },
  },
  plugins: [],
}
