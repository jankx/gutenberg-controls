/**
 * Jankx Gutenberg Controls - Frontend Script
 * 
 * Handles entrance animations and scroll-triggered reveals for Jankx blocks.
 */

document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.jankx-animate-on-scroll, .jankx-animate-infinite');

    if ('IntersectionObserver' in window) {
        const animationObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;

                    // Handle delay from data attribute or computed style variable
                    const delay = el.dataset.jankxDelay ||
                        getComputedStyle(el).getPropertyValue('--jankx-delay') ||
                        0;

                    setTimeout(() => {
                        el.classList.add('jankx-animated');

                        // Handle staggered children if enabled
                        if (el.classList.contains('jankx-stagger-children')) {
                            const children = el.querySelectorAll('.jankx-stagger-item');
                            const staggerDelay = parseInt(el.dataset.jankxStagger) || 100;

                            children.forEach((child, index) => {
                                setTimeout(() => {
                                    child.classList.add('jankx-animated');
                                }, index * staggerDelay);
                            });
                        }
                    }, parseInt(delay));

                    // Stop observing once animated if not infinite
                    if (!el.classList.contains('jankx-animate-infinite')) {
                        observer.unobserve(el);
                    }
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        animatedElements.forEach(el => {
            animationObserver.observe(el);
        });
    } else {
        // Fallback for older browsers
        animatedElements.forEach(el => {
            el.classList.add('jankx-animated');
        });
    }
});
