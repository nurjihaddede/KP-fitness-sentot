<?php ?>
            </main>
        </div>
    </div>

    <script>
    // Mobile Sidebar Functions - Pastikan di global scope
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        if (sidebar && overlay) {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            
            if (isHidden) {
                // Buka sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('sidebar-open');
                document.body.style.overflow = 'hidden';
            } else {
                // Tutup sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            }
        }
    };

    window.closeSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        if (sidebar && overlay) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('sidebar-open');
            document.body.style.overflow = '';
        }
    };

    // Initialize setelah DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Event listener untuk tombol hamburger
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.toggleSidebar();
            });
        }

        // Event listener untuk overlay (close saat klik overlay)
        const overlay = document.getElementById('mobileOverlay');
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.closeSidebar();
            });
        }

        // Close sidebar ketika klik link di dalam sidebar (mobile)
        const sidebarLinks = document.querySelectorAll('#sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    window.closeSidebar();
                }
            });
        });

        // Close sidebar saat resize ke desktop
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 768) {
                    window.closeSidebar();
                }
            }, 250);
        });

        // Close sidebar saat ESC key ditekan
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeSidebar();
            }
        });
    });

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


