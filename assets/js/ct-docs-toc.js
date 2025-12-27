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
        
        if (!toc) return;

        const tocList = toc.querySelector('.ct-docs-toc-list');
        const links = toc.querySelectorAll('a[href^="#"]');
        const headingIds = Array.from(links).map(link => link.getAttribute('href').slice(1));
        const headings = headingIds.map(id => document.getElementById(id)).filter(Boolean);
        
        // Progressive disclosure setup - detect top-level and mark collapsible items
        const tocItems = toc.querySelectorAll('.ct-docs-toc-item');
        
        // Find the minimum heading level (top level in this doc)
        let minLevel = 6;
        tocItems.forEach(item => {
            for (let i = 2; i <= 5; i++) {
                if (item.classList.contains('ct-docs-toc-level-' + i)) {
                    minLevel = Math.min(minLevel, i);
                    break;
                }
            }
        });
        
        // Mark items with children, and top-level items as collapsible
        tocItems.forEach(item => {
            const hasSublist = item.querySelector(':scope > .ct-docs-toc-sublist');
            if (hasSublist) {
                item.classList.add('has-children');
                // Top-level items with children are collapsible
                if (item.classList.contains('ct-docs-toc-level-' + minLevel)) {
                    item.classList.add('is-collapsible');
                }
            }
        });
        
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
         * Expand/collapse sections for progressive disclosure
         */
        function updateExpandedSections(activeItem) {
            // Collapse all sections first
            tocItems.forEach(item => {
                item.classList.remove('is-expanded');
            });
            
            if (!activeItem) return;
            
            // Expand the active item if it has children
            if (activeItem.classList.contains('has-children')) {
                activeItem.classList.add('is-expanded');
            }
            
            // Expand all ancestor items (for nested active items)
            let parent = activeItem.parentElement?.closest('.ct-docs-toc-item');
            while (parent) {
                parent.classList.add('is-expanded');
                parent = parent.parentElement?.closest('.ct-docs-toc-item');
            }
        }

        /**
         * Update active TOC item based on scroll position
         */
        const updateActiveItem = throttle(function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            let activeId = '';
            let activeLink = null;
            let activeItem = null;
            
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
                    activeItem = listItem;
                } else {
                    listItem?.classList.remove('is-active');
                    link.removeAttribute('aria-current');
                }
            });
            
            // Update progressive disclosure
            updateExpandedSections(activeItem);
            
            // Update indicator position (delay for expand/collapse transition to complete)
            setTimeout(() => updateIndicator(activeLink), 350);
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

        // Event listeners
        links.forEach(link => {
            link.addEventListener('click', scrollToHeading);
        });
        
        // Click to toggle expandable items
        tocItems.forEach(item => {
            if (item.classList.contains('has-children')) {
                const link = item.querySelector(':scope > a');
                link?.addEventListener('click', (e) => {
                    // Toggle expanded state on click (in addition to navigation)
                    item.classList.toggle('is-expanded');
                    // Update indicator after expand animation completes
                    setTimeout(() => {
                        const activeLink = toc.querySelector('.ct-docs-toc-item.is-active > a');
                        updateIndicator(activeLink);
                    }, 350);
                });
            }
        });

        window.addEventListener('scroll', updateActiveItem);

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
        
        // Remove loading state after setup is complete
        requestAnimationFrame(() => {
            toc.classList.remove('is-loading');
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTOC);
    } else {
        initTOC();
    }

})();

