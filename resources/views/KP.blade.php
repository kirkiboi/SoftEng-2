    @extends('main')
    @section('kp', 'System 2')
    @section('content')
    @vite(['resources/css/kp.css'])
        <div class="kitchen-production-main-container">
            <div class="kitchen-production-parent-container">
                <div class="header-container">
                    <svg id="filter-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="filter-icon">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                        <button class="filter-option" data-category="all">All</button>
                        <button class="filter-option" data-category="drinks">Drinks</button>
                        <button class="filter-option" data-category="snacks">Snacks</button>
                        <button class="filter-option" data-category="meals">Meals</button>
                    </div>
                    <input 
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Search"
                    >
                    <div class="add-item-container">
                        <button class="add-item-button">+ Add Batch</button>
                    </div>
                </div>
                <div class="main-body-container">
                    <div class="queued-container">
                        <div class="header-wrapper">
                            <span class="queue-icon"></span>
                            <h1>Queue</h1>
                        </div>
                        <div class="wrapper">
                            <div class="product-name">
                                <span>Corned Beef w/ Rice</span>
                            </div>
                            <div class="batch-amount">
                                <span>2 Batches</span>
                            </div>
                            <div class="time">
                                <span>19 minutes</span>
                            </div>
                            <div class="navigation-buttons">
                                <button class="start-button">start</button>
                                <button class="cancel-button">discard</button>
                            </div>
                        </div>
                        <div class="wrapper">
                            <div class="product-name">
                                <span>Pork Adobo</span>
                            </div>
                            <div class="batch-amount">
                                <span>3 Batches</span>
                            </div>
                            <div class="time">
                                <span>90 minutes</span>
                            </div>
                            <div class="navigation-buttons">
                                <button class="start-button">start</button>
                                <button class="cancel-button">discard</button>
                            </div>
                        </div>
                    </div>

                    <div class="cooking-container">
                        <div class="header-wrapper">
                            <span class="cooking-icon"></span>
                            <h1>Cooking</h1>
                        </div>
                        <div class="wrapper">
                            <div class="product-name">
                                <span>Fried Chicken</span>
                            </div>
                            <div class="batch-amount">
                                <span>4 Batches</span>
                            </div>
                            <div class="time">
                                <span>45 minutes</span>
                            </div>
                            <div class="navigation-buttons">
                                <button class="complete-button">complete</button>
                                <button class="cancel-button">discard</button>
                            </div>
                        </div>
                        <div class="wrapper">
                            <div class="product-name">
                                <span>Longganisa w/ Egg</span>
                            </div>
                            <div class="batch-amount">
                                <span>3 Batches</span>
                            </div>
                            <div class="time">
                                <span>52 minutes</span>
                            </div>
                            <div class="navigation-buttons">
                                <button class="complete-button">complete</button>
                                <button class="cancel-button">discard</button>
                            </div>
                        </div>
                    </div>

                    <div class="done-container">
                        <div class="header-wrapper">
                            <span class="done-icon"></span>
                            <h1>Done</h1>
                        </div>
                        <div class="wrapper">
                            <div class="product-name">
                                <span>Daing na Bangus</span>
                            </div>
                            <div class="batch-amount">
                                <span>3 Batches</span>
                            </div>
                            <div class="time">
                                <span>31 minutes</span>
                            </div>
                            <div class="navigation-buttons">
                                <button class="serve-button">serve</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection