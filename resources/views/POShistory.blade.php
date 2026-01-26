@extends('main')
@section('poshistory', 'System 1')
@section('content')
@vite(['resources/css/poshistory.css'])
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
                        <span>Today</span>
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
                        <span>Export Sales Data</span>
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
                            <th class="th">Order ID</th>
                            <th class="th">Date & Time</th>
                            <th class="th">Items Sold</th>
                            <th class="th">Payment Method</th>
                            <th class="th">Grand Total</th>
                            <th class="th">Staff ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tr">
                            <td>P-20251215-0010</td>
                            <td>1/27/2026 12:44 PM</td>
                            <td>3</td>
                            <td>Cash</td>
                            <td>$150</td>
                            <td>Staff 003</td>
                        </tr>
                        <tr class="tr">
                            <td>P-20251215-0009</td>
                            <td>1/27/2026 12:30 PM</td>
                            <td>5</td>
                            <td>GCash</td>
                            <td>$350</td>
                            <td>Staff 001</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection