<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Dashboard</title>
    @vite(['resources/css/main.css'])
    @vite(['resources/js/main.js'])
</head>
<body>
    <div class="side-bar-container">
        <div class="logo-and-drop-down-container">
            <div class="logo-container">
                <img src="{{asset('photos/UMDiningcenter.png')}}" alt="UM Dining Center" class ="sidebar-container-logo">
            </div>
            <div class="drop-down-container">
                <i class="fa-solid fa-angles-left drop-down-container-button"></i>
            </div>
        </div>
        <div class="drop-down-main-container">
            <a href="{{ route('pos') }}">
                <div class="point-of-sales-container subsystem">
                    <div class="point-of-sale">
                        <span class ="subsystem-span">Point of Sale</span>
                    </div>
                    <div class="subsystem-drop-down">
                        <i class="fa-solid fa-angles-right button"></i>
                    </div>
                </div>
            </a>
            <div class="subsystem-feature">
                <div class="new-transaction feature">
                    <span>X</span>
                    <span class ="subsystem-span">New Transaction</span>
                </div>
                <div class="transaction-history feature">
                    <span>X</span>
                    <span class ="subsystem-span">Transaction History</span>                    
                </div>
            </div>
            <a href="{{ route('kp') }}">
                <div class="kitchen-production-container subsystem">
                    <div class="kitchen-production">
                        <span class ="subsystem-span">Kitchen Production</span>
                    </div>              
                </div>
            </a>

            <a href="{{ route('im') }}">
                <div class="inventory-management-container subsystem">
                    <div class="inventory-management">
                        <span class ="subsystem-span">Inventory Management</span>
                    </div>
                    <div class="subsystem-drop-down">
                        <i class="fa-solid fa-angles-right button"></i>
                    </div>
                </div>
            </a>
            <div class="subsystem-feature">
                <div class="new-transaction feature">
                    <span>X</span>
                    <span class ="subsystem-span">Ingredient Master List</span>
                </div>
                <div class="transaction-history feature">
                    <span>X</span>
                    <span class ="subsystem-span">Stock-In History</span>                    
                </div>
            </div>

            <a href="{{ route('mp') }}">
                <div class="menu-pricing-container subsystem">
                    <div class="menu-pricing">
                        <span class ="subsystem-span">Menu & Pricing </span>
                    </div>
                    <div class="subsystem-drop-down">
                        <i class="fa-solid fa-angles-right button"></i>
                    </div>
                </div>
            </a>
            
            <div class="subsystem-feature">
                <div class="new-transaction feature">
                    <span>X</span>
                    <span class ="subsystem-span">Item Master List</span>
                </div>
                <div class="transaction-history feature">
                    <span>X</span>
                    <span class ="subsystem-span">Pricing History</span>                    
                </div>
            </div>

            <a href="{{ route('ar') }}">
                <div class="analysis-and-reporting-container subsystem">
                    <div class="analysis-and-reporting">
                        <span class ="subsystem-span">Analysis & Reporting</span>
                    </div>
                    <div class="subsystem-drop-down">
                        <i class="fa-solid fa-angles-right button"></i>
                    </div>
                </div>
            </a>
            <div class="subsystem-feature">
                <div class="new-transaction feature">
                    <span>X</span>
                    <span class ="subsystem-span">Financial Dashboard</span>
                </div>
                <div class="transaction-history feature">
                    <span>X</span>
                    <span class ="subsystem-span">Cost & Variance Reports</span>                    
                </div>
                <div class="transaction-history feature">
                    <span>X</span>
                    <span class ="subsystem-span">Yield & Forecasting Reports</span>                    
                </div>
            </div>
        </div>

        <form action="">
            <div class="logout-button-wrapper">
                <i>X</i>
                <button class="logout-button subsystem-span">Logout</button>
            </div>
        </form>
    </div>

    <div class="main-content">
        @yield('content')
    </div>
    
</body>
</html>