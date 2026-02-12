<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Dashboard</title>
    @vite(['resources/css/main.css'])
    @vite(['resources/js/main.js'])
</head>
<body>
<div class="side-bar-container">
    <!-- LOGO DROP DOWN  -->
    <div class="logo-and-drop-down-container">
        <div class="logo-container">
            <img src="{{asset('photos/UMDiningcenter.png')}}" alt="UM Dining Center" class ="sidebar-container-logo">
            <img src="{{asset('favicon.png')}}" alt="UM Dining Center" class ="sidebar-logo-collapsed">
        </div>
        <div class="drop-down-container">
            <i class="fa-solid fa-angles-left drop-down-container-button"></i>
        </div>
    </div>
    <div class="drop-down-main-container">
        <!-- Point of Sales shit ni -->
        <div class="point-of-sales-container subsystem">
            <div class="point-of-sale">
                <i class="fa-solid fa-cash-register me-3"></i>
                <span class ="subsystem-span">Point of Sale</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button-pos"></i>
            </div>
        </div>
        <div class="subsystem-feature">    
            <a href="{{ route('pos') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-plus-circle me-3 sub-icon"></i>
                    <span class="subsystem-span">New Transaction</span>
                </div>
            </a>
            <a href="{{ route('POShistory') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-receipt me-3 sub-icon"></i>
                    <span class="subsystem-span">Transaction History</span>                    
                </div>
            </a>
        </div>
        <!-- Kitchen Production -->
        <div class="kitchen-production-container subsystem">
            <div class="kitchen-production">
                <i class="fa-solid fa-utensils me-2"></i>
                <span class="subsystem-span">Kitchen Production</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('kp') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-fire me-3 sub-icon"></i>
                    <span class="subsystem-span">Kitchen Board</span>
                </div>
            </a>
            <a href="{{ route('kitchen.logs') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-clipboard-list me-3 sub-icon"></i>
                    <span class="subsystem-span">Production Logs</span>
                </div>
            </a>
        </div>
        <!-- Inventory Manager shit ni -->
        <div class="inventory-management-container subsystem">
            <div class="inventory-management">
                <!-- icon -->
                <i class="fa-solid fa-boxes-stacked me-3"></i>
                <span class ="subsystem-span">Inventory Management</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('im') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-list-check me-3 sub-icon"></i>
                    <span class ="subsystem-span">Ingredient Master Lists</span>
                </div>
            </a>
            <a href="{{ route('stock-history') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-truck-ramp-box me-3 sub-icon"></i>
                    <span class ="subsystem-span">Stock-In History</span>                    
                </div>
            </a>
        </div>
        <!-- Menu and Pricing shit ni diria -->
        <div class="menu-pricing-container subsystem">
            <div class="menu-pricing">
                <i class="fa-solid fa-book-open me-3"></i>
                <span class ="subsystem-span">Menu & Pricing </span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('mp') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-clipboard-list me-3 sub-icon"></i>
                    <span class ="subsystem-span">Item Master List</span>
                </div>
            </a>
            <a href="{{ route('pricing-history') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-tag me-3 sub-icon"></i>
                    <span class ="subsystem-span">Pricing History</span>                    
                </div>
            </a>
        </div>

        <!-- CVAM ass shit ni diria -->
        <div class="analysis-and-reporting-container subsystem">
            <div class="analysis-and-reporting">
                <i class="fa-solid fa-chart-line me-3"></i>
                <span class ="subsystem-span">Analysis & Reporting</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <div class="new-transaction feature d-flex align-items-center">
                <i class="fa-solid fa-gauge-high me-3 sub-icon"></i>
                <span class ="subsystem-span">Financial Dashboard</span>
            </div>
            <div class="transaction-history feature d-flex align-items-center">
                <i class="fa-solid fa-file-invoice-dollar me-3 sub-icon"></i>
                <span class ="subsystem-span">Cost & Variance Reports</span>                    
            </div>
            <div class="transaction-history feature d-flex align-items-center">
                <i class="fa-solid fa-magnifying-glass-chart me-3 sub-icon"></i>
                <span class ="subsystem-span">Yield & Forecasting Reports</span>                    
            </div>
        </div>
    </div>
    <form action="{{ route('login') }}">
        <div class="logout-button-wrapper">
            <button class="logout-button">
                <i class="fa-solid fa-right-from-bracket me-3" style="color: red;"></i>
                <span class="subsystem-span">Logout</span>
            </button>
        </div>
    </form>
</div>
<div class="main-content">
    @yield('content')
</div>
</body>
</html>