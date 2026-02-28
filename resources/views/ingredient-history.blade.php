@extends('main')
@section('ingredient history', 'Ingredient History')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/ingredient-history.css'])
    @vite(['resources/js/ingredient-history.js'])
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
                        <form method="GET" action="{{ route('ingredient-history') }}" class="filter-drop-down-wrapper">
                            <div class="filter-group">
                                <select name="action">
                                    <option value="">All Actions</option>
                                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="edited" {{ request('action') == 'edited' ? 'selected' : '' }}>Edited</option>
                                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>
                            <button type="submit" class="apply-filter-button">Apply Filter</button>
                        </form>
                    </div>
                    
                    <!-- SEARCH INPUT -->
                    <form method="GET" action="{{ route('ingredient-history') }}" class="search-form">
                        @if(request('action')) <input type="hidden" name="action" value="{{ request('action') }}"> @endif
                        <input 
                            type="text"
                            name="search"
                            class="search-input"
                            placeholder="Search ingredient..."
                            value="{{ request('search') }}"
                        >
                    </form>

                    <!-- DATE FILTER -->
                    <form method="GET" action="{{ route('ingredient-history') }}" class="date-form">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @if(request('action')) <input type="hidden" name="action" value="{{ request('action') }}"> @endif
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
                    <button class="export-sales-data-button" data-export-name="ingredient-history">
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
                                @if($log->action === 'edited' && $log->supplier)
                                    <div style="font-size:0.7rem; color:#636e72; margin-top:0.3rem; line-height:1.3;">{{ $log->supplier }}</div>
                                @endif
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
@endsection
