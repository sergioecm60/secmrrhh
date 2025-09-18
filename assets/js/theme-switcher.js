(() => {
    'use strict'

    const getStoredTheme = () => localStorage.getItem('theme')
    const setStoredTheme = theme => localStorage.setItem('theme', theme)

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme()
        if (storedTheme) {
            return storedTheme
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = theme => {
        const effectiveTheme = theme === 'auto'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : theme;

        document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
        
        // Dispara un evento personalizado para que otros scripts (como los de gráficos) puedan reaccionar.
        const themeChangeEvent = new CustomEvent('theme:change', { detail: { theme: effectiveTheme } });
        document.dispatchEvent(themeChangeEvent);
    }

    const showActiveTheme = (theme) => {
        // Remove active class from all buttons
        document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
            element.classList.remove('active')
        })

        // Add active class to the correct button
        const activeThemeButton = document.querySelector(`.theme-btn[data-bs-theme-value="${theme}"]`)
        if (activeThemeButton) {
            activeThemeButton.classList.add('active')
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const theme = getPreferredTheme()
        setTheme(theme)
        showActiveTheme(theme)

        document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const theme = toggle.getAttribute('data-bs-theme-value')
                setStoredTheme(theme)
                setTheme(theme)
                showActiveTheme(theme)
            })
        })

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const storedTheme = getStoredTheme()
            if (storedTheme === 'auto') {
                setTheme('auto')
                showActiveTheme('auto')
            }
        })
    })
})()