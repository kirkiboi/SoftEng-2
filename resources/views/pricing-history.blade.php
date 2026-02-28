@extends('main')
@section('product audit log', 'System 4')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/mp.css'])
    @vite(['resources/css/pricing-history.css'])
    @vite(['resources/js/mp.js'])
    @vite(['resources/js/productAuditLog.js'])

    <div class="menu-pricing-parent-container">
        <!-- HEADER / CONTROLS LAYER -->
        <div class="header-container">
            <div class="controls-container">
                <!-- FILTER BUTTON & DROPDOWN -->
                <div style="position: relative;">
                    <div id="filter-button" class="filter-icon-container">
                        <i class="bi bi-funnel default-icon"></i>
                        <i class="bi bi-x-lg active-icon" style="display: none !important;"></i>
                    </div>
                    
                    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                        <form method="GET" action="{{ route('pricing-history') }}" class="filter-dropdown-form">
                            {{-- Action filter --}}
                            <div class="filter-group">
                                <label>Action</label>
                                <select name="action">
                                    <option value="">All Actions</option>
                                    <option value="added" {{ request('action') === 'added' ? 'selected' : '' }}>Added</option>
                                    <option value="edited" {{ request('action') === 'edited' ? 'selected' : '' }}>Edited</option>
                                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>
                            {{-- User filter --}}
                            <div class="filter-group">
                                <label>User</label>
                                <select name="user_id">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="apply-filter-button">Apply Filter</button>
                        </form>
                    </div>
                </div>

                <!-- SEARCH -->
                <form method="GET" action="{{ route('pricing-history') }}">
                    <input type="text" name="search" class="search-input" placeholder="Search by product name"
                        value="{{ request('search') }}">
                </form>

                <!-- DATE FILTER -->
                <div class="date-container">
                    <form method="GET" action="{{ route('pricing-history') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input
                            type="date"
                            name="date"
                            id="dateInput"
                            class="date-input"
                            value="{{ request('date') }}"
                        />
                    </form>
                </div>

                <!-- PAGINATION (In Controls) -->


                <!-- EXPORT BUTTON -->
                <div class="export-sales-data-container">
                    <button class="export-audit-log-button" data-export-name="pricing-history">
                        <i class="fa-solid fa-print"></i>
                        <span>Export Product Audit Log</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN TABLE -->
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 25%">
                    <col style="width: 14%">
                    <col style="width: 20%">
                    <col style="width: 16%">
                    <col style="width: 12%">
                    <col style="width: 12%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Item Name</th>
                        <th class="th">Action Type</th>
                        <th class="th">Date & Time</th>
                        <th class="th">User</th>
                        <th class="th">Previous</th>
                        <th class="th">New Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="tr">
                            <td style="font-weight: 800; color: #2d3436;">{{ $log->product_name }}</td>
                            <td>
                                @php
                                    $badgeClass = match($log->action) {
                                        'added' => 'badge-added',
                                        'edited' => 'badge-edited',
                                        'deleted' => 'badge-deleted',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td>{{ $log->created_at->format('m/d/Y h:i A') }}</td>
                            <td>
                                {{ $log->user 
                                    ? $log->user->first_name . ' ' . $log->user->last_name 
                                    : 'System' 
                                }}
                            </td>
                            <td>{{ $log->old_price ? '₱ '.number_format($log->old_price,2) : '-' }}</td>
                            <td>{{ $log->new_price ? '₱ '.number_format($log->new_price,2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- PAGINATION (Moved outside table container) -->
        <div class="pagination-container">
            @include('components.pagination', ['paginator' => $logs])
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
@endsection