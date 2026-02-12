@extends('main')
@section('waste logs', 'System 4')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/mp.css'])
    @vite(['resources/js/mp.js'])

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
        .badge-wasted { background-color: #ffebee; color: #d63031; }

    </style>

    <div class="menu-pricing-parent-container">
        <!-- HEADER / CONTROLS LAYER -->
        <div class="header-container">
            <h2 style="font-weight: 800; color: #2d3436; margin: 0;">Waste Logs</h2>
            <div class="controls-container" style="justify-content: flex-end;">
                <!-- FILTER BUTTON & DROPDOWN -->
                <div style="position: relative;">
                    <div id="filter-button" class="filter-icon-container">
                        <i class="bi bi-funnel default-icon"></i>
                        <i class="bi bi-x-lg active-icon" style="display: none !important;"></i>
                    </div>
                    
                    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                        <form method="GET" action="{{ route('waste.logs') }}" class="filter-dropdown-form">
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
                <form method="GET" action="{{ route('waste.logs') }}">
                    <input type="text" name="search" class="search-input" placeholder="Search product..."
                        value="{{ request('search') }}">
                </form>

                <!-- DATE FILTER -->
                <div class="date-container">
                    <form method="GET" action="{{ route('waste.logs') }}">
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
            </div>
        </div>

        <!-- MAIN TABLE -->
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 25%">
                    <col style="width: 15%">
                    <col style="width: 40%">
                    <col style="width: 20%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Item Name</th>
                        <th class="th">User</th>
                        <th class="th">Waste Details</th>
                        <th class="th">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="tr">
                            <td style="font-weight: 800; color: #2d3436;">{{ $log->product_name }}</td>
                            <td>
                                {{ $log->user 
                                    ? $log->user->first_name . ' ' . $log->user->last_name 
                                    : 'System' 
                                }}
                            </td>
                            <td style="color: #d63031; font-weight: 500;">
                                {{ $log->action }}
                            </td>
                            <td>{{ $log->created_at->format('m/d/Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No waste logs found.</td>
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
    <script>
        // Custom Date Picker Logic specifically for this page
        document.addEventListener('DOMContentLoaded', () => {
             const dateBtn = document.getElementById('dateBtn');
             const dateInput = document.getElementById('dateInput');
             if(dateBtn && dateInput) {
                 dateBtn.addEventListener('click', () => {
                     dateInput.showPicker(); // Modern browser API
                 });
                 dateInput.addEventListener('change', () => {
                     dateInput.form.submit();
                 });
             }
        });
    </script>
@endsection
