@extends('main')

@section('yield forecasting', 'Analysis & Reporting')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css'])
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="reports-container">
    <div class="report-header">
        <div class="report-title">
            <h1>Yield & Forecasting</h1>
            <p class="text-muted">Production efficiency and demand projections based on sales history.</p>
        </div>
        <div class="report-actions">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Production Yield Rate</div>
            <div class="kpi-value text-success">{{ number_format($yieldRate, 1) }}%</div>
            <div class="kpi-trend">Finished vs. Total Batches</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Avg. Daily Revenue (7d)</div>
            <div class="kpi-value">₱ {{ number_format($avgDailySales, 2) }}</div>
            <div class="kpi-trend">Trend-based average</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Projected Sales (Next 7d)</div>
            <div class="kpi-value text-primary">₱ {{ number_format($projectedWeeklyRevenue, 2) }}</div>
            <div class="kpi-trend">Simple moving average projection</div>
        </div>
    </div>

    <div class="report-grid">
        <div class="chart-container">
            <div class="card-title">
                <i class="bi bi-pie-chart"></i> Production Batch Outcomes
            </div>
            <div style="height: 300px;">
                <canvas id="yieldChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Yield Pie Chart
        const yieldCtx = document.getElementById('yieldChart').getContext('2d');
        const yieldData = @json($productionStats);
        
        new Chart(yieldCtx, {
            type: 'doughnut',
            data: {
                labels: yieldData.map(d => d.status.toUpperCase()),
                datasets: [{
                    data: yieldData.map(d => d.count),
                    backgroundColor: ['#00b894', '#fdcb6e', '#d63031', '#0984e3'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endsection
