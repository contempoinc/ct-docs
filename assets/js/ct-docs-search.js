/**
 * CT Docs Live Search
 *
 * Features:
 * - Debounced AJAX search
 * - Keyboard navigation
 * - Accessibility (ARIA)
 * - Cached results
 *
 * @package CT_Docs
 */

(function() {
    'use strict';

    // Configuration from localized script
    const config = window.ctDocsSearch || {
        ajaxUrl: '/wp-admin/admin-ajax.php',
        nonce: '',
        noResults: 'No documentation found. Try different keywords.',
        error: 'Something went wrong. Please try again.'
    };

    // Simple in-memory cache
    const searchCache = new Map();

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Initialize search widget
     */
    function initSearchWidget(container) {
        const input = container.querySelector('.ct-docs-search-input');
        const results = container.querySelector('.ct-docs-search-results');
        
        if (!input || !results) return;

        const limit = parseInt(input.dataset.limit, 10) || 8;
        let selectedIndex = -1;
        let currentResults = [];

        /**
         * Perform search
         */
        const performSearch = debounce(async function(query) {
            query = query.trim();
            
            if (query.length < 2) {
                hideResults();
                return;
            }

            // Check cache first
            const cacheKey = query.toLowerCase();
            if (searchCache.has(cacheKey)) {
                displayResults(searchCache.get(cacheKey));
                return;
            }

            // Show loading state
            container.classList.add('is-loading');

            try {
                const formData = new FormData();
                formData.append('action', 'ct_docs_search');
                formData.append('nonce', config.nonce);
                formData.append('search', query);
                formData.append('limit', limit);

                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success && data.data.results) {
                    // Cache the results
                    searchCache.set(cacheKey, data.data.results);
                    displayResults(data.data.results);
                } else {
                    showError();
                }
            } catch (err) {
                console.error('CT Docs Search Error:', err);
                showError();
            } finally {
                container.classList.remove('is-loading');
            }
        }, 300);

        /**
         * Display search results
         */
        function displayResults(items) {
            currentResults = items;
            selectedIndex = -1;
            
            if (items.length === 0) {
                results.innerHTML = `<div class="ct-docs-search-no-results">${config.noResults}</div>`;
                results.classList.add('is-visible');
                return;
            }

            const html = items.map((item, index) => `
                <a href="${escapeHtml(item.url)}" 
                   class="ct-docs-search-result-item" 
                   role="option"
                   data-index="${index}"
                   id="ct-docs-result-${index}">
                    <span class="ct-docs-search-result-title">${escapeHtml(item.title)}</span>
                    <span class="ct-docs-search-result-excerpt">${item.excerpt}</span>
                    ${item.category ? `<span class="ct-docs-search-result-category">${escapeHtml(item.category)}</span>` : ''}
                </a>
            `).join('');

            results.innerHTML = html;
            results.classList.add('is-visible');
            
            // Announce to screen readers
            announceResults(items.length);
        }

        /**
         * Hide results dropdown
         */
        function hideResults() {
            results.classList.remove('is-visible');
            results.innerHTML = '';
            currentResults = [];
            selectedIndex = -1;
        }

        /**
         * Show error message
         */
        function showError() {
            results.innerHTML = `<div class="ct-docs-search-error">${config.error}</div>`;
            results.classList.add('is-visible');
        }

        /**
         * Announce results to screen readers
         */
        function announceResults(count) {
            const announcement = count === 1 
                ? '1 result found' 
                : `${count} results found`;
            
            // Create or update live region
            let liveRegion = container.querySelector('.ct-docs-sr-announce');
            if (!liveRegion) {
                liveRegion = document.createElement('div');
                liveRegion.className = 'ct-docs-sr-announce';
                liveRegion.setAttribute('role', 'status');
                liveRegion.setAttribute('aria-live', 'polite');
                liveRegion.setAttribute('aria-atomic', 'true');
                liveRegion.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);';
                container.appendChild(liveRegion);
            }
            liveRegion.textContent = announcement;
        }

        /**
         * Handle keyboard navigation
         */
        function handleKeydown(e) {
            if (!results.classList.contains('is-visible') || currentResults.length === 0) {
                return;
            }

            const items = results.querySelectorAll('.ct-docs-search-result-item');

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    updateSelection(items);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection(items);
                    break;

                case 'Enter':
                    if (selectedIndex >= 0 && items[selectedIndex]) {
                        e.preventDefault();
                        items[selectedIndex].click();
                    }
                    break;

                case 'Escape':
                    e.preventDefault();
                    hideResults();
                    input.blur();
                    break;
            }
        }

        /**
         * Update visual selection
         */
        function updateSelection(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('is-selected');
                    item.scrollIntoView({ block: 'nearest' });
                    input.setAttribute('aria-activedescendant', item.id);
                } else {
                    item.classList.remove('is-selected');
                }
            });

            if (selectedIndex === -1) {
                input.removeAttribute('aria-activedescendant');
            }
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Event listeners
        input.addEventListener('input', (e) => {
            performSearch(e.target.value);
        });

        input.addEventListener('keydown', handleKeydown);

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2 && currentResults.length > 0) {
                results.classList.add('is-visible');
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                hideResults();
            }
        });

        // Handle result click (for touch devices)
        results.addEventListener('click', (e) => {
            const item = e.target.closest('.ct-docs-search-result-item');
            if (item && item.href) {
                // Let the link navigate naturally
            }
        });

        // Mouse hover selection
        results.addEventListener('mouseover', (e) => {
            const item = e.target.closest('.ct-docs-search-result-item');
            if (item) {
                const index = parseInt(item.dataset.index, 10);
                selectedIndex = index;
                updateSelection(results.querySelectorAll('.ct-docs-search-result-item'));
            }
        });
    }

    /**
     * Initialize all search widgets on page
     */
    function init() {
        const widgets = document.querySelectorAll('.ct-docs-search-widget');
        widgets.forEach(initSearchWidget);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialize for dynamic content (Elementor, AJAX, etc.)
    window.ctDocsInitSearch = init;

})();

