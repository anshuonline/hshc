tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#111827', // Dark charcoal/slate
                secondary: '#1f2937', // Lighter charcoal
                accent: '#d4af37', // Champagne Gold
                'accent-light': '#f3e5ab', // Vanilla
                'accent-dark': '#aa8c2c', // Dark Gold
                light: '#f9fafb', // Off-white
                dark: '#030712', // Almost black
            },
            fontFamily: {
                'serif': ['"Cormorant Garamond"', 'serif'],
                'sans': ['"Montserrat"', 'sans-serif']
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'glass': 'linear-gradient(to bottom right, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05))',
                'glass-dark': 'linear-gradient(to bottom right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3))',
            }
        }
    }
}
