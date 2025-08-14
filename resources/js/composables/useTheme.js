import { ref, computed, watch, onMounted } from 'vue'
import { useStorage } from '@vueuse/core'

// Theme configuration
const THEMES = {
  light: {
    name: 'Light',
    colors: {
      primary: '#2563eb',
      secondary: '#7c3aed',
      accent: '#06b6d4',
      success: '#10b981',
      warning: '#f59e0b',
      error: '#ef4444',
      info: '#3b82f6'
    }
  },
  dark: {
    name: 'Dark',
    colors: {
      primary: '#3b82f6',
      secondary: '#8b5cf6',
      accent: '#22d3ee',
      success: '#34d399',
      warning: '#fbbf24',
      error: '#f87171',
      info: '#60a5fa'
    }
  },
  system: {
    name: 'System',
    colors: null // Uses system preference
  }
}

// Global theme state
const currentTheme = useStorage('hd-tickets-theme', 'system')
const isDarkMode = ref(false)
const systemPrefersDark = ref(false)

// Media query for system preference
let mediaQuery = null

export function useTheme() {
  // Initialize system preference detection
  const initializeSystemPreference = () => {
    if (typeof window !== 'undefined') {
      mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      systemPrefersDark.value = mediaQuery.matches
      
      // Listen for system preference changes
      mediaQuery.addEventListener('change', (e) => {
        systemPrefersDark.value = e.matches
        if (currentTheme.value === 'system') {
          updateDarkMode()
        }
      })
    }
  }

  // Update dark mode based on current theme
  const updateDarkMode = () => {
    if (currentTheme.value === 'system') {
      isDarkMode.value = systemPrefersDark.value
    } else {
      isDarkMode.value = currentTheme.value === 'dark'
    }
  }

  // Apply theme to document
  const applyTheme = () => {
    if (typeof document !== 'undefined') {
      const html = document.documentElement
      
      // Remove existing theme classes
      html.classList.remove('dark', 'light')
      
      // Add appropriate class
      if (isDarkMode.value) {
        html.classList.add('dark')
      } else {
        html.classList.add('light')
      }

      // Update CSS custom properties
      const theme = THEMES[currentTheme.value === 'system' ? (isDarkMode.value ? 'dark' : 'light') : currentTheme.value]
      if (theme.colors) {
        const root = document.documentElement
        Object.entries(theme.colors).forEach(([key, value]) => {
          root.style.setProperty(`--theme-${key}`, value)
        })
      }

      // Dispatch custom event for theme change
      window.dispatchEvent(new CustomEvent('theme-changed', {
        detail: {
          theme: currentTheme.value,
          isDark: isDarkMode.value
        }
      }))
    }
  }

  // Set theme
  const setTheme = (theme) => {
    if (THEMES[theme]) {
      currentTheme.value = theme
      updateDarkMode()
      applyTheme()
    }
  }

  // Toggle between light and dark (ignores system)
  const toggleTheme = () => {
    if (currentTheme.value === 'light') {
      setTheme('dark')
    } else {
      setTheme('light')
    }
  }

  // Get available themes
  const availableThemes = computed(() => 
    Object.entries(THEMES).map(([key, value]) => ({
      value: key,
      name: value.name
    }))
  )

  // Get current theme info
  const themeInfo = computed(() => ({
    current: currentTheme.value,
    isDark: isDarkMode.value,
    systemPrefersDark: systemPrefersDark.value,
    effectiveTheme: currentTheme.value === 'system' 
      ? (systemPrefersDark.value ? 'dark' : 'light')
      : currentTheme.value
  }))

  // Get theme colors
  const themeColors = computed(() => {
    const effectiveTheme = currentTheme.value === 'system' 
      ? (isDarkMode.value ? 'dark' : 'light')
      : currentTheme.value
    return THEMES[effectiveTheme]?.colors || THEMES.light.colors
  })

  // Create CSS variable getter
  const getCSSVar = (name) => {
    if (typeof document !== 'undefined') {
      return getComputedStyle(document.documentElement).getPropertyValue(`--theme-${name}`)
    }
    return null
  }

  // Reactive CSS variables
  const cssVars = computed(() => {
    const colors = themeColors.value
    const vars = {}
    
    Object.entries(colors).forEach(([key, value]) => {
      vars[`--theme-${key}`] = value
    })
    
    return vars
  })

  // Theme-aware component classes
  const getThemeClass = (lightClass, darkClass) => {
    return computed(() => isDarkMode.value ? darkClass : lightClass)
  }

  // Get contrasting text color
  const getContrastingTextColor = (backgroundColor) => {
    // Simple contrast calculation
    if (!backgroundColor) return isDarkMode.value ? '#ffffff' : '#000000'
    
    // Remove # if present
    const color = backgroundColor.replace('#', '')
    
    // Calculate luminance
    const r = parseInt(color.substr(0, 2), 16)
    const g = parseInt(color.substr(2, 2), 16)
    const b = parseInt(color.substr(4, 2), 16)
    
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
    
    return luminance > 0.5 ? '#000000' : '#ffffff'
  }

  // Generate theme-aware gradient
  const createGradient = (direction = 'to right', colors = []) => {
    if (colors.length === 0) {
      colors = isDarkMode.value 
        ? ['#374151', '#1f2937'] 
        : ['#f3f4f6', '#e5e7eb']
    }
    
    return `linear-gradient(${direction}, ${colors.join(', ')})`
  }

  // Animation preferences
  const prefersReducedMotion = ref(false)
  
  const initializeMotionPreference = () => {
    if (typeof window !== 'undefined') {
      const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
      prefersReducedMotion.value = motionQuery.matches
      
      motionQuery.addEventListener('change', (e) => {
        prefersReducedMotion.value = e.matches
      })
    }
  }

  // Watch for theme changes
  watch(currentTheme, () => {
    updateDarkMode()
    applyTheme()
  })

  watch(systemPrefersDark, () => {
    if (currentTheme.value === 'system') {
      updateDarkMode()
      applyTheme()
    }
  })

  // Initialize on mount
  onMounted(() => {
    initializeSystemPreference()
    initializeMotionPreference()
    updateDarkMode()
    applyTheme()
  })

  return {
    // State
    currentTheme,
    isDarkMode: computed(() => isDarkMode.value),
    systemPrefersDark: computed(() => systemPrefersDark.value),
    prefersReducedMotion: computed(() => prefersReducedMotion.value),
    
    // Theme info
    themeInfo,
    themeColors,
    availableThemes,
    cssVars,
    
    // Methods
    setTheme,
    toggleTheme,
    getCSSVar,
    getThemeClass,
    getContrastingTextColor,
    createGradient,
    
    // Utilities
    applyTheme
  }
}

// Global theme instance for use outside components
let globalTheme = null

export function setupGlobalTheme() {
  if (!globalTheme) {
    globalTheme = useTheme()
  }
  return globalTheme
}

// Export theme constants
export { THEMES }

// CSS utility classes generator
export function generateThemeClasses() {
  const theme = globalTheme || setupGlobalTheme()
  
  return {
    // Background classes
    'bg-primary': `background-color: ${theme.themeColors.value.primary}`,
    'bg-secondary': `background-color: ${theme.themeColors.value.secondary}`,
    'bg-accent': `background-color: ${theme.themeColors.value.accent}`,
    'bg-success': `background-color: ${theme.themeColors.value.success}`,
    'bg-warning': `background-color: ${theme.themeColors.value.warning}`,
    'bg-error': `background-color: ${theme.themeColors.value.error}`,
    'bg-info': `background-color: ${theme.themeColors.value.info}`,
    
    // Text classes
    'text-primary': `color: ${theme.themeColors.value.primary}`,
    'text-secondary': `color: ${theme.themeColors.value.secondary}`,
    'text-accent': `color: ${theme.themeColors.value.accent}`,
    'text-success': `color: ${theme.themeColors.value.success}`,
    'text-warning': `color: ${theme.themeColors.value.warning}`,
    'text-error': `color: ${theme.themeColors.value.error}`,
    'text-info': `color: ${theme.themeColors.value.info}`,
    
    // Border classes
    'border-primary': `border-color: ${theme.themeColors.value.primary}`,
    'border-secondary': `border-color: ${theme.themeColors.value.secondary}`,
    'border-accent': `border-color: ${theme.themeColors.value.accent}`,
    'border-success': `border-color: ${theme.themeColors.value.success}`,
    'border-warning': `border-color: ${theme.themeColors.value.warning}`,
    'border-error': `border-color: ${theme.themeColors.value.error}`,
    'border-info': `border-color: ${theme.themeColors.value.info}`
  }
}
