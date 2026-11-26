<?php ?>
            </main>
        </div>
    </div>

    <script>
    (function () {
        function isMobileNav() {
            return window.matchMedia('(max-width: 767px)').matches;
        }

        function setSidebar(open) {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('mobileOverlay');
            var toggle = document.getElementById('sidebarToggle');
            if (!sidebar || !overlay) return;
            sidebar.classList.toggle('is-open', open);
            overlay.classList.toggle('is-open', open);
            overlay.classList.toggle('hidden', !open);
            document.body.classList.toggle('sidebar-open', open);
            if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        window.toggleSidebar = function () {
            if (!isMobileNav()) return;
            var sidebar = document.getElementById('sidebar');
            setSidebar(!(sidebar && sidebar.classList.contains('is-open')));
        };

        window.closeSidebar = function () {
            setSidebar(false);
        };

        var lastToggleAt = 0;
        document.addEventListener('click', function (e) {
            if (e.target.closest('#sidebarToggle')) {
                e.preventDefault();
                var now = Date.now();
                if (now - lastToggleAt < 400) return;
                lastToggleAt = now;
                window.toggleSidebar();
                return;
            }
            if (e.target.closest('#sidebarClose') || e.target.closest('#mobileOverlay')) {
                e.preventDefault();
                window.closeSidebar();
                return;
            }
            if (e.target.closest('#sidebar a') && isMobileNav()) {
                window.closeSidebar();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') window.closeSidebar();
        });

        window.addEventListener('resize', function () {
            if (!isMobileNav()) window.closeSidebar();
        });
    })();

    // Service Worker Registration untuk PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('../sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                })
                .catch(function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
        });
    }

    // PWA Install Prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // Bisa menampilkan tombol install custom di sini
        console.log('PWA install prompt available');
    });

    // Touch optimization - prevent double tap zoom on buttons
    document.addEventListener('touchstart', function(event) {
        if (event.target.matches('button, a, input, select, textarea')) {
            event.target.style.touchAction = 'manipulation';
        }
    }, { passive: true });
    </script>
</body>
</html>


