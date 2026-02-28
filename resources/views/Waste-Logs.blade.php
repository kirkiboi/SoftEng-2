@extends('main')
@section('waste logs', 'System 4')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/mp.css'])
    @vite(['resources/css/waste-logs.css'])
    @vite(['resources/js/mp.js'])
    @vite(['resources/js/waste-logs.js'])

    <div class="menu-pricing-parent-container">
        <!-- HEADER / CONTROLS LAYER -->
        <div class="header-container" style="flex-wrap: nowrap;">
            <div class="controls-container" style="justify-content: flex-start; width: 100%;">
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
                        <input
                            type="date"
                            name="date"
                            id="dateInput"
                            class="date-input"
                            value="{{ request('date') }}"
                        />
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
@endsection
