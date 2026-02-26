@extends('main')
@section('ingredient history', 'Ingredient History')
@section('content')
@vite(['resources/css/ingredient-history.css'])

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-and-search-container">
                <!-- FILTER ICON -->
                <div class="filter-icon" id="filterToggle">
                    <i class="bi bi-funnel"></i>
                </div>

                <!-- FILTER DROPDOWN -->
                <form method="GET" action="{{ route('ingredient-history') }}" class="filter-drop-down-modal" id="filterDropdown">
                    <div class="filter-drop-down-wrapper">
                        <select name="action">
                            <option value="">All Actions</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="edited" {{ request('action') == 'edited' ? 'selected' : '' }}>Edited</option>
                            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="stock_in" {{ request('action') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out" {{ request('action') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        </select>
                        <button class="apply-filter-button">Apply Filter</button>
                    </div>
                </form>

                <!-- SEARCH -->
                <form method="GET" action="{{ route('ingredient-history') }}" class="search-form">
                    @if(request('action'))
                        <input type="hidden" name="action" value="{{ request('action') }}">
                    @endif
                    <input type="text" name="search" class="search-input" placeholder="Search ingredient..." value="{{ request('search') }}">
                </form>

                <!-- DATE FILTER -->
                <form method="GET" action="{{ route('ingredient-history') }}" class="date-form">
                    @if(request('action'))
                        <input type="hidden" name="action" value="{{ request('action') }}">
                    @endif
                    <input type="date" name="date" class="date-input" value="{{ request('date') }}" onchange="this.form.submit()">
                </form>
            </div>

            <div class="button-container">
                <a href="{{ route('stock-history') }}" class="nav-link-button">Stock-In History</a>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 15%">
                    <col style="width: 12%">
                    <col style="width: 20%">
                    <col style="width: 13%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                    <col style="width: 20%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="th">Date & Time</th>
                        <th class="th">Action</th>
                        <th class="th">Ingredient</th>
                        <th class="th">Qty Changed</th>
                        <th class="th">Old Stock</th>
                        <th class="th">New Stock</th>
                        <th class="th">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="tr">
                        <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            @php
                                $actionMap = [
                                    'created' => ['label' => 'Created', 'class' => 'status-created'],
                                    'edited' => ['label' => 'Edited', 'class' => 'status-edited'],
                                    'deleted' => ['label' => 'Deleted', 'class' => 'status-deleted'],
                                    'stock_in' => ['label' => 'Stock In', 'class' => 'status-stock-in'],
                                    'stock_out' => ['label' => 'Stock Out', 'class' => 'status-stock-out'],
                                ];
                                $action = $actionMap[$log->action] ?? ['label' => ucfirst($log->action), 'class' => ''];
                            @endphp
                            <span class="action-badge {{ $action['class'] }}">{{ $action['label'] }}</span>
                        </td>
                        <td><strong>{{ $log->ingredient_name }}</strong></td>
                        <td>
                            @if($log->quantity_changed > 0)
                                @if(in_array($log->action, ['stock_out', 'deleted']))
                                    <span style="color:#dc3545; font-weight:600;">-{{ $log->quantity_changed }}</span>
                                @elseif(in_array($log->action, ['stock_in', 'created']))
                                    <span style="color:#28a745; font-weight:600;">+{{ $log->quantity_changed }}</span>
                                @else
                                    {{ $log->quantity_changed }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $log->old_stock }}</td>
                        <td>{{ $log->new_stock }}</td>
                        <td style="font-size:0.85rem; color:#666;">{{ $log->supplier ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:3rem; color:#999;">No ingredient history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($logs->hasPages())
        <div class="pagination-container">
            <p>Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }}</p>
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterToggle = document.getElementById('filterToggle');
    const filterDropdown = document.getElementById('filterDropdown');
    filterToggle?.addEventListener('click', () => {
        filterDropdown.classList.toggle('show');
    });
});
</script>
@endsection
