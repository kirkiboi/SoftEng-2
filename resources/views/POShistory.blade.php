@extends('main')
@section('poshistory', 'System 1')
@section('content')
@vite(['resources/css/pos-history.css'])

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="search-container">
                <form method="GET" action="{{ route('POShistory') }}">
                    <input type="text" name="search" class="search-input" placeholder="Search by Order ID" value="{{ request('search') }}">
                </form>
            </div>
            <div class="date-container">
                <form method="GET" action="{{ route('POShistory') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="date" name="date" id="dateInput" value="{{ request('date') }}" onchange="this.form.submit()"/>
                </form>
            </div>
            <div class="pagination-container">
                {{ $transactions->onEachSide(0)->links() }}
            </div>
            <div class="export-sales-data-container">
                <button>
                    <i class="fa-solid fa-print"></i>
                    <span>Export Sales Data</span>
                </button>
            </div>
        </div>

        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 18%">
                    <col style="width: 12%">
                    <col style="width: 15%">
                    <col style="width: 15%">
                    <col style="width: 20%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Order ID</th>
                        <th class="th">Date & Time</th>
                        <th class="th">Items Sold</th>
                        <th class="th">Payment Method</th>
                        <th class="th">Grand Total</th>
                        <th class="th">Cashier</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr class="tr clickable-row" data-order-id="{{ $txn->order_id }}">
                            <td>{{ $txn->order_id }}</td>
                            <td>{{ $txn->created_at->format('m/d/Y h:i A') }}</td>
                            <td>{{ $txn->items->sum('quantity') }}</td>
                            <td>
                                <span class="payment-badge payment-{{ $txn->payment_method }}">
                                    {{ ucfirst($txn->payment_method) }}
                                </span>
                            </td>
                            <td class="amount-cell">₱{{ number_format($txn->total_amount, 2) }}</td>
                            <td>{{ $txn->user ? $txn->user->first_name . ' ' . $txn->user->last_name : 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:#999;">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection