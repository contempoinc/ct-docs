/**
 * CT Docs Accordion Navigation
 *
 * Features:
 * - Expand/collapse sections
 * - LocalStorage persistence
 * - Mobile slide-in navigation
 * - Auto-scroll to current doc
 *
 * @package CT_Docs
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'ct_docs_accordion_state';

    /**
     * Initialize mobile header scroll behavior
     */
    function initMobileHeaderScroll() {
        const mobileHeader = document.querySelector('.ct-docs-mobile-header');
        if (!mobileHeader) return;

        let lastScrollY = window.scrollY;
        let ticking = false;

        function updateHeaderPosition() {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY <= 0) {
                // At top of page - normal position
                mobileHeader.classList.remove('is-scrolled-up');
                mobileHeader.classList.remove('is-scrolled-down');
            } else if (currentScrollY < lastScrollY) {
                // Scrolling up - move down to accommodate main header
                mobileHeader.classList.add('is-scrolled-up');
                mobileHeader.classList.remove('is-scrolled-down');
            } else if (currentScrollY > lastScrollY) {
                // Scrolling down - stay at top
                mobileHeader.classList.remove('is-scrolled-up');
                mobileHeader.classList.add('is-scrolled-down');
            }
            
            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateHeaderPosition);
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * Initialize accordion functionality
     */
    function initAccordion() {
        const accordion = document.querySelector('.ct-docs-accordion');
        const sidebar = document.querySelector('.ct-docs-sidebar');
        const tocSidebar = document.querySelector('.ct-docs-toc-sidebar');
        const menuBtn = document.querySelector('.ct-docs-mobile-menu-btn');
        const tocBtn = document.querySelector('.ct-docs-mobile-toc-btn');
        const backdrop = document.querySelector('.ct-docs-backdrop');
        
        if (!accordion) return;

        const items = accordion.querySelectorAll('.ct-docs-accordion-item');
        const headers = accordion.querySelectorAll('.ct-docs-accordion-header');
        
        // Load saved state from localStorage
        const savedState = loadState();

        /**
         * Toggle accordion item
         */
        function toggleItem(item, forceState = null) {
            const header = item.querySelector('.ct-docs-accordion-header');
            const content = item.querySelector('.ct-docs-accordion-content');
            const category = item.dataset.category;
            
            const isOpen = forceState !== null ? forceState : !item.classList.contains('is-open');
            
            if (isOpen) {
                item.classList.add('is-open');
                header.setAttribute('aria-expanded', 'true');
                content.removeAttribute('hidden');
                
                // Animate height
                content.style.height = '0px';
                content.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    content.style.transition = 'height 0.3s ease';
                    content.style.height = content.scrollHeight + 'px';
                });
                
                setTimeout(() => {
                    content.style.height = '';
                    content.style.overflow = '';
                    content.style.transition = '';
                }, 300);
            } else {
                item.classList.remove('is-open');
                header.setAttribute('aria-expanded', 'false');
                
                // Animate height
                content.style.height = content.scrollHeight + 'px';
                content.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    content.style.transition = 'height 0.3s ease';
                    content.style.height = '0px';
                });
                
                setTimeout(() => {
                    content.setAttribute('hidden', '');
                    content.style.height = '';
                    content.style.overflow = '';
                    content.style.transition = '';
                }, 300);
            }
            
            // Save state
            saveState(category, isOpen);
        }

        /**
         * Load state from localStorage
         */
        function loadState() {
            try {
                const state = localStorage.getItem(STORAGE_KEY);
                return state ? JSON.parse(state) : {};
            } catch (e) {
                return {};
            }
        }

        /**
         * Save state to localStorage
         */
        function saveState(category, isOpen) {
            try {
                const state = loadState();
                state[category] = isOpen;
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                // localStorage not available
            }
        }

        /**
         * Open mobile navigation drawer
         */
        function openMobileNav() {
            sidebar?.classList.add('is-open');
            backdrop?.classList.add('is-visible');
            menuBtn?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        /**
         * Close mobile navigation drawer
         */
        function closeMobileNav() {
            sidebar?.classList.remove('is-open');
            menuBtn?.setAttribute('aria-expanded', 'false');
            
            // Only hide backdrop if TOC is also closed
            if (!tocSidebar?.classList.contains('is-open')) {
                backdrop?.classList.remove('is-visible');
                document.body.style.overflow = '';
            }
            
            menuBtn?.focus();
        }

        /**
         * Open TOC drawer
         */
        function openTocDrawer() {
            tocSidebar?.classList.add('is-open');
            backdrop?.classList.add('is-visible');
            tocBtn?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        /**
         * Close TOC drawer
         */
        function closeTocDrawer() {
            tocSidebar?.classList.remove('is-open');
            tocBtn?.setAttribute('aria-expanded', 'false');
            
            // Only hide backdrop if nav is also closed
            if (!sidebar?.classList.contains('is-open')) {
                backdrop?.classList.remove('is-visible');
                document.body.style.overflow = '';
            }
            
            tocBtn?.focus();
        }

        /**
         * Close all drawers
         */
        function closeAllDrawers() {
            sidebar?.classList.remove('is-open');
            tocSidebar?.classList.remove('is-open');
            backdrop?.classList.remove('is-visible');
            menuBtn?.setAttribute('aria-expanded', 'false');
            tocBtn?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        /**
         * Scroll to current doc in accordion
         */
        function scrollToCurrentDoc() {
            const currentItem = accordion.querySelector('.ct-docs-nav-item.is-current');
            if (!currentItem) return;
            
            // Ensure parent accordion is open
            const accordionItem = currentItem.closest('.ct-docs-accordion-item');
            if (accordionItem && !accordionItem.classList.contains('is-open')) {
                toggleItem(accordionItem, true);
            }
            
            // Scroll into view after a short delay
            setTimeout(() => {
                currentItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }, 350);
        }

        // Apply saved state or defaults
        items.forEach(item => {
            const category = item.dataset.category;
            
            // Check if this is the current category
            const hasCurrent = item.querySelector('.ct-docs-nav-item.is-current');
            
            if (savedState.hasOwnProperty(category)) {
                // Use saved state, but always open if contains current doc
                toggleItem(item, savedState[category] || hasCurrent);
            } else if (!item.classList.contains('is-open') && hasCurrent) {
                // Open if contains current doc
                toggleItem(item, true);
            }
        });

        // Event listeners for accordion headers
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const item = header.closest('.ct-docs-accordion-item');
                toggleItem(item);
            });
            
            // Keyboard support
            header.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const item = header.closest('.ct-docs-accordion-item');
                    toggleItem(item);
                }
            });
        });

        // Mobile menu button toggle
        menuBtn?.addEventListener('click', () => {
            if (sidebar?.classList.contains('is-open')) {
                closeMobileNav();
            } else {
                // Close TOC first if open
                if (tocSidebar?.classList.contains('is-open')) {
                    closeTocDrawer();
                }
                openMobileNav();
            }
        });

        // Mobile TOC button toggle
        tocBtn?.addEventListener('click', () => {
            if (tocSidebar?.classList.contains('is-open')) {
                closeTocDrawer();
            } else {
                // Close nav first if open
                if (sidebar?.classList.contains('is-open')) {
                    closeMobileNav();
                }
                openTocDrawer();
            }
        });

        // Close on backdrop click
        backdrop?.addEventListener('click', () => {
            closeAllDrawers();
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllDrawers();
            }
        });

        // Focus trap for mobile nav
        sidebar?.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab' || !sidebar.classList.contains('is-open')) return;
            
            const focusable = sidebar.querySelectorAll('input, button, a, [tabindex]:not([tabindex="-1"])');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });

        // Focus trap for TOC drawer
        tocSidebar?.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab' || !tocSidebar.classList.contains('is-open')) return;
            
            const focusable = tocSidebar.querySelectorAll('input, button, a, [tabindex]:not([tabindex="-1"])');
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });

        // Scroll to current doc on load
        scrollToCurrentDoc();
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAccordion();
            initMobileHeaderScroll();
        });
    } else {
        initAccordion();
        initMobileHeaderScroll();
    }

})();

