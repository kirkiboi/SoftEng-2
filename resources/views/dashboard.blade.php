@extends('main')

@section('financial dashboard', 'Analysis & Reporting')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@vite(['resources/css/reports.css'])
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-container">
    <div class="parent-container">
        <div class="report-header">
            <div class="report-title">
                <h1>Financial Dashboard</h1>
                <p class="text-muted">High-level performance overview and key metrics.</p>
            </div>
            <div class="report-actions">
                <button class="export-btn" data-export-name="financial-dashboard" onclick="exportTableToCSV('financial-dashboard')">
                    <i class="fa-solid fa-print"></i>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="bi bi-currency-dollar text-primary"></i> Total Revenue
                </div>
                <div class="kpi-value">₱ {{ number_format($totalRevenue, 2) }}</div>
                <div class="kpi-trend trend-up">
                    <i class="bi bi-graph-up"></i> Lifetime Earnings
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="bi bi-calendar-check text-success"></i> Today's Revenue
                </div>
                <div class="kpi-value">₱ {{ number_format($todayRevenue, 2) }}</div>
                <div class="kpi-trend">
                    Daily actual sales
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="bi bi-cart-check text-success"></i> Realized Food Cost
                </div>
                <div class="kpi-value">₱ {{ number_format($totalCost, 2) }}</div>
                <div class="kpi-trend">
                    Served batches only
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="bi bi-pie-chart text-info"></i> Profit Margin
                </div>
                <div class="kpi-value">{{ number_format($profitMargin, 1) }}%</div>
                <div class="kpi-trend {{ $profitMargin > 30 ? 'trend-up' : 'trend-down' }}">
                    Goal: > 30%
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-label">
                    <i class="bi bi-trash text-danger"></i> Waste Cost
                </div>
                <div class="kpi-value" style="color: #dc3545;">₱ {{ number_format($wasteCost, 2) }}</div>
                <div class="kpi-trend trend-down">
                    Lost to wasted batches
                </div>
            </div>
        </div>

        <!-- Charts and Top Products -->
        <div class="report-grid">
            <div class="chart-container">
                <div class="card-title">
                    <i class="bi bi-activity"></i> Revenue Trend (Past 7 Days)
                </div>
                <div style="height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="data-table-card">
                <div class="card-title">
                    <i class="bi bi-trophy"></i> Top 5 Sellers
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Sold</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $index => $product)
                        <tr>
                            <td>
                                <div class="product-row">
                                    <span class="product-rank">{{ $index + 1 }}</span>
                                    <strong>{{ $product->product_name }}</strong>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($product->total_sold) }}</td>
                            <td class="text-end">₱ {{ number_format($product->revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const salesData = @json($salesTrend);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(d => d.date),
                datasets: [{
                    label: 'Revenue',
                    data: salesData.map(d => d.total),
                    borderColor: '#2975da',
                    backgroundColor: 'rgba(41, 117, 218, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2975da',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] },
                        ticks: {
                            callback: function(value) { return '₱' + value; }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endsection
