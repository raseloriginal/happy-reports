<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#1e293b',
                        accent: '#22d3ee',
                        dark: '#0f172a',
                        light: '#f8fafc',
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .sidebar-link { transition: all 0.3s; }
        .sidebar-link:hover, .sidebar-link.active { background-color: rgba(255,255,255,0.1); border-left: 4px solid #4f46e5; }
    </style>
</head>
<body class="text-secondary flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-dark text-white flex flex-col shadow-xl z-20 h-full">
        <div class="h-16 flex items-center px-6 border-b border-gray-700 font-bold text-xl tracking-wider">
            <i class="fa-solid fa-chart-line text-primary mr-2"></i> Happy<span class="text-accent">Reports</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="<?php echo URLROOT; ?>/dashboard" class="sidebar-link active flex items-center px-6 py-3 text-sm font-medium">
                <i class="fa-solid fa-house w-6 text-gray-400"></i> Dashboard
            </a>
            <a href="<?php echo URLROOT; ?>/company" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium">
                <i class="fa-solid fa-building w-6 text-gray-400"></i> Company View
            </a>
            <a href="<?php echo URLROOT; ?>/inventory" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium">
                <i class="fa-solid fa-boxes-stacked w-6 text-gray-400"></i> Inventory Monitor
            </a>
            <a href="<?php echo URLROOT; ?>/dealer" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium">
                <i class="fa-solid fa-users w-6 text-gray-400"></i> Dealers
            </a>
            <a href="<?php echo URLROOT; ?>/import" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium">
                <i class="fa-solid fa-file-import w-6 text-gray-400"></i> Imports & Entries
            </a>
        </nav>
        <div class="p-4 border-t border-gray-700 text-xs text-gray-400">
            &copy; 2026 CEO Panel
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <!-- Topbar -->
        <header class="h-16 glass-panel flex items-center justify-between px-6 z-10">
            <div class="flex items-center">
                <button class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h2 class="ml-4 text-lg font-semibold text-gray-800" id="pageTitle">Overview</h2>
            </div>
            <div class="flex items-center space-x-4">
                <button class="text-gray-400 hover:text-primary transition">
                    <i class="fa-regular fa-bell text-xl"></i>
                </button>
                <div class="flex items-center space-x-2 cursor-pointer">
                    <img src="https://ui-avatars.com/api/?name=CEO&background=4f46e5&color=fff" alt="User" class="h-8 w-8 rounded-full border-2 border-primary">
                    <span class="text-sm font-medium hidden md:block">CEO Admin</span>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                </div>
            </div>
        </header>

        <!-- Dynamic View Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
            <?php require_once '../app/Views/' . $view . '.php'; ?>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Set active sidebar link based on current URL
        const currentUrl = window.location.href;
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.classList.remove('active', 'border-l-4', 'border-primary');
            if (currentUrl.includes(link.getAttribute('href'))) {
                link.classList.add('active', 'border-l-4', 'border-primary');
            }
        });
        
        // DataTables Global Config
        $.extend( true, $.fn.dataTable.defaults, {
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Search..."
            },
            "dom": '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
        });
    </script>
</body>
</html>
