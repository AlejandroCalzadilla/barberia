import { ref, watch, onMounted } from 'vue'

// Lista de temas disponibles con colores CSS personalizados
const availableThemes = [
    { 
        value: 'light', 
        name: '☀️ Claro', 
        icon: '☀️',
        colors: {
            primary: '#3b82f6',     // blue-500
            secondary: '#8b5cf6',   // violet-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#374151',     // gray-700
            base: '#ffffff',        // white
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
        }
    },
    { 
        value: 'dark', 
        name: '🌙 Oscuro', 
        icon: '🌙',
        colors: {
            primary: '#60a5fa',     // blue-400
            secondary: '#a78bfa',   // violet-400
            accent: '#22d3ee',      // cyan-400
            neutral: '#e5e7eb',     // gray-200
            base: '#1f2937',        // gray-800
            info: '#38bdf8',        // sky-400
            success: '#4ade80',     // green-400
            warning: '#fbbf24',     // amber-400
            error: '#f87171',       // red-400
        }
    },
    { 
        value: 'cupcake', 
        name: '🧁 Cupcake', 
        icon: '🧁',
        colors: {
            primary: '#7cec48ff',     // pink-500
            secondary: '#f97316',   // orange-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#64748b',     // slate-500
            base: '#fef7f0',        // orange-50
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
        }
    },
    { 
        value: 'emerald', 
        name: '💚 Esmeralda', 
        icon: '💚',
        colors: {
            primary: '#10b981',     // emerald-500
            secondary: '#059669',   // emerald-600
            accent: '#06b6d4',      // cyan-500
            neutral: '#374151',     // gray-700
            base: '#ecfdf5',        // green-50
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
        }
    },
    { 
        value: 'synthwave', 
        name: '🌆 Synthwave', 
        icon: '🌆',
        colors: {
            primary: '#e879f9',     // fuchsia-400
            secondary: '#a855f7',   // purple-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#f3e8ff',     // violet-50
            base: '#1e1b4b',        // indigo-900
            info: '#8b5cf6',        // violet-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
        }
    },
]

// Estado global del tema
const theme = ref('light')
const isInitialized = ref(false)

// Función para obtener el tema guardado en localStorage
const getStoredTheme = () => {
    if (typeof window !== 'undefined') {
        try {
            const stored = localStorage.getItem('theme') || 'light'
            // Verificar que el tema guardado esté en la lista de disponibles
            const isValidTheme = availableThemes.some(t => t.value === stored)
            return isValidTheme ? stored : 'light'
        } catch (error) {
            console.warn('Error al leer el tema desde localStorage:', error)
            return 'light'
        }
    }
    return 'light'
}

// Función para aplicar el tema al DOM y guardarlo en localStorage
const applyTheme = (newTheme) => {
    if (typeof document !== 'undefined') {
        const themeConfig = availableThemes.find(t => t.value === newTheme)
        if (!themeConfig) return
        
        try {
            // Aplicar variables CSS personalizadas al root
            const root = document.documentElement
            
            Object.entries(themeConfig.colors).forEach(([key, value]) => {
                root.style.setProperty(`--color-${key}`, value)
            })
            
            // Aplicar clase dark para Tailwind si es un tema oscuro
            const darkThemes = ['dark', 'synthwave']
            if (darkThemes.includes(newTheme)) {
                root.classList.add('dark')
            } else {
                root.classList.remove('dark')
            }
            
            // Aplicar clase del tema
            root.setAttribute('data-theme', newTheme)
            
            // Cambiar el color de fondo del body
            document.body.style.backgroundColor = themeConfig.colors.base
            
            // Guardar en localStorage
            localStorage.setItem('theme', newTheme)
            theme.value = newTheme
            
            console.log('Tema aplicado:', newTheme)
            console.log('Colores:', themeConfig.colors)
        } catch (error) {
            console.error('Error al aplicar el tema:', error)
        }
    }
}

// Inicializar el tema solo una vez
const initializeTheme = () => {
    if (!isInitialized.value && typeof window !== 'undefined') {
        const storedTheme = getStoredTheme()
        theme.value = storedTheme
        applyTheme(storedTheme)
        isInitialized.value = true
        console.log('Tema inicializado:', storedTheme)
    }
}

// Observar cambios en el tema
watch(theme, (newTheme) => {
    if (isInitialized.value) {
        applyTheme(newTheme)
    }
})

export function useTheme() {
    // Inicializar en el primer uso
    onMounted(() => {
        initializeTheme()
    })
    
    // Si ya estamos en el navegador, inicializar inmediatamente
    if (typeof window !== 'undefined' && !isInitialized.value) {
        initializeTheme()
    }

    const toggleTheme = () => {
        const currentIndex = availableThemes.findIndex(t => t.value === theme.value)
        const nextIndex = (currentIndex + 1) % availableThemes.length
        const newTheme = availableThemes[nextIndex].value
        theme.value = newTheme
        console.log('Toggleando tema a:', newTheme)
    }

    const setTheme = (newTheme) => {
        const isValidTheme = availableThemes.some(t => t.value === newTheme)
        if (isValidTheme) {
            theme.value = newTheme
        }
    }

    const getCurrentThemeInfo = () => {
        return availableThemes.find(t => t.value === theme.value) || availableThemes[0]
    }

    const getThemeColors = () => {
        const currentTheme = getCurrentThemeInfo()
        return currentTheme.colors
    }

    return {
        theme: theme,
        availableThemes,
        toggleTheme,
        setTheme,
        getCurrentThemeInfo,
        getThemeColors,
        isInitialized
    }
}