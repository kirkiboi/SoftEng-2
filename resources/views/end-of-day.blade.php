@extends('main')
@section('end of day', 'Analysis & Reporting')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css', 'resources/css/end-of-day.css'])

<div class="main-container">
    <div class="parent-container">
        {{-- Header --}}
        <div class="report-header">
            <div class="report-title">
                <h1>End of Day Report</h1>
                <p class="text-muted">Daily summary for <strong>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</strong></p>
            </div>
            <div class="report-actions">
                <form method="GET" action="{{ route('reports.end-of-day') }}" class="eod-date-form">
                    <input type="date" name="date" value="{{ $date }}" class="eod-date-input" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="eod-tabs">
            <button class="eod-tab active" data-tab="pos">
                <i class="fa-solid fa-cash-register"></i> Point of Sales
            </button>
            <button class="eod-tab" data-tab="kitchen">
                <i class="fa-solid fa-utensils"></i> Kitchen Production
            </button>
            <button class="eod-tab" data-tab="inventory">
                <i class="fa-solid fa-boxes-stacked"></i> Inventory Management
            </button>
            <button class="eod-tab" data-tab="summary">
                <i class="fa-solid fa-chart-pie"></i> End of Day Sales
            </button>
        </div>

        {{-- ============================== --}}
        {{-- TAB 1: POINT OF SALES --}}
        {{-- ============================== --}}
        <div class="eod-panel active" id="panel-pos">
            <div class="eod-section-header">
                <h2><i class="bi bi-receipt-cutoff"></i> Products Sold</h2>
                <div class="eod-summary-pills">
                    <span class="pill pill-blue">{{ $posTotalQty }} items sold</span>
                    <span class="pill pill-green">₱ {{ number_format($posTotalRevenue, 2) }} total</span>
                </div>
            </div>
            <div class="eod-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th class="text-end">Qty Sold</th>
                            <th class="text-end">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posSales as $index => $item)
                        <tr>
                            <td>
                                <span class="product-rank">{{ $index + 1 }}</span>
                            </td>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td class="text-end">{{ number_format($item->total_qty) }}</td>
                            <td class="text-end">₱ {{ number_format($item->total_sales, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="eod-empty">No sales recorded for this day.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($posSales->count() > 0)
                    <tfoot>
                        <tr class="eod-total-row">
                            <td colspan="2"><strong>TOTAL</strong></td>
                            <td class="text-end"><strong>{{ number_format($posTotalQty) }}</strong></td>
                            <td class="text-end"><strong>₱ {{ number_format($posTotalRevenue, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ============================== --}}
        {{-- TAB 2: KITCHEN PRODUCTION --}}
        {{-- ============================== --}}
        <div class="eod-panel" id="panel-kitchen">
            {{-- Served / Done Products --}}
            <div class="eod-section-header">
                <h2><i class="bi bi-check-circle text-success"></i> Served Products</h2>
                <span class="pill pill-green">{{ $servedLogs->count() }} batches</span>
            </div>
            <div class="eod-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Times Cooked</th>
                            <th>Servings</th>
                            <th>Status</th>
                            <th>Ingredients Deducted</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servedLogs as $log)
                        <tr>
                            <td><strong>{{ $log->product_name }}</strong></td>
                            <td>{{ $log->times_cooked }}</td>
                            <td>{{ $log->total_servings }}</td>
                            <td><span class="status-badge status-served">{{ ucfirst($log->status) }}</span></td>
                            <td>
                                <div class="deduction-tags-wrap">
                                    @foreach($log->deductions as $d)
                                        <span class="deduction-tag">{{ $d->ingredient_name }}: -{{ number_format($d->quantity_deducted, 2) }}{{ $d->unit }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $log->created_at->format('h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="eod-empty">No served products for this day.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Wasted Products --}}
            <div class="eod-section-header" style="margin-top: 2rem;">
                <h2><i class="bi bi-x-circle text-danger"></i> Wasted Products</h2>
                <span class="pill pill-red">{{ $wastedLogs->count() }} batches</span>
            </div>
            <div class="eod-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Times Cooked</th>
                            <th>Servings</th>
                            <th>Status</th>
                            <th>Ingredients Deducted</th>
                            <th>Waste Reason</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wastedLogs as $log)
                        <tr>
                            <td><strong>{{ $log->product_name }}</strong></td>
                            <td>{{ $log->times_cooked }}</td>
                            <td>{{ $log->total_servings }}</td>
                            <td><span class="status-badge status-wasted">Wasted</span></td>
                            <td>
                                <div class="deduction-tags-wrap">
                                    @foreach($log->deductions as $d)
                                        <span class="deduction-tag">{{ $d->ingredient_name }}: -{{ number_format($d->quantity_deducted, 2) }}{{ $d->unit }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td><span style="color: #dc3545; font-size: 0.85em;">{{ $log->waste_reason ?? 'N/A' }}</span></td>
                            <td>{{ $log->created_at->format('h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="eod-empty">No wasted products for this day.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================== --}}
        {{-- TAB 3: INVENTORY MANAGEMENT --}}
        {{-- ============================== --}}
        <div class="eod-panel" id="panel-inventory">
            {{-- Stock In --}}
            <div class="eod-section-header">
                <h2><i class="bi bi-box-arrow-in-down text-success"></i> Stock In</h2>
                <div class="eod-summary-pills">
                    <span class="pill pill-green">{{ $stockIns->count() }} entries</span>
                    <span class="pill pill-blue">₱ {{ number_format($totalStockInCost, 2) }} total cost</span>
                </div>
            </div>
            <div class="eod-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th class="text-end">Qty Changed</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Cost</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockIns as $log)
                        <tr>
                            <td><strong>{{ $log->ingredient_name ?? 'Deleted Ingredient' }}</strong></td>
                            <td class="text-end">+{{ number_format($log->quantity_changed, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($log->unit_cost, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($log->total_cost, 2) }}</td>
                            <td>{{ $log->created_at->format('h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="eod-empty">No stock in entries for this day.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($stockIns->count() > 0)
                    <tfoot>
                        <tr class="eod-total-row">
                            <td colspan="3"><strong>TOTAL STOCK IN COST</strong></td>
                            <td class="text-end"><strong>₱ {{ number_format($totalStockInCost, 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Stock Out --}}
            <div class="eod-section-header" style="margin-top: 2rem;">
                <h2><i class="bi bi-box-arrow-up text-danger"></i> Stock Out</h2>
                <div class="eod-summary-pills">
                    <span class="pill pill-red">{{ $stockOuts->count() }} entries</span>
                    <span class="pill pill-blue">₱ {{ number_format($totalStockOutCost, 2) }} total cost</span>
                </div>
            </div>
            <div class="eod-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th class="text-end">Qty Changed</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Cost</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockOuts as $log)
                        <tr>
                            <td><strong>{{ $log->ingredient_name ?? 'Deleted Ingredient' }}</strong></td>
                            <td class="text-end">-{{ number_format($log->quantity_changed, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($log->unit_cost, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($log->total_cost, 2) }}</td>
                            <td>{{ $log->created_at->format('h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="eod-empty">No stock out entries for this day.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($stockOuts->count() > 0)
                    <tfoot>
                        <tr class="eod-total-row">
                            <td colspan="3"><strong>TOTAL STOCK OUT COST</strong></td>
                            <td class="text-end"><strong>₱ {{ number_format($totalStockOutCost, 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ============================== --}}
        {{-- TAB 4: END OF DAY SALES --}}
        {{-- ============================== --}}
        <div class="eod-panel" id="panel-summary">
            <div class="eod-section-header">
                <h2><i class="bi bi-calculator"></i> End of Day Sales Summary</h2>
            </div>

            <div class="eod-summary-grid">
                {{-- Revenue Card --}}
                <div class="eod-summary-card eod-card-revenue">
                    <div class="eod-card-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="eod-card-label">Total POS Revenue</div>
                    <div class="eod-card-value">₱ {{ number_format($posTotalRevenue, 2) }}</div>
                    <div class="eod-card-sub">From {{ $posSales->count() }} product(s) sold</div>
                </div>

                {{-- Ingredient Cost Card --}}
                <div class="eod-summary-card eod-card-cost">
                    <div class="eod-card-icon"><i class="bi bi-basket2"></i></div>
                    <div class="eod-card-label">Ingredient Costs (Served)</div>
                    <div class="eod-card-value">₱ {{ number_format($dayIngredientCost, 2) }}</div>
                    <div class="eod-card-sub">Cost of ingredients deducted</div>
                </div>

                {{-- Waste Cost Card --}}
                <div class="eod-summary-card eod-card-waste">
                    <div class="eod-card-icon"><i class="bi bi-trash3"></i></div>
                    <div class="eod-card-label">Waste Cost</div>
                    <div class="eod-card-value">₱ {{ number_format($dayWasteCost, 2) }}</div>
                    <div class="eod-card-sub">Cost of wasted batches</div>
                </div>

                {{-- Total Costs Card --}}
                <div class="eod-summary-card eod-card-total-cost">
                    <div class="eod-card-icon"><i class="bi bi-receipt"></i></div>
                    <div class="eod-card-label">Total Costs for the Day</div>
                    <div class="eod-card-value">₱ {{ number_format($dayTotalCosts, 2) }}</div>
                    <div class="eod-card-sub">Ingredient costs + waste costs</div>
                </div>
            </div>

            {{-- Net Profit Highlight --}}
            <div class="eod-profit-card {{ $dayNetProfit >= 0 ? 'eod-profit-positive' : 'eod-profit-negative' }}">
                <div class="eod-profit-header">
                    <i class="bi {{ $dayNetProfit >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
                    <span>Net Profit / Loss</span>
                </div>
                <div class="eod-profit-value">₱ {{ number_format($dayNetProfit, 2) }}</div>
                <div class="eod-profit-formula">
                    POS Revenue (₱{{ number_format($posTotalRevenue, 2) }}) − Total Costs (₱{{ number_format($dayTotalCosts, 2) }})
                </div>
            </div>

            {{-- Breakdown Table --}}
            <div class="eod-table-wrap" style="margin-top: 1.5rem;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="bi bi-plus-circle text-success"></i> POS Sales Revenue</td>
                            <td class="text-end" style="color: #00b894; font-weight: 700;">₱ {{ number_format($posTotalRevenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-dash-circle text-danger"></i> Ingredient Costs (Served Batches)</td>
                            <td class="text-end" style="color: #d63031; font-weight: 700;">- ₱ {{ number_format($dayIngredientCost, 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-dash-circle text-danger"></i> Waste Costs</td>
                            <td class="text-end" style="color: #d63031; font-weight: 700;">- ₱ {{ number_format($dayWasteCost, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="eod-total-row">
                            <td><strong>NET PROFIT / LOSS</strong></td>
                            <td class="text-end" style="font-size: 1.1rem; font-weight: 800; color: {{ $dayNetProfit >= 0 ? '#00b894' : '#d63031' }};">
                                ₱ {{ number_format($dayNetProfit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.eod-tab');
    const panels = document.querySelectorAll('.eod-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
        });
    });
});
</script>
@endsection
