// Profile Page Script (extracted from inline Blade)
// Provides Alpine.js component and supporting utilities with improved structure

(function () {
  'use strict';

  function safeFetch(url, options = {}, { retries = 1, timeoutMs = 10000 } = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), timeoutMs);

    return fetch(url, { ...options, signal: controller.signal })
      .then(r => {
        clearTimeout(timeout);
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .catch(err => {
        clearTimeout(timeout);
        if (retries > 0) {
          return safeFetch(url, options, { retries: retries - 1, timeoutMs });
        }
        throw err;
      });
  }

  window.profilePage = function profilePage() {
    const state = {
      statsLoading: false,
      photoUploadLoading: false,
      lastUpdated: new Date().toLocaleTimeString(),
      autoRefreshHandle: null,

      init() {
        try {
          this.setupIntersectionObservers();
          this.startAutoRefresh();
          this.handleVisibilityChange();
          this.announceToScreenReader('Profile page loaded successfully');
        } catch (e) {
          console.error('Profile init error', e);
        }
      },

      triggerPhotoUpload() {
        document.getElementById('photo-upload')?.click();
      },

      async handlePhotoUpload(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        if (!this.validatePhotoFile(file)) return;
        this.photoUploadLoading = true;
        try {
          const formData = new FormData();
          formData.append('photo', file);
          formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
          const response = await fetch('/profile/photo/upload', { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
          const data = await response.json();
          if (data.success) {
            this.announceToScreenReader('Profile photo updated successfully');
            window.location.reload();
          } else { throw new Error(data.message || 'Upload failed'); }
        } catch (e) {
          console.error('Upload error', e);
          this.showErrorToast('Error uploading photo: ' + e.message);
          this.announceToScreenReader('Error uploading photo');
        } finally { this.photoUploadLoading = false; }
      },

      validatePhotoFile(file) {
        if (file.size > 5 * 1024 * 1024) { this.showErrorToast('File size must be < 5MB'); return false; }
        const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) { this.showErrorToast('Only JPEG, PNG, WebP'); return false; }
        return true;
      },

      async refreshStats() {
        if (this.statsLoading) return;
        this.statsLoading = true;
        this.announceToScreenReader('Refreshing statistics');
        try {
          const data = await safeFetch('/profile/stats', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
          if (data.success) {
            this.updateStatsDisplay(data.stats);
            this.lastUpdated = new Date().toLocaleTimeString();
            this.announceToScreenReader('Statistics updated');
          } else throw new Error(data.message || 'Failed fetch');
        } catch (e) {
          console.warn('Stats refresh failed', e);
          this.showErrorToast('Failed to refresh statistics');
          this.announceToScreenReader('Failed to refresh statistics');
        } finally { this.statsLoading = false; }
      },

      updateStatsDisplay(stats) {
        const mapping = {
          'monitored-events': stats.monitored_events,
          'total-alerts': stats.total_alerts,
          'active-searches': stats.active_searches,
          'recent-purchases': stats.recent_purchases
        };
        Object.entries(mapping).forEach(([id, val]) => this.animateStatUpdate(id, val));
        if (stats.profile_completion) this.updateProgressRing('profile-completion', stats.profile_completion);
        if (stats.security_score) this.updateProgressRing('security-score', stats.security_score);
      },

      animateStatUpdate(id, val) {
        const el = document.getElementById(id + '-title');
        if (!el) return;
        if (el.textContent === String(val)) return;
        el.classList.add('stat-updating');
        setTimeout(() => {
          el.textContent = val;
          el.classList.remove('stat-updating');
          el.classList.add('stat-updated');
          setTimeout(() => el.classList.remove('stat-updated'), 500);
        }, 150);
      },

      updateProgressRing(id, pct) {
        const ring = document.querySelector(`#${id} circle:last-child`);
        const txt = document.querySelector(`#${id} .progress-text`);
        if (!ring || !txt) return;
        const circ = 2 * Math.PI * 40;
        ring.style.strokeDashoffset = circ * (1 - pct / 100);
        txt.textContent = pct + '%';
      },

      startAutoRefresh() {
        if (this.autoRefreshHandle) clearInterval(this.autoRefreshHandle);
        this.autoRefreshHandle = setInterval(() => { if (!document.hidden) this.refreshStats(); }, 5 * 60 * 1000);
      },

      handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => { if (!document.hidden) setTimeout(() => this.refreshStats(), 1000); });
      },

      setupIntersectionObservers() {
        if (!('IntersectionObserver' in window)) {
          document.querySelectorAll('.enhanced-feature').forEach(el => el.classList.add('visible'));
          return;
        }
        const obs = new IntersectionObserver(entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.1 });
        document.querySelectorAll('.enhanced-feature').forEach(el => obs.observe(el));
      },

      announceToScreenReader(message) {
        const el = document.createElement('div');
        el.setAttribute('aria-live', 'polite');
        el.className = 'sr-only';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => { if (document.body.contains(el)) document.body.removeChild(el); }, 1000);
      },

      showErrorToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed';
        toast.style.cssText = 'top:20px;right:20px;z-index:1060;';
        toast.role = 'alert';
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button></div>`;
        document.body.appendChild(toast);
        const closeBtn = toast.querySelector('button');
        closeBtn.addEventListener('click', () => toast.remove());
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 5000);
      },

      handleStatClick(type) {
        console.log('Stat clicked:', type);
        this.announceToScreenReader(`Viewing ${type} details`);
      }
    };
    return state;
  };

  // Runtime enhancements
  document.addEventListener('DOMContentLoaded', () => {
    const style = document.createElement('style');
    style.textContent = `.stat-updating{transform:scale(1.1);transition:transform .15s ease;background-color:rgba(59,130,246,.1);} .stat-updated{transform:scale(1);transition:transform .3s ease;background-color:rgba(16,185,129,.1);} @media (prefers-reduced-motion:reduce){.stat-updating,.stat-updated{transform:none!important;transition:none!important;}}`;
    document.head.appendChild(style);
  });
})();
