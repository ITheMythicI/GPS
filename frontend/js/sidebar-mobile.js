(function () {
    function isMobileSidebar() {
        return window.matchMedia('(max-width: 768px)').matches;
    }

    function setSidebarOpen(open) {
        document.body.classList.toggle('sidebar-open', open);
        var toggle = document.getElementById('sidebar-toggle');
        var overlay = document.getElementById('sidebar-overlay');
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (overlay) overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            document.body.style.overflow = 'hidden';
        } else if (isMobileSidebar()) {
            document.body.style.overflow = '';
        }
    }

    function onToggle(event) {
        if (!isMobileSidebar()) return;
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var willOpen = !document.body.classList.contains('sidebar-open');
        setSidebarOpen(willOpen);
        document.dispatchEvent(new CustomEvent('sidebarToggled'));
    }

    function scrollContentToHash(hash) {
        if (!hash) return;
        var content = document.getElementById('content');
        var target = document.getElementById(hash.replace('#', ''));
        if (!content || !target) return;

        var contentRect = content.getBoundingClientRect();
        var targetRect = target.getBoundingClientRect();
        var top = content.scrollTop + (targetRect.top - contentRect.top) - 12;
        content.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    function initAnchorLinks() {
        document.querySelectorAll('.tool-icons a[href*="#"]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = link.getAttribute('href') || '';
                var parts = href.split('#');
                if (parts.length < 2 || !parts[1]) return;

                var pagePart = parts[0];
                var currentPage = window.location.pathname.split('/').pop() || '';
                var isSamePage = pagePart === '' || pagePart === currentPage || window.location.pathname.endsWith(pagePart);
                if (!isSamePage) return;

                event.preventDefault();
                scrollContentToHash(parts[1]);
            });
        });

        if (window.location.hash) {
            setTimeout(function () {
                scrollContentToHash(window.location.hash.substring(1));
            }, 120);
        }
    }

    function initSidebarMobile() {
        var toggle = document.getElementById('sidebar-toggle');
        var overlay = document.getElementById('sidebar-overlay');
        var sidebar = document.getElementById('sidebar');
        if (!toggle || !sidebar) return;

        toggle.addEventListener('click', onToggle);

        if (overlay) {
            overlay.addEventListener('click', function () {
                setSidebarOpen(false);
            });
        }

        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobileSidebar()) setSidebarOpen(false);
            });
        });

        window.addEventListener('resize', function () {
            if (!isMobileSidebar()) {
                setSidebarOpen(false);
                document.body.style.overflow = '';
            }
        });

        initAnchorLinks();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarMobile);
    } else {
        initSidebarMobile();
    }
})();
