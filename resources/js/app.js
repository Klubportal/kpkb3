import './bootstrap';
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

// Icon Libraries
import '@fortawesome/fontawesome-free/css/all.css';
import { createIcons, icons } from 'lucide';
import '@phosphor-icons/web/bold';

// Alpine Plugins registrieren
Alpine.plugin(intersect);
Alpine.plugin(focus);
Alpine.plugin(collapse);

// Alpine Data Components
Alpine.data('darkMode', () => ({
    dark: localStorage.getItem('darkMode') === 'true',

    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('darkMode', this.dark);
        this.updateTheme();
    },

    updateTheme() {
        if (this.dark) {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    },

    init() {
        this.updateTheme();
    }
}));

Alpine.data('scrollReveal', () => ({
    revealed: false,

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.revealed = true;
                    this.$el.classList.add('active');
                }
            });
        }, {
            threshold: 0.1
        });

        observer.observe(this.$el);
    }
}));

Alpine.data('lazyImage', () => ({
    loaded: false,

    init() {
        const img = this.$el;
        const src = img.dataset.src;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        img.src = src;
                        img.onload = () => {
                            this.loaded = true;
                            img.classList.add('loaded');
                        };
                        observer.unobserve(img);
                    }
                });
            });

            observer.observe(img);
        } else {
            img.src = src;
            this.loaded = true;
        }
    }
}));

Alpine.data('counter', (target, duration = 2000) => ({
    current: 0,
    target: target,

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animate();
                    observer.unobserve(this.$el);
                }
            });
        });

        observer.observe(this.$el);
    },

    animate() {
        const start = 0;
        const increment = this.target / (duration / 16); // 60fps

        const timer = setInterval(() => {
            this.current += increment;
            if (this.current >= this.target) {
                this.current = this.target;
                clearInterval(timer);
            }
        }, 16);
    },

    get displayValue() {
        return Math.round(this.current);
    }
}));

Alpine.data('carousel', () => ({
    current: 0,
    items: [],
    autoplay: true,
    interval: 5000,
    timer: null,

    init() {
        this.items = Array.from(this.$el.querySelectorAll('[data-carousel-item]'));
        if (this.autoplay) {
            this.startAutoplay();
        }
    },

    next() {
        this.current = (this.current + 1) % this.items.length;
        this.resetAutoplay();
    },

    prev() {
        this.current = (this.current - 1 + this.items.length) % this.items.length;
        this.resetAutoplay();
    },

    goto(index) {
        this.current = index;
        this.resetAutoplay();
    },

    startAutoplay() {
        this.timer = setInterval(() => {
            this.next();
        }, this.interval);
    },

    resetAutoplay() {
        if (this.autoplay) {
            clearInterval(this.timer);
            this.startAutoplay();
        }
    }
}));

Alpine.data('modal', () => ({
    open: false,

    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },

    hide() {
        this.open = false;
        document.body.style.overflow = 'auto';
    },

    toggle() {
        this.open ? this.hide() : this.show();
    }
}));

Alpine.data('dropdown', () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    }
}));

Alpine.data('tabs', (defaultTab = 0) => ({
    active: defaultTab,

    select(index) {
        this.active = index;
    }
}));

Alpine.data('accordion', () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    }
}));

// Global Functions
window.scrollToTop = function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

window.scrollToElement = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
};

// Back to Top Button
window.addEventListener('scroll', () => {
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        if (window.scrollY > 300) {
            backToTop.classList.remove('hidden');
            backToTop.classList.add('animate-fadeIn');
        } else {
            backToTop.classList.add('hidden');
        }
    }
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('header.navbar-scroll');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-lg', 'bg-opacity-95');
        } else {
            navbar.classList.remove('shadow-lg', 'bg-opacity-95');
        }
    }
});

// Alpine starten
window.Alpine = Alpine;
Alpine.start();

// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
// Console Info
console.log('🚀 Klubportal Frontend loaded');
console.log('🎨 Alpine.js v3 + TailwindCSS v4 + DaisyUI');
console.log('⚡ Modern, Dynamic & Responsive');
