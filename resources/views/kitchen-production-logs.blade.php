@extends('main')
@section('kitchen production logs', 'System 4')
@section('content')
@vite(['resources/css/kitchen-production-logs.css'])

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <div class="filter-dropdown" id="filterDropdown">
                    <form method="GET" action="{{ route('kitchen.logs') }}" class="filter-dropdown-form">
                        <div class="filter-group">
                            <select name="status">
                                <option value="">All Status</option>
                                <option value="wasted" {{ request('status') === 'wasted' ? 'selected' : '' }}>Wasted</option>
                                <option value="served" {{ request('status') === 'served' ? 'selected' : '' }}>Served</option>
                            </select>
                        </div>
                        <button type="submit">Apply filter</button>
                    </form>
                </div>
            </div>
            <div class="search-container">
                <form method="GET" action="{{ route('kitchen.logs') }}">
                    <input type="text" name="search" class="search-input" placeholder="Search by product name" value="{{ request('search') }}">
                </form>
            </div>
            <div class="date-container">
                <form method="GET" action="{{ route('kitchen.logs') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="date" name="date" id="dateInput" value="{{ request('date') }}" onchange="this.form.submit()"/>
                </form>
            </div>
            <div class="export-sales-data-container">
                <button class="export-btn" id="exportBtn" data-export-name="kitchen-production-logs">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Logs</span>
                </button>
            </div>
            <div class="pagination-container">
                @include('components.pagination', ['paginator' => $logs])
            </div>
        </div>

        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 15%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                    <col style="width: 20%">
                    <col style="width: 15%">
                    <col style="width: 14%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Product</th>
                        <th class="th">Times Cooked</th>
                        <th class="th">Servings</th>
                        <th class="th">Status</th>
                        <th class="th">User</th>
                        <th class="th">Ingredients Used</th>
                        <th class="th">Waste Reason</th>
                        <th class="th">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->product_name }}</td>
                            <td>{{ $log->times_cooked }}</td>
                            <td>{{ $log->total_servings }}</td>
                            <td>
                                <span class="status-badge status-{{ $log->status }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->user ? $log->user->first_name : 'System' }}</td>
                            <td>
                                @foreach($log->deductions as $d)
                                    <span class="deduction-tag">{{ $d->ingredient_name }}: -{{ $d->quantity_deducted }}{{ $d->unit }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($log->status === 'wasted')
                                    <span style="color: #dc3545; font-size: 0.9em;">{{ $log->waste_reason ?? 'N/A' }}</span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>{{ $log->created_at->format('m/d/Y h:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="overlay" id="overlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtn = document.querySelector('.filter-icon');
        const filterDropdown = document.getElementById('filterDropdown');
        filterBtn?.addEventListener('click', () => filterDropdown?.classList.toggle('show'));
    });
</script>
@endsection
