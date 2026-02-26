@extends('main')

@section('cost variance', 'Analysis & Reporting')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css'])

<div class="main-container">
    <div class="parent-container">
        <div class="report-header">
            <div class="report-title">
                <h1>Cost & Variance Report</h1>
                <p class="text-muted">Compare theoretical vs. actual ingredient usage to identify waste.</p>
            </div>
            <div class="report-actions">
                <button class="export-btn" data-export-name="cost-variance-report">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        <!-- Summary KPI -->
        <div class="kpi-grid kpi-grid-3">
            <div class="kpi-card kpi-card-accent-red">
                <div class="kpi-label">Total Over-Usage Cost</div>
                <div class="kpi-value text-danger">₱ {{ number_format(abs(collect($variances)->where('variance_cost', '<', 0)->sum('variance_cost')), 2) }}</div>
                <div class="kpi-trend">Monetary value of excess usage</div>
            </div>
            <div class="kpi-card kpi-card-accent-green">
                <div class="kpi-label">Total Under-Usage Savings</div>
                <div class="kpi-value text-success">₱ {{ number_format(collect($variances)->where('variance_cost', '>', 0)->sum('variance_cost'), 2) }}</div>
                <div class="kpi-trend">Ingredients used less than expected</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Net Variance</div>
                <div class="kpi-value {{ collect($variances)->sum('variance_cost') < 0 ? 'text-danger' : 'text-success' }}">₱ {{ number_format(collect($variances)->sum('variance_cost'), 2) }}</div>
                <div class="kpi-trend">Overall efficiency indicator</div>
            </div>
        </div>

        <div class="data-table-card data-table-card-full">
            <div class="card-title" style="justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <i class="bi bi-list-columns-reverse"></i> Detailed Ingredient Variance
                </div>
                <div style="display:flex; gap:0.75rem; align-items:center;">
                    <select id="varianceFilter" style="padding:0.4rem 0.8rem; border-radius:50px; border:1px solid #e0e0e0; font-size:0.85rem; cursor:pointer;">
                        <option value="all">All Items</option>
                        <option value="over">Over-Usage Only</option>
                        <option value="under">Under-Usage Only</option>
                    </select>
                    <input type="text" id="varianceSearch" placeholder="Search ingredient..." style="padding:0.4rem 0.8rem; border-radius:50px; border:1px solid #e0e0e0; font-size:0.85rem; width:180px;">
                </div>
            </div>
            <table class="report-table" id="varianceTable">
                <thead>
                    <tr>
                        <th>Ingredient Name</th>
                        <th>Actual Usage</th>
                        <th>Theoretical Usage</th>
                        <th>Variance</th>
                        <th>Var %</th>
                        <th class="text-end">Loss/Gain (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variances as $v)
                    <tr data-name="{{ strtolower($v->ingredient_name) }}" data-variance="{{ $v->variance_cost }}">
                        <td><strong>{{ $v->ingredient_name }}</strong></td>
                        <td>{{ number_format($v->actual_usage, 2) }} {{ $v->unit }}</td>
                        <td>{{ number_format($v->theoretical_usage, 2) }} {{ $v->unit }}</td>
                        <td class="{{ $v->variance < 0 ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($v->variance, 2) }} {{ $v->unit }}
                        </td>
                        <td>
                            <span class="variance-badge {{ $v->variance_percent < -5 ? 'vb-danger' : ($v->variance_percent < 0 ? 'vb-warning' : 'vb-success') }}">
                                {{ number_format($v->variance_percent, 1) }}%
                            </span>
                        </td>
                        <td class="text-end {{ $v->variance_cost < 0 ? 'variance-high' : 'variance-low' }}">
                            ₱ {{ number_format($v->variance_cost, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 2rem; color: #999;">No variance data available. Start producing batches to see variance analysis.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Client-side pagination -->
            <div id="variancePagination" style="display:flex; justify-content:center; gap:0.5rem; margin-top:1rem;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('varianceTable');
    const rows = Array.from(table.querySelectorAll('tbody tr[data-name]'));
    const search = document.getElementById('varianceSearch');
    const filter = document.getElementById('varianceFilter');
    const pagDiv = document.getElementById('variancePagination');
    const perPage = 10;
    let currentPage = 1;

    function getFiltered() {
        const q = search.value.toLowerCase();
        const f = filter.value;
        return rows.filter(r => {
            const name = r.dataset.name;
            const v = parseFloat(r.dataset.variance);
            const matchSearch = !q || name.includes(q);
            const matchFilter = f === 'all' || (f === 'over' && v < 0) || (f === 'under' && v >= 0);
            return matchSearch && matchFilter;
        });
    }

    function render() {
        const filtered = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        rows.forEach(r => r.style.display = 'none');
        filtered.slice(start, end).forEach(r => r.style.display = '');

        pagDiv.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.style.cssText = `padding:0.4rem 0.8rem; border-radius:50px; border:1px solid #e0e0e0; cursor:pointer; font-weight:600; font-size:0.85rem; ${i === currentPage ? 'background:#2975da; color:white; border-color:#2975da;' : 'background:white;'}`;
                btn.addEventListener('click', () => { currentPage = i; render(); });
                pagDiv.appendChild(btn);
            }
        }
    }

    search.addEventListener('input', () => { currentPage = 1; render(); });
    filter.addEventListener('change', () => { currentPage = 1; render(); });
    render();
});
</script>
@endsection
