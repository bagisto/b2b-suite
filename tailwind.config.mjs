/** @type {import('tailwindcss').Config} */
export default {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            spacing: {
                '15': '3.75rem',
            },

            colors: {
                navyBlue: "#060C3B",
                lightOrange: "#F6F2EB",
            },

            fontFamily: {
                poppins: ["Poppins"],
                dmserif: ["DM Serif Display"],
            },
        }
    },

    darkMode: 'class',

    plugins: []
};