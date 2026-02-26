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
        <div class="kpi-grid">
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
            <div class="card-title">
                <i class="bi bi-list-columns-reverse"></i> Detailed Ingredient Variance
            </div>
            <table class="report-table">
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
                    <tr>
                        <td><strong>{{ $v->ingredient_name }}</strong></td>
                        <td>{{ number_format($v->actual_usage, 2) }} {{ $v->unit }}</td>
                        <td>{{ number_format($v->theoretical_usage, 2) }} {{ $v->unit }}</td>
                        <td class="{{ $v->variance < 0 ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($v->variance, 2) }} {{ $v->unit }}
                        </td>
                        <td>
                            <span class="badge {{ $v->variance_percent < -5 ? 'bg-danger' : ($v->variance_percent < 0 ? 'bg-warning' : 'bg-success') }}">
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
        </div>
    </div>
</div>
@endsection
