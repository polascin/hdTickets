/* Native-like navigation controller for HD Tickets PWA */

export default class AppShellNav {
  constructor({
    contentSelector = 'main',
    transition = 'fade',
    cacheSize = 10,
  } = {}) {
    this.container = document.querySelector(contentSelector);
    this.transition = transition;
    this.cache = new Map();
    this.cacheOrder = [];
    this.cacheSize = cacheSize;
    this.prefetchLinks();
    this.bindClicks();
    this.bindPopstate();
  }

  prefetchLinks() {
    const links = document.querySelectorAll(
      'a[href^="/"]:not([data-no-prefetch])'
    );
    links.forEach(link => {
      link.addEventListener('mouseenter', () => this.prefetch(link.href), {
        passive: true,
      });
      link.addEventListener('touchstart', () => this.prefetch(link.href), {
        passive: true,
      });
    });
  }

  async prefetch(url) {
    if (this.cache.has(url)) return;
    try {
      const res = await fetch(url, {
        headers: { 'X-Prefetch': '1' },
        credentials: 'same-origin',
      });
      if (!res.ok) return;
      const html = await res.text();
      this.saveCache(url, html);
    } catch (e) {
      // ignore
    }
  }

  bindClicks() {
    document.addEventListener('click', e => {
      const a = e.target.closest('a');
      if (!a) return;
      const url = new URL(a.href, location.origin);
      if (url.origin !== location.origin) return;
      if (
        a.target === '_blank' ||
        a.hasAttribute('download') ||
        a.dataset.noSpa === 'true'
      )
        return;
      e.preventDefault();
      this.navigate(url.pathname + url.search + url.hash, { push: true });
    });
  }

  bindPopstate() {
    window.addEventListener('popstate', () => {
      this.navigate(location.pathname + location.search + location.hash, {
        push: false,
      });
    });
  }

  async navigate(url, { push = true } = {}) {
    try {
      let html = this.cache.get(url);
      if (!html) {
        const res = await fetch(url, {
          headers: { 'X-Partial': '1' },
          credentials: 'same-origin',
        });
        html = await res.text();
        this.saveCache(url, html);
      }
      if (push) history.pushState({}, '', url);
      this.swapContent(html);
      this.updateActiveNav(url);
      this.scrollRestore();
    } catch (e) {
      location.href = url; // fallback
    }
  }

  saveCache(url, html) {
    this.cache.set(url, html);
    this.cacheOrder.push(url);
    if (this.cacheOrder.length > this.cacheSize) {
      const oldest = this.cacheOrder.shift();
      this.cache.delete(oldest);
    }
  }

  swapContent(html) {
    if (!this.container) return;
    const tpl = document.createElement('template');
    tpl.innerHTML = html.trim();
    const next = tpl.content.querySelector('main');
    if (!next) {
      this.container.innerHTML = html;
      return;
    }

    this.animateOut(this.container, () => {
      this.container.replaceWith(next);
      this.container = next;
      this.animateIn(this.container);
      document.dispatchEvent(new CustomEvent('route:changed'));
    });
  }

  animateOut(el, done) {
    if (this.transition === 'slide') {
      el.style.transition = 'transform 200ms ease, opacity 200ms ease';
      el.style.transform = 'translateX(10px)';
      el.style.opacity = '0.001';
      setTimeout(done, 200);
    } else {
      // fade
      el.style.transition = 'opacity 120ms ease';
      el.style.opacity = '0.001';
      setTimeout(done, 120);
    }
  }

  animateIn(el) {
    requestAnimationFrame(() => {
      el.style.transition = 'none';
      el.style.opacity = '0.001';
      el.style.transform = 'translateX(0)';
      requestAnimationFrame(() => {
        el.style.transition = 'opacity 160ms ease';
        el.style.opacity = '1';
      });
    });
  }

  updateActiveNav(url) {
    document.querySelectorAll('nav a').forEach(a => {
      try {
        const u = new URL(a.href);
        if (u.pathname === new URL(url, location.origin).pathname) {
          a.classList.add('text-indigo-600');
        } else {
          a.classList.remove('text-indigo-600');
        }
      } catch (_) {}
    });
  }

  scrollRestore() {
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    window.scrollTo({ top: 0, behavior: 'instant' });
  }
}
