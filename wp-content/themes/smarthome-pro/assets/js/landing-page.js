/**
 * SmartHome Pro — Landing Page JavaScript
 * ========================================
 * Vanilla ES6+ · No dependencies (except Swiper loaded externally)
 *
 * Features:
 *   1. Sticky header with .scrolled class
 *   2. Active nav link via IntersectionObserver
 *   3. Swiper testimonial slider initialisation
 *   4. Scroll-triggered fade-in animations (.shp-animate)
 *   5. Mobile menu auto-close on link click
 *   6. Back-to-top button
 *   7. Smooth-scroll for anchor links
 *   8. Counter animation for stats bar
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ======================================================================
       1. STICKY HEADER
       ====================================================================== */

    const header = document.querySelector('.shp-header');

    if (header) {
        const SCROLL_THRESHOLD = 50;
        let lastKnownScroll = 0;
        let ticking = false;

        const updateHeader = function () {
            if (lastKnownScroll > SCROLL_THRESHOLD) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            ticking = false;
        };

        window.addEventListener('scroll', function () {
            lastKnownScroll = window.scrollY;
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });

        // Run once on load in case page is already scrolled
        lastKnownScroll = window.scrollY;
        updateHeader();
    }


    /* ======================================================================
       2. ACTIVE NAVIGATION LINK HIGHLIGHT
       ====================================================================== */

    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.shp-nav-link');

    if (sections.length && navLinks.length) {
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -60% 0px',   // trigger roughly when section is 20-40% in view
            threshold: 0
        };

        const activateLink = function (id) {
            navLinks.forEach(function (link) {
                link.classList.remove('active');
                // Match href="#home" or full URL ending in #home
                if (link.getAttribute('href') === '#' + id ||
                    (link.getAttribute('href') && link.getAttribute('href').endsWith('#' + id))) {
                    link.classList.add('active');
                }
            });
        };

        const sectionObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    activateLink(entry.target.id);
                }
            });
        }, observerOptions);

        sections.forEach(function (section) {
            sectionObserver.observe(section);
        });
    }


    /* ======================================================================
       3. SWIPER TESTIMONIALS SLIDER
       ====================================================================== */

    if (typeof Swiper !== 'undefined') {
        const swiperContainer = document.querySelector('.shp-testimonials-slider');

        if (swiperContainer) {
            const testimonialSlider = new Swiper('.shp-testimonials-slider', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                speed: 800,
                spaceBetween: 30,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                breakpoints: {
                    0: {
                        slidesPerView: 1,
                        spaceBetween: 15
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    1200: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                }
            });
        }
    }


    /* ======================================================================
       4. SCROLL ANIMATIONS (Fade-in on Scroll)
       ====================================================================== */

    const animateElements = document.querySelectorAll('.shp-animate');

    if (animateElements.length) {
        const animObserverOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.1
        };

        const animObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target); // animate only once
                }
            });
        }, animObserverOptions);

        animateElements.forEach(function (el, index) {
            // Auto-assign stagger delays to sibling cards in the same row
            if (!el.hasAttribute('data-delay')) {
                const parent = el.parentElement;
                if (parent) {
                    const siblings = parent.querySelectorAll(':scope > .shp-animate');
                    siblings.forEach(function (sib, i) {
                        if (!sib.hasAttribute('data-delay')) {
                            sib.setAttribute('data-delay', i + 1);
                            sib.style.transitionDelay = ((i + 1) * 0.1) + 's';
                        }
                    });
                }
            }
            animObserver.observe(el);
        });
    }


    /* ======================================================================
       5. MOBILE MENU — CLOSE ON LINK CLICK
       ====================================================================== */

    const offcanvasEl = document.getElementById('shpOffcanvasNav');

    if (offcanvasEl && typeof bootstrap !== 'undefined') {
        const mobileLinks = offcanvasEl.querySelectorAll('.shp-nav-link');

        mobileLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (!targetId || targetId === '#' || !targetId.startsWith('#')) return;

                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();

                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
                    if (bsOffcanvas) {
                        // Close the offcanvas
                        bsOffcanvas.hide();

                        // Scroll after offcanvas is hidden to avoid body scroll-lock conflict
                        offcanvasEl.addEventListener('hidden.bs.offcanvas', function onHidden() {
                            const headerHeight = header ? header.offsetHeight : 0;
                            const targetPosition = targetEl.getBoundingClientRect().top + window.scrollY - headerHeight;

                            window.scrollTo({
                                top: targetPosition,
                                behavior: 'smooth'
                            });

                            // Remove listener so it only fires once per click
                            offcanvasEl.removeEventListener('hidden.bs.offcanvas', onHidden);
                        });
                    }
                }
            });
        });
    }


    /* ======================================================================
       6. BACK TO TOP BUTTON
       ====================================================================== */

    const backToTopBtn = document.getElementById('backToTop');

    if (backToTopBtn) {
        const BACK_TOP_THRESHOLD = 500;

        window.addEventListener('scroll', function () {
            if (window.scrollY > BACK_TOP_THRESHOLD) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }


    /* ======================================================================
       7. SMOOTH SCROLL FOR ANCHOR LINKS
       ====================================================================== */

    const anchorLinks = document.querySelectorAll('a[href^="#"]:not(#shpOffcanvasNav a)');

    anchorLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');

            // Ignore empty hashes and non-element targets
            if (targetId === '#' || targetId === '') return;

            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();

                // Account for fixed header height
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = targetEl.getBoundingClientRect().top + window.scrollY - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });


    /* ======================================================================
       8. COUNTER ANIMATION (Stats Bar Numbers)
       ====================================================================== */

    const statNumbers = document.querySelectorAll('.shp-stat-number');

    if (statNumbers.length) {
        let countersAnimated = false;

        /**
         * Animate a number from 0 to the target value parsed from the element's
         * text content.  Supports optional suffix characters (e.g. "50K+", "99%").
         */
        const animateCounter = function (el) {
            const raw = el.textContent.trim();

            // Extract numeric part and suffix (e.g. "10K+" → 10, "K+")
            const match = raw.match(/^([\d,.]+)(.*)/);
            if (!match) return;

            const target = parseFloat(match[1].replace(/,/g, ''));
            const suffix = match[2] || '';
            const duration = 1800;       // ms
            const startTime = performance.now();

            const step = function (currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Ease-out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(eased * target);

                // Preserve commas for large numbers
                el.textContent = current.toLocaleString() + suffix;

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    // Ensure final value is exact
                    el.textContent = target.toLocaleString() + suffix;
                }
            };

            requestAnimationFrame(step);
        };

        const statsObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting && !countersAnimated) {
                    countersAnimated = true;
                    statNumbers.forEach(animateCounter);
                    observer.disconnect(); // fire only once
                }
            });
        }, { threshold: 0.3 });

        // Observe the first stat number's parent (the stats bar)
        const statsBar = statNumbers[0].closest('.shp-stats-bar');
        if (statsBar) {
            statsObserver.observe(statsBar);
        } else {
            statNumbers.forEach(function (el) {
                statsObserver.observe(el);
            });
        }
    }

}); // end DOMContentLoaded
