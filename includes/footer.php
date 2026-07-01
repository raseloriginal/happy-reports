        </main>
    </div>

    <script>
        // Live Date & Time in topbar
        function updateClock() {
            const dateEl = document.getElementById('topbar-date');
            if (dateEl) {
                const now = new Date();
                const dateStr = now.toLocaleDateString('en-GB', { weekday:'short', year:'numeric', month:'short', day:'numeric' });
                const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                dateEl.textContent = `${dateStr} • ${timeStr}`;
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Active nav
        const path = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (href && path.includes(href.split('/').pop()) && href !== '/') {
                link.classList.add('active');
            }
        });
        // Dashboard fallback
        if (path.endsWith('/index.php') || path.endsWith('/')) {
            document.getElementById('nav-dashboard')?.classList.add('active');
        }

        // Modal helpers
        function openModal(id) { document.getElementById(id)?.classList.add('open'); }
        function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) { e.target.classList.remove('open'); }
            if (e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
                e.target.closest('.modal-overlay')?.classList.remove('open');
            }
        });
    </script>
</body>
</html>
