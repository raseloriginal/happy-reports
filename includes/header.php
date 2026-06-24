<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Bangladesh - Finance Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Phosphor Icons for modern icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f8ff',
                            100: '#e0f2fe',
                            400: '#38bdf8',
                            500: '#0078D4',
                            600: '#106EBE',
                            700: '#005a9e',
                        },
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden dark:bg-dark-bg dark:text-gray-200 transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-dark-card border-r border-gray-200 dark:border-dark-border flex flex-col transition-colors duration-300 z-20 shadow-sm relative">
        <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-dark-border bg-primary-600 dark:bg-dark-bg">
            <div class="w-8 h-8 rounded bg-white flex items-center justify-center text-primary-600 font-bold text-xl mr-3 shadow-md">H</div>
            <span class="font-bold text-lg tracking-tight text-white">Happy BD</span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="index.php" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-squares-four text-xl mr-3"></i> Dashboard
            </a>
            
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Transactions</p>
            </div>
            
             <a href="deposits.php" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'deposits.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-arrow-down-left text-primary-600 text-xl mr-3"></i> Deposits
            </a>
            
            <a href="withdrawals.php" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'withdrawals.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-arrow-up-right text-primary-600 text-xl mr-3"></i> Dealer Withdraw
            </a>

            <a href="lots.php" class="nav-item flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'lots.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <span class="flex items-center">
                    <i class="ph ph-truck text-primary-600 text-xl mr-3"></i> Lots
                </span>
                <span id="crm-badge-lots" class="hidden px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400">...</span>
            </a>

            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Analytics & AI</p>
            </div>

            <a href="reports.php" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-chart-line-up text-xl mr-3 text-primary-600"></i> Reports
            </a>

            <a href="pl.php" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'pl.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-calculator text-xl mr-3 text-primary-600"></i> P&L Engine
            </a>
            
            <a href="ai.php" class="nav-item relative flex items-center px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'ai.php' ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400' ?>">
                <i class="ph ph-sparkle text-xl mr-3 text-primary-500"></i> AI Assistant
                <span class="absolute right-2 flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
            </a>

            <!-- CRM Hub Section -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">CRM Hub</p>
            </div>
            <div class="mx-1.5 px-2 py-2.5 bg-gradient-to-br from-indigo-50/40 to-blue-50/20 dark:from-slate-800/30 dark:to-slate-900/20 border border-indigo-100/35 dark:border-slate-850 rounded-xl space-y-1.5 shadow-sm">
                <!-- Companies Link -->
                <a href="companies.php" class="nav-item flex items-center justify-between px-2.5 py-2 text-xs font-medium rounded-lg hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm text-gray-600 dark:text-gray-400 transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 font-semibold shadow-sm border border-indigo-100/50 dark:border-slate-700' : '' ?>">
                    <span class="flex items-center">
                        <i class="ph ph-buildings text-lg mr-2.5 text-indigo-500"></i>
                        Companies
                    </span>
                    <span id="crm-badge-companies" class="hidden px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">...</span>
                </a>
                
                <!-- Sales Link -->
                <a href="sales.php" class="nav-item flex items-center justify-between px-2.5 py-2 text-xs font-medium rounded-lg hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm text-gray-600 dark:text-gray-400 transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 font-semibold shadow-sm border border-emerald-100/50 dark:border-slate-700' : '' ?>">
                    <span class="flex items-center">
                        <i class="ph ph-handshake text-lg mr-2.5 text-emerald-500"></i>
                        Sales
                    </span>
                    <span id="crm-badge-sales" class="hidden px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">...</span>
                </a>
                
                <!-- Products Link -->
                <a href="products.php" class="nav-item flex items-center justify-between px-2.5 py-2 text-xs font-medium rounded-lg hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm text-gray-600 dark:text-gray-400 transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400 font-semibold shadow-sm border border-amber-100/50 dark:border-slate-700' : '' ?>">
                    <span class="flex items-center">
                        <i class="ph ph-package text-lg mr-2.5 text-amber-500"></i>
                        Products
                    </span>
                    <span id="crm-badge-products" class="hidden px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">...</span>
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-200 dark:border-dark-border">
            <button id="theme-toggle" class="flex items-center justify-center w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <i class="ph ph-moon mr-2 dark:hidden"></i>
                <i class="ph ph-sun mr-2 hidden dark:block text-amber-400"></i>
                <span class="dark:hidden">Dark Mode</span>
                <span class="hidden dark:block">Light Mode</span>
            </button>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f3f6fa] dark:bg-dark-bg">
        <!-- Top header -->
        <header class="h-16 bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border flex items-center justify-between px-8 z-10 shadow-sm">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white" id="page-title">Dashboard</h1>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <i class="ph ph-bell text-xl text-gray-500 dark:text-gray-400 hover:text-gray-700 cursor-pointer"></i>
                    <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500"></span>
                </div>
                <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium shadow-sm">
                    A
                </div>
            </div>
        </header>

        <!-- Main scrollable area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-transparent p-6 relative">
