@extends('main')

@section('cost variance', 'Analysis & Reporting')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css'])

<div class="reports-container">
    <div class="report-header">
        <div class="report-title">
            <h1>Cost & Variance Report</h1>
            <p class="text-muted">Compare theoretical vs. actual ingredient usage to identify waste.</p>
        </div>
        <div class="report-actions">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    </div>

    <!-- Summary KPI -->
    <div class="kpi-grid">
        <div class="kpi-card" style="border-left: 5px solid #d63031;">
            <div class="kpi-label">Total Loss (Variance)</div>
            <div class="kpi-value text-danger">₱ {{ number_format(collect($variances)->sum('variance_cost'), 2) }}</div>
            <div class="kpi-trend">Monetary value of missing inventory</div>
        </div>
    </div>

    <div class="data-table-card">
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
                @foreach($variances as $v)
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
