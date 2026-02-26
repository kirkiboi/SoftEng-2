@extends('main')
@section('ingredient history', 'Ingredient History')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/stock-history.css'])

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <!-- FILTER ICON -->
                <div id="filter-button" class="filter-icon-container">
                    <i class="bi bi-funnel"></i>
                </div>
                <!-- FILTER DROPDOWN -->
                <div class="filter-dropdown" id="filterDropdown">
                    <form method="GET" action="{{ route('ingredient-history') }}" class="filter-dropdown-form">
                        <div class="filter-group">
                            <select name="action">
                                <option value="">All Actions</option>
                                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                                <option value="edited" {{ request('action') == 'edited' ? 'selected' : '' }}>Edited</option>
                                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                        </div>
                        <button type="submit">Apply Filter</button>
                    </form>
                </div>
            </div>
            <!-- SEARCH -->
            <div class="search-container">
                <form method="GET" action="{{ route('ingredient-history') }}">
                    @if(request('action'))
                        <input type="hidden" name="action" value="{{ request('action') }}">
                    @endif
                    <input type="text" name="search" class="search-input" placeholder="Search ingredient..." value="{{ request('search') }}">
                </form>
            </div>
            <!-- DATE -->
            <div class="date-container">
                <form method="GET" action="{{ route('ingredient-history') }}">
                    @if(request('action'))
                        <input type="hidden" name="action" value="{{ request('action') }}">
                    @endif
                    <input type="date" name="date" id="dateInput" value="{{ request('date') }}">
                </form>
            </div>
            <!-- EXPORT -->
            <div class="export-sales-data-container">
                <button data-export-name="ingredient-history">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Log</span>
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 15%">
                    <col style="width: 25%">
                    <col style="width: 15%">
                    <col style="width: 10%">
                    <col style="width: 15%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="th">Ingredient</th>
                        <th class="th">Action</th>
                        <th class="th">Date & Time</th>
                        <th class="th">User</th>
                        <th class="th">Old Stock</th>
                        <th class="th">New Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="tr">
                        <td>
                            <div class="product-name-and-image">
                                <span>{{ $log->ingredient_name }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeMap = [
                                    'created' => 'badge-created',
                                    'edited' => 'badge-edited',
                                    'deleted' => 'badge-deleted',
                                ];
                                $badgeClass = $badgeMap[$log->action] ?? '';
                            @endphp
                            <span class="action-badge {{ $badgeClass }}">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td>{{ $log->created_at->format('m/d/Y h:i A') }}</td>
                        <td>
                            {{ $log->user
                                ? $log->user->first_name . ' ' . $log->user->last_name
                                : 'System'
                            }}
                        </td>
                        <td>{{ $log->old_stock }}</td>
                        <td>{{ $log->new_stock }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:2rem; color:#999;">No ingredient history found.</td>
                    </tr>
                    @endforelse
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

<style>
.action-badge {
    padding: 0.35rem 0.7rem;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: capitalize;
    display: inline-block;
}
.badge-created { background: #d4edda; color: #155724; }
.badge-edited { background: #e3f2fd; color: #1565c0; }
.badge-deleted { background: #ffebee; color: #c62828; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterBtn = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');
    filterBtn?.addEventListener('click', () => {
        filterDropdown.style.display = filterDropdown.style.display === 'none' ? 'block' : 'none';
    });
    const dateInput = document.getElementById('dateInput');
    dateInput?.addEventListener('change', () => {
        dateInput.form.submit();
    });
});
</script>
@endsection
