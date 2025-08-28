import { keyframes } from "motion/react";

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./pages/**/*.{js,ts,jsx,tsx,mdx}",
        "./components/**/*.{js,ts,jsx,tsx,mdx}",
        "./app/**/*.{js,ts,jsx,tsx,mdx}",
        "*.{js,ts,jsx,tsx,mdx}",
    ],
    theme: {
        extend: {
            colors: {
                'icea-blue': '#114261',
                'edgewater': '#d1e4de',
                'sahara': '#bc9410',
            },
            keyframes: {
                shimmer: {
                    "100%": {
                        transform: "translateX(100%)",
                    }
                },
            },
            animations: {
                shimmer: "shimmer 1.5s infinite"
            },
        },
        plugins: [],
    },
}