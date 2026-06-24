document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle logic
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    // Check local storage or system preference
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // Modal logic (reusable)
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.modal-content').classList.remove('scale-95', 'opacity-0');
                modal.querySelector('.modal-content').classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.querySelector('.modal-content').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.modal-content').classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    // Set page title based on the active nav item
    const activeNav = document.querySelector('.nav-item.bg-primary-50, .nav-item[class*="text-indigo-700"], .nav-item[class*="text-indigo-400"], .nav-item[class*="text-emerald-700"], .nav-item[class*="text-emerald-400"], .nav-item[class*="text-amber-700"], .nav-item[class*="text-amber-400"]');
    if (activeNav) {
        let titleText = activeNav.innerText || activeNav.textContent;
        // Strip badge counts or newlines if any
        titleText = titleText.split('\n')[0].trim();
        const pageTitleElement = document.getElementById('page-title');
        if (pageTitleElement) {
            pageTitleElement.textContent = titleText;
        }
    }

    // Dynamic CRM Badge Counts Loading
    const badgeCompanies = document.getElementById('crm-badge-companies');
    const badgeSales = document.getElementById('crm-badge-sales');
    const badgeProducts = document.getElementById('crm-badge-products');
    const badgeLots = document.getElementById('crm-badge-lots');

    if (badgeCompanies || badgeSales || badgeProducts || badgeLots) {
        const cachedCrmData = sessionStorage.getItem('crm_badge_data');
        if (cachedCrmData) {
            try {
                const data = JSON.parse(cachedCrmData);
                updateCrmBadges(data);
            } catch (e) {
                fetchCrmBadges();
            }
        } else {
            fetchCrmBadges();
        }
    }

    function fetchCrmBadges() {
        fetch('api/dashboard.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const counts = {
                        companies: (res.data.crm && res.data.crm.total_companies) || 0,
                        sales: (res.data.crm && res.data.crm.total_transactions) || 0,
                        products: (res.data.crm && res.data.crm.total_products) || 0,
                        lots: (res.data.local && res.data.local.lot_count) || 0
                    };
                    sessionStorage.setItem('crm_badge_data', JSON.stringify(counts));
                    updateCrmBadges(counts);
                }
            })
            .catch(err => console.error('Failed to load CRM counts:', err));
    }

    function updateCrmBadges(counts) {
        if (badgeCompanies) {
            badgeCompanies.textContent = counts.companies;
            badgeCompanies.classList.remove('hidden');
        }
        if (badgeSales) {
            badgeSales.textContent = counts.sales;
            badgeSales.classList.remove('hidden');
        }
        if (badgeProducts) {
            badgeProducts.textContent = counts.products;
            badgeProducts.classList.remove('hidden');
        }
        if (badgeLots) {
            badgeLots.textContent = counts.lots;
            badgeLots.classList.remove('hidden');
        }
    }
});
