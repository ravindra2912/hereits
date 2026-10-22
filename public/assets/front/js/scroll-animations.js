/**
 * Frontend Scroll Reveal Animations
 * Uses IntersectionObserver to smoothly animate sections and cards as they enter the viewport
 */
(function () {
    'use strict';

    function initScrollReveal() {
        var targets = document.querySelectorAll('.reveal-on-scroll');
        if (!targets.length) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px'
            });

            targets.forEach(function (target) {
                observer.observe(target);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            targets.forEach(function (target) {
                target.classList.add('is-visible');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollReveal);
    } else {
        initScrollReveal();
    }
})();
