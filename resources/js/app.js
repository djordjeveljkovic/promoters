import Alpine from 'alpinejs';
import ticketOrder from './ticket_items.js'

Alpine.data('ticketOrder', ticketOrder);
Alpine.start();

/**
 * Mount the Three.js landing scene only when the welcome page is present.
 * Done lazily with `requestIdleCallback` so it doesn't compete with the
 * initial render of admin/promoter pages.
 */
function maybeMountLanding() {
    const container = document.getElementById('landing-scene');
    if (!container) return;

    const boot = async () => {
        const mount = (await import('./landing/scene.js')).default;
        mount(container);
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(boot, { timeout: 800 });
    } else {
        window.setTimeout(boot, 200);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', maybeMountLanding);
} else {
    maybeMountLanding();
}