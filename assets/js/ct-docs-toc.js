/**
 * CT Docs Table of Contents
 *
 * Features:
 * - Scroll spy (active state)
 * - Smooth scroll
 * - Mobile bottom sheet
 *
 * @package CT_Docs
 */

(function() {
    'use strict';

    /**
     * Throttle function
     */
    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Initialize TOC functionality
     */
    function initTOC() {
        const toc = document.querySelector('.ct-docs-toc');
        const tocSidebar = document.querySelector('.ct-docs-toc-sidebar');
        const tocToggle = document.querySelector('.ct-docs-mobile-toc-toggle');
        const backdrop = document.querySelector('.ct-docs-backdrop');
        
        if (!toc) return;

        const tocList = toc.querySelector('.ct-docs-toc-list');
        const links = toc.querySelectorAll('a[href^="#"]');
        const headingIds = Array.from(links).map(link => link.getAttribute('href').slice(1));
        const headings = headingIds.map(id => document.getElementById(id)).filter(Boolean);
        
        // Create indicator element
        const indicator = document.createElement('div');
        indicator.className = 'ct-docs-toc-indicator';
        indicator.style.opacity = '0';
        tocList?.appendChild(indicator);
        
        // Header offset for scroll calculations
        const headerOffset = parseInt(
            getComputedStyle(document.documentElement)
                .getPropertyValue('--ct-docs-header-offset') || '100',
            10
        );

        /**
         * Update indicator position
         */
        function updateIndicator(activeLink) {
            if (!activeLink || !tocList) {
                indicator.style.opacity = '0';
                return;
            }
            
            const tocRect = tocList.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();
            
            indicator.style.top = (linkRect.top - tocRect.top + 6) + 'px';
            indicator.style.height = (linkRect.height - 12) + 'px';
            indicator.style.opacity = '1';
        }

        /**
         * Update active TOC item based on scroll position
         */
        const updateActiveItem = throttle(function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            let activeId = '';
            let activeLink = null;
            
            // Find the heading that's currently in view
            for (let i = headings.length - 1; i >= 0; i--) {
                const heading = headings[i];
                const rect = heading.getBoundingClientRect();
                
                if (rect.top <= headerOffset + 50) {
                    activeId = heading.id;
                    break;
                }
            }
            
            // Update active states
            links.forEach(link => {
                const href = link.getAttribute('href').slice(1);
                const listItem = link.closest('.ct-docs-toc-item');
                
                if (href === activeId) {
                    listItem?.classList.add('is-active');
                    link.setAttribute('aria-current', 'true');
                    activeLink = link;
                } else {
                    listItem?.classList.remove('is-active');
                    link.removeAttribute('aria-current');
                }
            });
            
            // Update indicator position
            updateIndicator(activeLink);
        }, 100);

        /**
         * Smooth scroll to heading
         */
        function scrollToHeading(e) {
            const href = e.currentTarget.getAttribute('href');
            if (!href.startsWith('#')) return;
            
            const targetId = href.slice(1);
            const target = document.getElementById(targetId);
            
            if (!target) return;
            
            e.preventDefault();
            
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerOffset - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
            
            // Update URL hash without jumping
            history.pushState(null, '', href);
            
            // Close mobile TOC if open
            if (tocSidebar?.classList.contains('is-open')) {
                closeMobileTOC();
            }
            
            // Set focus to heading for accessibility
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
        }

        /**
         * Open mobile TOC (bottom sheet)
         */
        function openMobileTOC() {
            tocSidebar?.classList.add('is-open');
            backdrop?.classList.add('is-visible');
            tocToggle?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            
            // Focus first TOC link
            const firstLink = toc.querySelector('a');
            firstLink?.focus();
        }

        /**
         * Close mobile TOC
         */
        function closeMobileTOC() {
            tocSidebar?.classList.remove('is-open');
            backdrop?.classList.remove('is-visible');
            tocToggle?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            
            // Return focus to toggle
            tocToggle?.focus();
        }

        // Event listeners
        links.forEach(link => {
            link.addEventListener('click', scrollToHeading);
        });

        window.addEventListener('scroll', updateActiveItem);
        
        // Mobile TOC toggle
        tocToggle?.addEventListener('click', () => {
            if (tocSidebar?.classList.contains('is-open')) {
                closeMobileTOC();
            } else {
                openMobileTOC();
            }
        });

        // Close on backdrop click
        backdrop?.addEventListener('click', () => {
            closeMobileTOC();
            // Also close sidebar if open
            const sidebar = document.querySelector('.ct-docs-sidebar');
            if (sidebar?.classList.contains('is-open')) {
                sidebar.classList.remove('is-open');
                backdrop.classList.remove('is-visible');
                document.body.style.overflow = '';
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (tocSidebar?.classList.contains('is-open')) {
                    closeMobileTOC();
                }
            }
        });

        // Handle hash on page load
        if (window.location.hash) {
            const targetId = window.location.hash.slice(1);
            const target = document.getElementById(targetId);
            
            if (target) {
                setTimeout(() => {
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerOffset - 20;
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }, 100);
            }
        }

        // Initial active state
        updateActiveItem();
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTOC);
    } else {
        initTOC();
    }

})();

