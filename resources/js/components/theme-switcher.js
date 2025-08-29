import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
  Alpine.data('themeSwitcher', () => ({
    darkMode: false,
    isTransitioning: false,

    init() {
      // Check saved theme preference or system preference
      this.darkMode =
        localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') &&
          window.matchMedia('(prefers-color-scheme: dark)').matches);

      this.applyTheme();

      // Listen for system theme changes
      window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', e => {
          if (!localStorage.getItem('theme')) {
            this.darkMode = e.matches;
            this.applyTheme();
          }
        });
    },

    toggle() {
      if (this.isTransitioning) return;

      this.isTransitioning = true;
      this.darkMode = !this.darkMode;

      // Add transition class
      document.documentElement.classList.add('theme-transitioning');

      this.applyTheme();
      localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');

      // Remove transition class after animation
      setTimeout(() => {
        document.documentElement.classList.remove('theme-transitioning');
        this.isTransitioning = false;
      }, 300);

      // Track theme change
      if (typeof gtag !== 'undefined') {
        gtag('event', 'theme_change', {
          theme: this.darkMode ? 'dark' : 'light',
        });
      }
    },

    applyTheme() {
      if (this.darkMode) {
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
      }

      // Update meta theme-color
      const metaThemeColor = document.querySelector('meta[name="theme-color"]');
      if (metaThemeColor) {
        metaThemeColor.setAttribute(
          'content',
          this.darkMode ? '#1f2937' : '#2563eb'
        );
      }
    },

    get themeIcon() {
      return this.darkMode ? '☀️' : '🌙';
    },

    get themeLabel() {
      return this.darkMode ? 'Light Mode' : 'Dark Mode';
    },
  }));
});
