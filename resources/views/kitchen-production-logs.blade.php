@extends('main')
@section('kitchen production logs', 'System 4')
@section('content')
@vite(['resources/css/kitchen-production-logs.css'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <!-- FILTER ICON (Bootstrap Icons funnel, matching stock-history) -->
                <div id="filter-button" class="filter-icon-container">
                    <i class="bi bi-funnel"></i>
                </div>
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
        </div>

        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 14%">
                    <col style="width: 7%">
                    <col style="width: 7%">
                    <col style="width: 9%">
                    <col style="width: 9%">
                    <col style="width: 25%">
                    <col style="width: 14%">
                    <col style="width: 15%">
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
                            <td><strong>{{ $log->product_name }}</strong></td>
                            <td>{{ $log->times_cooked }}</td>
                            <td>{{ $log->total_servings }}</td>
                            <td>
                                <span class="status-badge status-{{ $log->status }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->user ? $log->user->first_name : 'System' }}</td>
                            <td>
                                <div class="deduction-tags-wrap">
                                    @foreach($log->deductions as $d)
                                        <span class="deduction-tag">{{ $d->ingredient_name }}: -{{ number_format($d->quantity_deducted, 2) }}{{ $d->unit }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($log->status === 'wasted')
                                    <span style="color: #dc3545; font-size: 0.85em;">{{ $log->waste_reason ?? 'N/A' }}</span>
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

        <!-- PAGINATION (moved to bottom) -->
        <div class="pagination-container">
            @include('components.pagination', ['paginator' => $logs])
        </div>
    </div>
</div>
<div class="overlay" id="overlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtn = document.getElementById('filter-button');
        const filterDropdown = document.getElementById('filterDropdown');
        filterBtn?.addEventListener('click', () => {
            filterDropdown.style.display = filterDropdown.style.display === 'block' ? 'none' : 'block';
        });
        const dateInput = document.getElementById('dateInput');
        dateInput?.addEventListener('change', () => {
            dateInput.form.submit();
        });
    });
</script>
@endsection
