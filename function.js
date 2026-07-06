'use strict';

// =============================================
// STATE
// =============================================
const state = {
    current: 'home',
    group: null
};

// =============================================
// ELEMENTS
// =============================================
const sidebar        = document.getElementById('sidebar');
const mainContent    = document.getElementById('main-content');
const mobileMenuBtn  = document.getElementById('mobile-menu-btn');

// =============================================
// VIEW NAVIGATION
// =============================================
function showView(id, group) {
    // Hide all views
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));

    // Show target
    const target = document.getElementById(`view-${id}`);
    if (!target) return;
    target.classList.add('active');

    // Update state
    state.current = id;
    state.group = group || null;

    // Scroll content to top
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Update sidebar active item
    updateSidebarActive(id);

    // Update URL hash for deep linking (without reloading)
    const hash = id === 'home' ? '' : `#${id}`;
    history.replaceState(null, '', hash || window.location.pathname);

    // Close mobile sidebar if open
    closeMobileSidebar();
}

function updateSidebarActive(id) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.toggle('active', item.dataset.id === id);
    });
}

// =============================================
// SIDEBAR NAV CLICKS
// =============================================
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();
        const id    = item.dataset.id;
        const group = item.dataset.group;
        if (!id) return;
        showView(id, group);
    });
});

// =============================================
// PREV / NEXT BUTTONS IN DETAIL VIEW
// =============================================
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id    = btn.dataset.id;
        const group = btn.dataset.group;
        if (!id) return;
        showView(id, group);
    });
});

// =============================================
// SECTION TOGGLES (Principales / Secundarias)
// =============================================
document.querySelectorAll('.nav-section-toggle').forEach(toggle => {
    toggle.addEventListener('click', () => {
        const targetId = toggle.dataset.target;
        const list     = document.getElementById(targetId);
        if (!list) return;

        const isCollapsed = list.classList.contains('collapsed');

        if (isCollapsed) {
            list.classList.remove('collapsed');
            toggle.classList.add('active');
        } else {
            list.classList.add('collapsed');
            toggle.classList.remove('active');
        }
    });
});

// =============================================
// MOBILE SIDEBAR
// =============================================
function openMobileSidebar() {
    sidebar.classList.add('open');
    mobileMenuBtn.setAttribute('aria-expanded', 'true');
    mobileMenuBtn.textContent = '✕';
}

function closeMobileSidebar() {
    sidebar.classList.remove('open');
    mobileMenuBtn.setAttribute('aria-expanded', 'false');
    mobileMenuBtn.textContent = '☰';
}

mobileMenuBtn.addEventListener('click', () => {
    if (sidebar.classList.contains('open')) {
        closeMobileSidebar();
    } else {
        openMobileSidebar();
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', e => {
    if (
        window.innerWidth <= 680 &&
        sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        e.target !== mobileMenuBtn
    ) {
        closeMobileSidebar();
    }
});

// =============================================
// KEYBOARD NAVIGATION
// =============================================
document.addEventListener('keydown', e => {
    if (state.current === 'home') return;

    // Arrow keys navigate prev/next within current view
    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        const view   = document.getElementById(`view-${state.current}`);
        if (!view) return;

        const dir    = e.key === 'ArrowRight' ? '.nav-btn-next' : '.nav-btn-prev';
        const btn    = view.querySelector(dir);
        if (btn) {
            e.preventDefault();
            btn.click();
        }
    }

    // Escape goes home
    if (e.key === 'Escape') {
        showView('home', null);
    }
});

// =============================================
// CONSENSUS BAR ANIMATION ON VIEW LOAD
// =============================================
function animateBars(viewEl) {
    if (!viewEl) return;
    viewEl.querySelectorAll('.consensus-fill').forEach(bar => {
        const target = bar.style.width;
        bar.style.width = '0%';
        // Trigger reflow
        void bar.offsetWidth;
        bar.style.width = target;
    });
}

// Patch showView to animate bars
const _showView = showView;
window.showViewWithAnimation = function(id, group) {
    _showView(id, group);
    const view = document.getElementById(`view-${id}`);
    animateBars(view);
};

document.querySelectorAll('.nav-item, .nav-btn').forEach(el => {
    el.addEventListener('click', () => {
        const id    = el.dataset.id;
        const group = el.dataset.group;
        if (!id) return;
        const view  = document.getElementById(`view-${id}`);
        animateBars(view);
    });
});

// =============================================
// DEEP LINK ON PAGE LOAD
// =============================================
(function handleInitialHash() {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const view = document.getElementById(`view-${hash}`);
        if (view) {
            // Determine group from the nav item
            const navItem = document.querySelector(`.nav-item[data-id="${hash}"]`);
            const group   = navItem ? navItem.dataset.group : null;

            // Expand the right section if needed
            if (group === 'secundario') {
                const list   = document.getElementById('nav-secundario');
                const toggle = document.querySelector('[data-target="nav-secundario"]');
                if (list)   list.classList.remove('collapsed');
                if (toggle) toggle.classList.add('active');
            }

            showView(hash, group);
            animateBars(view);
            return;
        }
    }
    // Default: show home
    showView('home', null);
})();