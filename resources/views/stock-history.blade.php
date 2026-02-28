@extends('main')
@section('inventory audit log', 'System 3')
@section('content')
@vite(['resources/css/stock-history.css'])
@vite(['resources/js/stock-history.js'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <div class="main-container">
        <div class="parent-container">
            <div class="header-container">
                <div class="filter-and-search-container">
                    <!-- FILTER ICON -->
                    <div id="filter-button" class="filter-icon">
                        <i class="bi bi-funnel"></i>
                    </div>
                    
                    <!-- FILTER DROPDOWN -->
                    <div class="filter-drop-down-modal" id="filterDropdown">
                        <form method="GET" action="{{ route('stock-history') }}" class="filter-drop-down-wrapper">
                            <div class="filter-group">
                                <select name="action">
                                    <option value="">All Actions</option>
                                    <option value="stock_in" {{ request('action') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="stock_out" {{ request('action') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="user_id">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="apply-filter-button">Apply Filter</button>
                        </form>
                    </div>
                    
                    <!-- SEARCH INPUT -->
                    <form method="GET" action="{{ route('stock-history') }}" class="search-form">
                        <input 
                            type="text"
                            name="search"
                            class="search-input"
                            placeholder="Search by ingredient name"
                            value="{{ request('search') }}"
                        >
                    </form>

                    <!-- DATE FILTER -->
                    <form method="GET" action="{{ route('stock-history') }}" class="date-form">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @if(request('action')) <input type="hidden" name="action" value="{{ request('action') }}"> @endif
                        @if(request('user_id')) <input type="hidden" name="user_id" value="{{ request('user_id') }}"> @endif
                        <input
                            type="date"
                            name="date"
                            id="dateInput"
                            class="date-input"
                            value="{{ request('date') }}"
                        />
                    </form>
                </div>

                <!-- BUTTON CONTAINER -->
                <div class="button-container">
                    <button class="export-sales-data-button" data-export-name="stock-history">
                        <i class="fa-solid fa-print"></i>
                        <span>Export Stock History</span>
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-container">
                <table>
                    <colgroup>
                        <col style="width: 25%">
                        <col style="width: 15%">
                        <col style="width: 20%">
                        <col style="width: 15%">
                        <col style="width: 12%">
                        <col style="width: 13%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="th">Item Name</th>
                            <th class="th">Action</th>
                            <th class="th">Date & Time</th>
                            <th class="th">User</th>
                            <th class="th">Unit Cost</th>
                            <th class="th">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr class="tr">
                                <td>
                                    <div class="product-name-and-image">
                                        <span>{{ $log->ingredient_name ?? 'Deleted Ingredient' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($log->action === 'stock_in')
                                        <span class="action-badge badge-stock-in">Stock In</span>
                                    @elseif($log->action === 'stock_out')
                                        <span class="action-badge badge-stock-out">Stock Out</span>
                                    @else
                                        <span class="action-badge">{{ ucfirst($log->action) }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('m/d/Y h:i A') }}</td>
                                <td>
                                    {{ $log->user 
                                        ? $log->user->first_name . ' ' . $log->user->last_name 
                                        : 'System' 
                                    }}
                                </td>
                                <td>₱{{ number_format($log->unit_cost, 2) }}</td>
                                <td>₱{{ number_format($log->total_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="pagination-container">
                @include('components.pagination', ['paginator' => $logs])
            </div>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
@endsection