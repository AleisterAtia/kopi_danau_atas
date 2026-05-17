document.addEventListener('DOMContentLoaded', () => {

    /* ── Scroll reveal ────────────────── */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));

    /* ── Navbar scroll ────────────────── */
    const navbar = document.getElementById('navbar');
    if (navbar) {
        const update = () => {
            if (window.scrollY > 60) {
                navbar.classList.remove('navbar--top');
                navbar.classList.add('navbar--scrolled');
            } else {
                navbar.classList.remove('navbar--scrolled');
                navbar.classList.add('navbar--top');
            }
        };
        update();
        window.addEventListener('scroll', update, { passive: true });
    }
});

/* ── Testimonial carousel ─────────── */
function initTestimonialCarousel() {
    const container = document.getElementById('testimonial-carousel');
    if (!container) return;

    const items = container.querySelectorAll('.testimonial-item');
    if (items.length === 0) return;

    const counter = document.getElementById('testimonial-counter');
    let current = 0;
    let timer = null;

    // Keep all items visible in the DOM but stack them; toggle only via
    // opacity + pointer-events so the CSS transition (`transition-opacity`)
    // can play smoothly. `display:none` would short-circuit the transition.
    function show(index) {
        items.forEach((item, i) => {
            const active = i === index;
            item.style.opacity = active ? '1' : '0';
            item.style.pointerEvents = active ? 'auto' : 'none';
            item.style.zIndex = active ? '1' : '0';
        });
        if (counter) counter.textContent = `${index + 1} / ${items.length}`;
    }

    // Always normalize initial state (Blade ships item 0 as visible, but other
    // items have inline display:none — clear it so opacity transitions can run).
    items.forEach((item) => {
        item.style.display = 'block';
    });

    show(0);

    // Single-item carousel: no nav, no autoplay needed.
    if (items.length <= 1) return;

    window.nextTestimonial = () => {
        current = (current + 1) % items.length;
        show(current);
    };
    window.prevTestimonial = () => {
        current = (current - 1 + items.length) % items.length;
        show(current);
    };

    const startAuto = () => {
        timer = setInterval(() => window.nextTestimonial(), 6000);
    };
    const stopAuto = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    startAuto();

    // Pause auto-advance while the user hovers the carousel.
    container.addEventListener('mouseenter', stopAuto);
    container.addEventListener('mouseleave', startAuto);
}

document.addEventListener('DOMContentLoaded', initTestimonialCarousel);
