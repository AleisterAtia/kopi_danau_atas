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
    const counter = document.getElementById('testimonial-counter');
    let current = 0;

    function show(index) {
        items.forEach((item, i) => {
            item.style.display = i === index ? 'block' : 'none';
            item.style.opacity = i === index ? '1' : '0';
        });
        if (counter) counter.textContent = `${index + 1} / ${items.length}`;
    }

    window.nextTestimonial = () => { current = (current + 1) % items.length; show(current); };
    window.prevTestimonial = () => { current = (current - 1 + items.length) % items.length; show(current); };

    show(0);

    // Auto advance every 6 seconds
    setInterval(() => { window.nextTestimonial(); }, 6000);
}

document.addEventListener('DOMContentLoaded', initTestimonialCarousel);
