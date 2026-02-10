@extends('main')
@section('product audit log', 'System 4')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/mp.css'])
    @vite(['resources/js/mp.js'])
    @vite(['resources/js/productAuditLog.js'])

    <style>
        /* Custom styles for the filter form within the dropdown */
        .filter-dropdown-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 0.5rem;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #636e72;
            margin-bottom: 0.3rem;
            display: block;
        }

        .filter-dropdown-form select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            background: #fdfdfd;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .apply-filter-button {
            background-color: #2975da;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
            transition: background 0.3s;
        }

        .apply-filter-button:hover {
            background-color: #1e5bb0;
        }

        /* Date picker styling to match controls */
        .date-container {
            position: relative;
        }

        .date-toggle-button {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 0.8rem;
            width: 2.8rem;
            height: 2.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .date-toggle-button:hover {
            border-color: #b2bec3;
            background: #f8f9fa;
        }

        .date-toggle-button i {
            font-size: 1.2rem;
            color: #636e72;
        }

        /* Export button styling matching standard pill buttons */
        .export-audit-log-button {
            border: none;
            background: #d63031; /* Red like 'Delete' or 'Add Item' accent */
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(214, 48, 49, 0.3);
            transition: 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .export-audit-log-button:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }

        /* Badge pills for Actions */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .badge-added { background-color: #e6fffa; color: #00b894; }
        .badge-edited { background-color: #fff8e1; color: #f1c40f; }
        .badge-deleted { background-color: #ffebee; color: #d63031; }

    </style>

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
                        <!-- Hidden actual date input -->
                        <input
                            type="date"
                            name="date"
                            id="dateInput"
                            value="{{ request('date') }}"
                            style="position: absolute; visibility: hidden; pointer-events: none;"
                        />
                        <!-- Visible button to trigger date picker -->
                        <div class="date-toggle-button" id="dateBtn" title="Filter by Date">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </form>
                </div>

                <!-- PAGINATION (In Controls) -->


                <!-- EXPORT BUTTON -->
                <div class="export-sales-data-container">
                    <button class="export-audit-log-button">
                        <i class="fa-solid fa-print"></i>
                        <span>Export Log</span>
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
            {{ $logs->onEachSide(0)->links() }}
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
@endsection