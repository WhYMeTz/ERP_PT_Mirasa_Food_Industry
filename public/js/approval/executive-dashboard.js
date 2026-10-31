/**
 * Executive Dashboard JavaScript - macOS Architecture & Zero-Flicker SPA
 * PT Mirasa Food Industry - ERP Executive Suite
 */

(function() {
    'use strict';

    // ========================================================================
    // 1. MACOS SIDEBAR TOGGLE & STATE CONTROLLER
    // ========================================================================
    const execSidebar = document.getElementById('execSidebar');
    const toggleBtn = document.getElementById('execSidebarToggle');
    const macMinimizeBtn = document.getElementById('macMinimizeBtn');

    function toggleExecutiveSidebar() {
        const isCurrentlyCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
        const nextState = !isCurrentlyCollapsed;

        if (nextState) {
            document.documentElement.classList.add('sidebar-collapsed');
            localStorage.setItem('mirasa_exec_sidebar_collapsed', 'true');
        } else {
            document.documentElement.classList.remove('sidebar-collapsed');
            localStorage.setItem('mirasa_exec_sidebar_collapsed', 'false');
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleExecutiveSidebar();
        });
    }

    if (macMinimizeBtn) {
        macMinimizeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleExecutiveSidebar();
        });
    }

    // ========================================================================
    // 2. ZERO-FLICKER SEAMLESS SPA NAVIGATION (SIDEBAR REMAINS FROZEN & STATIC)
    // ========================================================================
    const progressBar = document.getElementById('macosProgressBar');
    let isNavigating = false;

    function startProgressBar() {
        if (!progressBar) return;
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
        progressBar.style.opacity = '1';
        progressBar.classList.add('active');

        requestAnimationFrame(() => {
            progressBar.style.transition = 'width 0.3s cubic-bezier(0.2, 0, 0, 1), opacity 0.2s ease';
            progressBar.style.width = '35%';
            setTimeout(() => {
                if (isNavigating) progressBar.style.width = '70%';
            }, 120);
        });
    }

    function completeProgressBar() {
        if (!progressBar) return;
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => {
                progressBar.style.width = '0%';
                progressBar.classList.remove('active');
            }, 250);
        }, 150);
    }

    // Intercept internal links for in-place content swapping
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || link.hasAttribute('download') || link.getAttribute('target') === '_blank') {
            return;
        }

        // Only handle internal executive links (same origin)
        if (link.href && link.href.startsWith(window.location.origin) && !link.closest('.no-spa')) {
            // Check if link is going outside executive portal (e.g. back to general ERP)
            const isLeavingPortal = link.title && link.title.includes('ERP Biasa');
            if (isLeavingPortal) {
                return; // Let standard browser navigation take user back to regular ERP
            }

            // Exclude review/detail page and links on review page from SPA interception
            // so page-specific styles, scripts, and print/modal handlers always load cleanly
            const linkUrl = new URL(link.href, window.location.origin);
            if (linkUrl.pathname.includes('/review') || window.location.pathname.includes('/review')) {
                return; // Full browser navigation
            }

            e.preventDefault();
            if (isNavigating) return;

            const targetUrl = link.href;
            if (targetUrl === window.location.href) return;

            updateSidebarActiveStates(targetUrl);
            navigateToUrl(targetUrl);
        }
    });

    // Browser History navigation (Back / Forward)
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.url) {
            updateSidebarActiveStates(e.state.url);
            fetchAndSwapContent(e.state.url, false);
        } else {
            updateSidebarActiveStates(window.location.href);
            fetchAndSwapContent(window.location.href, false);
        }
    });

    function navigateToUrl(url) {
        window.history.pushState({ url: url }, '', url);
        fetchAndSwapContent(url, true);
    }

    function updateSidebarActiveStates(url) {
        if (!execSidebar) return;

        const urlObj = new URL(url, window.location.origin);
        const targetPath = urlObj.pathname.replace(/\/$/, '');
        const isPoRoute = targetPath.includes('/gudang/po/approval-center') || (targetPath.includes('/gudang/po/') && targetPath.includes('/review'));

        // Update all macOS sidebar nav items
        execSidebar.querySelectorAll('.macos-nav-item, .sidebar-link').forEach(item => {
            const navKey = item.getAttribute('data-nav');
            const href = item.getAttribute('href');

            item.classList.remove('active');

            // 1. If currently on PO Approval Center or Reviewing a PO, ONLY highlight Purchase Order (PO)
            if (isPoRoute) {
                if (navKey === 'po') {
                    item.classList.add('active');
                }
                return;
            }

            if (!href || href.startsWith('javascript:')) return;

            const linkUrl = new URL(href, window.location.origin);
            const linkPath = linkUrl.pathname.replace(/\/$/, '');

            // 2. Exact match or query match for other modules
            if (linkPath === targetPath) {
                if (linkUrl.search) {
                    if (linkUrl.search === urlObj.search) {
                        item.classList.add('active');
                    }
                } else {
                    item.classList.add('active');
                }
            }
        });
    }

    function fetchAndSwapContent(url, isPushState) {
        isNavigating = true;
        startProgressBar();

        const contentArea = document.getElementById('execContentArea') || document.querySelector('.exec-main-wrapper');
        if (contentArea) {
            contentArea.style.transition = 'opacity 0.15s ease';
            contentArea.style.opacity = '0.5';
            contentArea.style.pointerEvents = 'none';
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Executive-SPA': '1'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
            return res.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Update Page Title
            if (doc.title) {
                document.title = doc.title;
            }

            // Extract the new main content
            const newContentArea = doc.getElementById('execContentArea') || doc.querySelector('.exec-main-wrapper');
            const targetArea = document.getElementById('execContentArea') || document.querySelector('.exec-main-wrapper');

            if (newContentArea && targetArea) {
                // Sync any new stylesheets from fetched document head
                doc.querySelectorAll('link[rel="stylesheet"]').forEach(function(l) {
                    const href = l.getAttribute('href');
                    if (href && !document.querySelector(`link[href="${href}"]`)) {
                        const newL = document.createElement('link');
                        newL.rel = 'stylesheet';
                        newL.href = href;
                        document.head.appendChild(newL);
                    }
                });

                targetArea.innerHTML = newContentArea.innerHTML;
                
                // Re-bind scripts & components on the freshly swapped view
                reinitializePageScripts();

                // Scroll smoothly to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Restore opacity
                targetArea.style.opacity = '1';
                targetArea.style.pointerEvents = 'auto';
            } else {
                // Safe fallback to native navigation
                window.location.href = url;
            }
        })
        .catch(err => {
            console.warn('SPA Navigation fallback to hard reload:', err);
            window.location.href = url;
        })
        .finally(() => {
            isNavigating = false;
            completeProgressBar();
        });
    }

    // ========================================================================
    // 3. REINITIALIZE PAGE SCRIPTS & EVENTS AFTER CONTENT SWAP
    // ========================================================================
    function reinitializePageScripts() {
        initSearchFunctionality();
        initFilterButtons();
        initTableInteractions();
    }

    // ========================================================================
    // 4. SEARCH & FILTER LOGIC
    // ========================================================================
    let searchTimeout;

    function initSearchFunctionality() {
        const searchInput = document.getElementById('execPoSearch');
        if (!searchInput) return;

        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            searchTimeout = setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                if (query.length >= 2) {
                    urlParams.set('search', query);
                } else {
                    urlParams.delete('search');
                }
                const newUrl = window.location.pathname + '?' + urlParams.toString();
                navigateToUrl(window.location.origin + newUrl);
            }, 400);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                searchInput.blur();
            }
        });
    }

    function initFilterButtons() {
        const filterButtons = document.querySelectorAll('[data-status-filter]');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.getAttribute('data-status-filter');
                const urlParams = new URLSearchParams(window.location.search);

                if (status === 'ALL') {
                    urlParams.delete('status');
                } else {
                    urlParams.set('status', status);
                }

                const newUrl = window.location.pathname + '?' + urlParams.toString();
                navigateToUrl(window.location.origin + newUrl);
            });
        });
    }

    function initTableInteractions() {
        const tableRows = document.querySelectorAll('.table-dark-header tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('.btn-action-tinjau') && !e.target.closest('a') && !e.target.closest('button')) {
                    const actionBtn = this.querySelector('.btn-action-tinjau');
                    if (actionBtn) {
                        const url = actionBtn.getAttribute('href');
                        if (url) navigateToUrl(url);
                    }
                }
            });
            row.style.cursor = 'pointer';
        });
    }

    // ========================================================================
    // 5. GLOBAL KEYBOARD SHORTCUTS
    // ========================================================================
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K: Quick Focus Search
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const searchInput = document.getElementById('execPoSearch');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }

        // Ctrl/Cmd + B: Toggle macOS Sidebar
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            toggleExecutiveSidebar();
        }
    });

    // Run initial binds on initial page load
    initSearchFunctionality();
    initFilterButtons();
    initTableInteractions();
    updateSidebarActiveStates(window.location.href);

})();
