@extends('main')
@section('poshistory', 'System 1')
@section('content')
@vite(['resources/css/stock-history.css'])
@vite(['resources/js/poshistory.js'])

<div class="main-container">
    <div class="parent-container">
        <div class="header-container">
            <div class="filter-container">
                <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
            <div class="search-container">
                <input 
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search"
                >
            </div>
            <div class="date-container">
                <button class="date-filter-button">
                    <span>30 Days</span>
                    <i class="fa-solid fa-calendar"></i>
                </button>
                <input
                    type="date"
                    id="dateInput"
                    hidden
                />
            </div>
            <div class="pagination-container">
                <span>1 - 8 of 52</span>
                <span> < > </span>
            </div>
            <div class="export-sales-data-container">
                <button>
                    <i class="fa-solid fa-print"></i>
                    <span>Export Audit Log</span>
                </button>
            </div>
        </div>
        <div class="main-body-container">
            <table>
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                    <col style="width: 16%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Item Name</th>
                        <th class="th">Date & Time</th>
                        <th class="th">Supplier Name</th>
                        <th class="th">Quantity Received</th>
                        <th class="th">User ID</th>
                        <th class="th">Unit Cost</th>
                        <th class="th">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tr">
                        <td>Chicken Breast</td>
                        <td>1/27/2026 12:44 PM</td>
                        <td>Star Poultry Inc.</td>
                        <td>50.0kg</td>
                        <td>Cotton F. Zayas</td>
                        <td>180</td>
                        <td>9000</td>
                    </tr>
                    <tr class="tr">
                        <td>Red Onions</td>
                        <td>1/27/2026 1:00 PM</td>
                        <td>Davao Produce Co.</td>
                        <td>20.0kg</td>
                        <td>Blanco F. Zayas</td>
                        <td>155</td>
                        <td>3,100</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection