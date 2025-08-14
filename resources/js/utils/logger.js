/**
 * Development Logger Utility
 * Provides safe logging that can be disabled in production
 */

class DevLogger {
    constructor() {
        this.isDevelopment = process.env.NODE_ENV === 'development' || process.env.NODE_ENV === undefined;
        this.isEnabled = this.isDevelopment && typeof console !== 'undefined';
    }

    log(...args) {
        if (this.isEnabled) {
            console.log('[HDTickets]', ...args);
        }
    }

    info(...args) {
        if (this.isEnabled) {
            console.info('[HDTickets]', ...args);
        }
    }

    warn(...args) {
        if (this.isEnabled) {
            console.warn('[HDTickets]', ...args);
        }
    }

    error(...args) {
        if (this.isEnabled) {
            console.error('[HDTickets]', ...args);
        }
    }

    debug(...args) {
        if (this.isEnabled) {
            console.debug('[HDTickets]', ...args);
        }
    }

    group(title) {
        if (this.isEnabled && console.group) {
            console.group(`[HDTickets] ${title}`);
        }
    }

    groupEnd() {
        if (this.isEnabled && console.groupEnd) {
            console.groupEnd();
        }
    }

    time(label) {
        if (this.isEnabled && console.time) {
            console.time(`[HDTickets] ${label}`);
        }
    }

    timeEnd(label) {
        if (this.isEnabled && console.timeEnd) {
            console.timeEnd(`[HDTickets] ${label}`);
        }
    }

    table(data) {
        if (this.isEnabled && console.table) {
            console.table(data);
        }
    }

    trace(...args) {
        if (this.isEnabled && console.trace) {
            console.trace('[HDTickets]', ...args);
        }
    }
}

// Create singleton instance
const logger = new DevLogger();

// Export for use
export default logger;

// Make available globally for non-module usage
if (typeof window !== 'undefined') {
    window.hdLogger = logger;
}
