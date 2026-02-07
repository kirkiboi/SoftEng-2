@extends('main')
@section('inventory audit log', 'System 3')
@section('content')
@vite(['resources/css/stock-history.css'])
@vite(['resources/js/ingredientAuditLog.js'])
<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <!-- FILTER ICON -->
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <!-- FILTER DROPDOWN -->
                <div class="filter-dropdown" id="filterDropdown" >
                    <form method="GET" action="{{ route('stock-history') }}" class="filter-dropdown-form">
                        {{-- Action filter --}}
                        <div class="filter-group">
                            <select name="action">
                                <option value="">All</option>
                                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Added</option>
                                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                        </div>
                        {{-- User filter --}}
                        <div class="filter-group">
                            <select name="user_id">
                                <option value="">All</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit">Apply filter</button>
                    </form>
                </div>
            </div>
            <!-- FILTER CONTAINER ENDS HERE + SEARCH CONTAINER STARTS HERE -->
            <div class="search-container">
                <form method="GET" action="{{ route('stock-history') }}">
                    <input 
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Search by ingredient name"
                        value="{{ request('search') }}"
                    >
                </form>
            </div>
            <!-- SERACH CONTAINRE ENDS HERE + DATE CONTAINER STARTS HERE -->
            <div class="date-container">
                <form method="GET" action="{{ route('stock-history') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input
                        type="date"
                        name="date"
                        id="dateInput"
                        value="{{ request('date') }}"
                    />
                </form>
            </div>
            <!-- DATE CONTAINER ENDS HERE + PAGINATION CONTROLS STARTS HERE -->
            <div class="pagination-container">
                {{ $logs->onEachSide(0)->links() }}
            </div>
            <!-- PAGINATION CONTROL ENDS HERE + EXPORT SALES DATA NGA BUTTON IS HERE -->
            <div class="export-sales-data-container">
                <button>
                    <i class="fa-solid fa-print"></i>
                    <span>Export Audit Log</span>
                </button>
            </div>
            <!-- EXPORT SALES DATA NGA BUTTON ENDS HERE -->
        </div>
        <!-- HEADER CONTAINER ENDS HERE + TABLE STARTS HERE -->
        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 20%">
                    <col style="width: 20%">
                    <col style="width: 20%">
                    <col style="width: 20%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Item Name</th>
                        <th class="th">Action</th>
                        <th class="th">Date & Time</th>
                        <th class="th">User</th>
                        <th class="th">Unit Cost</th>
                        <th class="th">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- LOOP THROUGH THE LOGS SA INGREDIENT NGA CONTROLLER -->
                    @foreach($logs as $log)
                        <tr>
                            <!-- LOGS SA CONTROLLER -->
                            <td>{{ $log->ingredient_name ?? 'Deleted Ingredient' }}</td>
                            <!-- LOGS SA ACTION -->
                            <td>{{ ucfirst($log->action) }}</td>
                            <!-- LOGS SA TIMESTAMPS -->
                            <td>{{ $log->created_at }}</td>
                            <!-- LOGS SA USER -->
                            <td>
                                {{ $log->user 
                                    ? $log->user->first_name . ' ' . $log->user->last_name 
                                    : 'System' 
                                }}
                            </td>
                            <!-- LOGS SA UNIT COST -->
                            <td>{{ $log->unit_cost }}</td>
                            <!-- LOGS SA TOTAL COST -->
                            <td>{{ $log->total_cost }}</td>
                        </tr>
                    @endforeach
                    <!-- END SA FOR EACH LOOP -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="overlay" id="overlay"></div>
@endsection