@extends('main')

@section('yield forecasting', 'Analysis & Reporting')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css'])
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-container">
    <div class="parent-container">
        <div class="report-header">
            <div class="report-title">
                <h1>Yield & Forecasting</h1>
                <p class="text-muted">Production efficiency and demand projections based on sales history.</p>
            </div>
            <div class="report-actions">
                <button class="export-btn" data-export-name="yield-forecasting-report">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-check-circle text-success"></i> Production Yield Rate</div>
                <div class="kpi-value text-success">{{ number_format($yieldRate, 1) }}%</div>
                <div class="kpi-trend">Finished vs. Total Batches</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-currency-dollar text-primary"></i> Avg. Daily Revenue (7d)</div>
                <div class="kpi-value">₱ {{ number_format($avgDailySales, 2) }}</div>
                <div class="kpi-trend">Trend-based average</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-graph-up-arrow text-info"></i> Projected Sales (Next 7d)</div>
                <div class="kpi-value text-primary">₱ {{ number_format($projectedWeeklyRevenue, 2) }}</div>
                <div class="kpi-trend">Simple moving average projection</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-exclamation-triangle text-danger"></i> Waste Rate</div>
                <div class="kpi-value text-danger">{{ number_format($wasteRate, 1) }}%</div>
                <div class="kpi-trend">Wasted vs Total Batches</div>
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

            <div class="data-table-card">
                <div class="card-title">
                    <i class="bi bi-bar-chart"></i> Top Produced Products
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Batches</th>
                            <th class="text-end">Servings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProduced as $item)
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td class="text-end">{{ $item->batch_count }}</td>
                            <td class="text-end">{{ $item->total_servings }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center; padding: 2rem; color: #999;">No production data yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yieldCtx = document.getElementById('yieldChart').getContext('2d');
        const yieldData = @json($productionStats);
        
        const statusColors = {
            'queued': '#fdcb6e',
            'cooking': '#e17055',
            'done': '#00b894',
            'wasted': '#d63031',
            'served': '#0984e3'
        };
        
        new Chart(yieldCtx, {
            type: 'doughnut',
            data: {
                labels: yieldData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
                datasets: [{
                    data: yieldData.map(d => d.count),
                    backgroundColor: yieldData.map(d => statusColors[d.status] || '#636e72'),
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
