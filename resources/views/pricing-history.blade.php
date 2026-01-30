@extends('main')
@section('poshistory', 'System 1')
@section('content')
@vite(['resources/css/pricing-history.css'])
@vite(['resources/js/productAuditLog.js'])
<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                    <form method="GET" action="{{ route('pricing-history') }}" class="filter-dropdown-form">
                        {{-- Action filter --}}
                        <div class="filter-group">
                            <select name="action">
                                <option value="">All</option>
                                <option value="added" {{ request('action') === 'added' ? 'selected' : '' }}>Added</option>
                                <option value="edited" {{ request('action') === 'edited' ? 'selected' : '' }}>Edited</option>
                                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                        </div>
                        {{-- User filter --}}
                        <div class="filter-group">
                            <select name="user_id">
                                <option value="">All</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit">Apply</button>
                    </form>
                </div>
            </div>  
            <div class="search-container">
                <form method="GET" action="{{ route('pricing-history') }}">
                    <input 
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Search"
                        value="{{ request('search') }}"
                    >
                </form>
            </div>
            <div class="date-container">
                <form method="GET" action="{{ route('pricing-history') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <button type="button" class="date-filter-button" id="dateBtn">
                        <span>
                            {{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('M d, Y') : 'Last 30 Days' }}
                        </span>
                        <i class="fa-solid fa-calendar"></i>
                    </button>

                    <input
                        type="date"
                        name="date"
                        id="dateInput"
                        value="{{ request('date') }}"
                    />
                </form>
            </div>
            <div class="pagination-container">
                {{ $logs->links() }}
            </div>
            <div class="export-sales-data-container">
                <button class="export-audit-log-button">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Audit Log</span>
                </button>
            </div>
        </div>
        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Item Name</th>
                        <th class="th">Action Type</th>
                        <th class="th">Date & Time</th>
                        <th class="th">User</th>
                        <th class="th">Previous Price</th>
                        <th class="th">New Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->product_name }}</td>
                            <td>{{ ucfirst($log->action) }}</td>
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
    </div>
</div>
@endsection