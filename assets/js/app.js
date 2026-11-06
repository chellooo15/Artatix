// Artatix - Main JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Navbar toggle + a11y
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');
    const accDropdown = document.getElementById('accountDropdown');
    const accToggle = document.getElementById('accountToggle');

    const closeMenu = () => {
        if (!toggle || !menu) return;
        menu.classList.remove('active');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };
    const closeAccount = () => {
        if (!accDropdown || !accToggle) return;
        accDropdown.classList.remove('open');
        accToggle.setAttribute('aria-expanded', 'false');
    };
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            menu.classList.toggle('active');
            toggle.classList.toggle('active');
            document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
            if (menu.classList.contains('active')) closeAccount();
        });
        
        // Close menu on link click
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.remove('active');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        // Close on outside click / Escape
        document.addEventListener('click', (e) => {
            if (menu.classList.contains('active') && !menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('active');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && menu.classList.contains('active')) {
                menu.classList.remove('active');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                toggle.focus();
            }
        });
    }

    // Theme toggle (light/dark, persisted)
    const themeKey = 'artatix-theme';
    const metaTheme = document.getElementById('metaThemeColor');
    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem(themeKey, theme); } catch (e) {}
        if (metaTheme) metaTheme.setAttribute('content', theme === 'dark' ? '#0f172a' : '#6366f1');
        document.querySelectorAll('.theme-toggle').forEach(btn => {
            const dark = theme === 'dark';
            btn.setAttribute('aria-pressed', String(dark));
            btn.setAttribute('aria-label', dark ? 'Ganti ke tema terang' : 'Ganti ke tema gelap');
            btn.setAttribute('title', dark ? 'Ganti ke tema terang' : 'Ganti ke tema gelap');
        });
    };
    const currentTheme = () => document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    applyTheme(currentTheme());
    document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
    });

    // Account dropdown (hover handled by CSS; JS covers touch/click + a11y)
    const accMenu = document.getElementById('accountMenu');

    if (accDropdown && accToggle && accMenu) {
        const setOpen = (open) => {
            accDropdown.classList.toggle('open', open);
            accToggle.setAttribute('aria-expanded', String(open));
        };
        accToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !accDropdown.classList.contains('open');
            setOpen(willOpen);
            if (willOpen) closeMenu();
        });
        document.addEventListener('click', (e) => {
            if (accDropdown.classList.contains('open') && !accDropdown.contains(e.target)) {
                setOpen(false);
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && accDropdown.classList.contains('open')) {
                setOpen(false);
                accToggle.focus();
            }
        });
        accMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => setOpen(false));
        });
    }

    // Close flash on button
    document.querySelectorAll('.flash-close').forEach(btn => {
        btn.addEventListener('click', () => btn.parentElement?.remove());
    });
    
    // Auto-dismiss flash messages
    document.querySelectorAll('.flash').forEach(flash => {
        setTimeout(() => {
            flash.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    });
    
    // Form validation enhancement
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            form.querySelectorAll('[required]').forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
            });
            if (!valid) e.preventDefault();
        });
    });
    
    // Quantity input handlers (emit input/change for summary sync)
    document.querySelectorAll('.qty-input').forEach(input => {
        const btnMinus = input.parentElement?.querySelector('.qty-minus');
        const btnPlus = input.parentElement?.querySelector('.qty-plus');
        const getMin = () => parseInt(input.min) || 1;
        const getMax = () => parseInt(input.max) || 10;
        
        const fire = () => {
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        
        if (btnMinus) {
            btnMinus.addEventListener('click', () => {
                const val = parseInt(input.value) || getMin();
                if (val > getMin()) { input.value = val - 1; fire(); }
            });
        }
        if (btnPlus) {
            btnPlus.addEventListener('click', () => {
                const val = parseInt(input.value) || getMin();
                if (val < getMax()) { input.value = val + 1; fire(); }
            });
        }
        // Clamp on manual input
        input.addEventListener('input', () => {
            let v = parseInt(input.value) || getMin();
            const min = getMin(), max = getMax();
            if (v < min) v = min;
            if (v > max) v = max;
            if (String(v) !== input.value) input.value = String(v);
        });
    });
    
    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Clickable table rows (e.g. /user/orders): row navigates, links/buttons inside still work
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.addEventListener('click', (e) => {
            if (e.target.closest('a, button, input, select, textarea')) return;
            const href = row.dataset.href;
            if (!href) return;
            if (e.ctrlKey || e.metaKey) {
                window.open(href, '_blank', 'noopener');
            } else {
                window.location.href = href;
            }
        });
        row.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && row.dataset.href) {
                if (e.target !== row) return;
                e.preventDefault();
                window.location.href = row.dataset.href;
            }
        });
    });
    
    // Ticket class selection is handled per-page in events/show.php via the
    // radio `change` event (respects disabled/sold-out + keyboard). No generic handler here.

    // PO code toggle (if checkbox exists)
    const poCodeInput = document.getElementById('po_code');
    const poCodeWrapper = document.getElementById('poCodeWrapper');
    const poToggle = document.getElementById('poToggle');
    
    if (poToggle && poCodeWrapper) {
        poToggle.addEventListener('change', () => {
            poCodeWrapper.style.display = poToggle.checked ? 'block' : 'none';
            if (poCodeInput) poCodeInput.required = poToggle.checked;
        });
    }

    // Countdown for expiry (data-countdown="2026-09-25 12:00:00")
    const countdownEls = document.querySelectorAll('[data-countdown]');
    if (countdownEls.length) {
        const tick = () => {
            const now = Date.now();
            countdownEls.forEach(el => {
                const target = new Date(el.dataset.countdown.replace(' ', 'T')).getTime();
                let diff = target - now;
                if (isNaN(target)) { el.textContent = el.dataset.countdown; return; }
                if (diff <= 0) {
                    el.textContent = 'Kedaluwarsa';
                    el.classList.add('urgent');
                    const btn = document.querySelector('[data-pay-btn]');
                    if (btn) { btn.disabled = true; btn.textContent = 'Kedaluwarsa'; }
                    return;
                }
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                el.classList.toggle('urgent', diff < 10*60*1000);
            });
        };
        tick();
        setInterval(tick, 1000);
    }
});

// Utility functions
const Artatix = {
    formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    },
    
    showToast(message, type = 'info') {
        const container = document.getElementById('flashContainer') || (() => {
            const c = document.createElement('div');
            c.id = 'flashContainer';
            c.className = 'flash-container';
            document.body.appendChild(c);
            return c;
        })();
        
        const toast = document.createElement('div');
        toast.className = `flash flash-${type}`;
        toast.innerHTML = `
            <span class="flash-message">${message}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    },
    
    confirm(message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    },
    
    ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('input[name="_token"]')?.value || ''
            }
        };
        return fetch(url, { ...defaults, ...options }).then(res => res.json());
    }
};

// Make globally available
window.Artatix = Artatix;