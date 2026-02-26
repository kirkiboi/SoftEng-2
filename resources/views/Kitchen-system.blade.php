@extends('main')
@section('kitchen production', 'System 4')
@section('content')
@vite(['resources/css/kitchen-system.css'])
@vite(['resources/js/kitchen-system.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-container">
    <div class="parent-container">
        <!-- HEADER -->
        <div class="header-container">
            <div class="header-left">
                <h2 class="page-title">Kitchen Production</h2>
            </div>
            <div class="header-right">
                <button class="action-button start-shift-btn" id="openStartShift">
                    <i class="fa-solid fa-sun"></i>
                    <span>Start Shift</span>
                </button>
                <button class="action-button end-shift-btn" id="openEndShift">
                    <i class="fa-solid fa-moon"></i>
                    <span>End Shift</span>
                </button>
                <button class="action-button recipe-manager-btn" id="openRecipeManager">
                    <i class="fa-solid fa-book"></i>
                    <span>Recipe Manager</span>
                </button>
                <button class="action-button add-batch-btn" id="openAddBatch">
                    <i class="fa-solid fa-plus"></i>
                    <span>Cook Batch</span>
                </button>
            </div>
        </div>

        <!-- SUCCESS/ERROR MESSAGES -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- KANBAN BOARD -->
        <div class="kanban-board">
            <!-- QUEUED COLUMN -->
            <div class="kanban-column" data-status="queued">
                <div class="kanban-header queued-header">
                    <i class="fa-solid fa-clock"></i>
                    <span>Queue</span>
                    <span class="count-badge">{{ count($queued) }}</span>
                </div>
                <div class="kanban-cards">
                    @forelse($queued as $log)
                    <div class="production-card" data-id="{{ $log->id }}">
                        <div class="card-header">
                            <span class="product-name">{{ $log->product_name }}</span>
                            <span class="badge badge-queued">Queued</span>
                        </div>
                        <div class="card-body">
                            <div class="card-detail"><i class="fa-solid fa-utensils"></i> {{ $log->times_cooked }}x cooked</div>
                            <div class="card-detail"><i class="fa-solid fa-users"></i> {{ $log->total_servings }} servings</div>
                        </div>
                        <div class="card-ingredients">
                            @foreach($log->deductions as $d)
                                <span class="ingredient-tag">{{ $d->ingredient_name }}: -{{ $d->quantity_deducted }}{{ $d->unit }}</span>
                            @endforeach
                        </div>
                        <div class="card-actions">
                            <button class="status-btn move-to-cooking" data-id="{{ $log->id }}" data-status="cooking">
                                <i class="fa-solid fa-fire"></i> Start Cooking
                            </button>
                            <button class="status-btn cancel-btn" data-id="{{ $log->id }}">
                                <i class="fa-solid fa-ban"></i> Cancel
                            </button>
                        </div>
                    </div>
                    @empty
                        <div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No items in queue</p></div>
                    @endforelse
                </div>
            </div>

            <!-- COOKING COLUMN -->
            <div class="kanban-column" data-status="cooking">
                <div class="kanban-header cooking-header">
                    <i class="fa-solid fa-fire"></i>
                    <span>Cooking</span>
                    <span class="count-badge">{{ count($cooking) }}</span>
                </div>
                <div class="kanban-cards">
                    @forelse($cooking as $log)
                    <div class="production-card cooking-active" data-id="{{ $log->id }}">
                        <div class="card-header">
                            <span class="product-name">{{ $log->product_name }}</span>
                            <span class="badge badge-cooking">Cooking</span>
                        </div>
                        <div class="card-body">
                            <div class="card-detail"><i class="fa-solid fa-utensils"></i> {{ $log->times_cooked }}x cooked</div>
                            <div class="card-detail"><i class="fa-solid fa-users"></i> {{ $log->total_servings }} servings</div>
                        </div>
                        <div class="card-actions">
                            <button class="status-btn move-to-done" data-id="{{ $log->id }}" data-status="done">
                                <i class="fa-solid fa-check"></i> Mark Done
                            </button>
                            <button class="status-btn waste-btn" data-id="{{ $log->id }}" data-name="{{ $log->product_name }}">
                                <i class="fa-solid fa-trash"></i> Waste
                            </button>
                        </div>
                    </div>
                    @empty
                        <div class="empty-state"><i class="fa-solid fa-fire"></i><p>Nothing cooking</p></div>
                    @endforelse
                </div>
            </div>

            <!-- DONE COLUMN -->
            <div class="kanban-column" data-status="done">
                <div class="kanban-header done-header">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Done</span>
                    <span class="count-badge">{{ count($done) }}</span>
                </div>
                <div class="kanban-cards">
                    @forelse($done as $log)
                    <div class="production-card done-card" data-id="{{ $log->id }}">
                        <div class="card-header">
                            <span class="product-name">{{ $log->product_name }}</span>
                            <span class="badge badge-done">Done</span>
                        </div>
                        <div class="card-body">
                            <div class="card-detail"><i class="fa-solid fa-utensils"></i> {{ $log->times_cooked }}x cooked</div>
                            <div class="card-detail"><i class="fa-solid fa-users"></i> {{ $log->total_servings }} servings</div>
                        </div>
                        <div class="card-actions">
                            <button class="status-btn serve-btn" data-id="{{ $log->id }}" data-status="served">
                                <i class="fa-solid fa-bell-concierge"></i> Served
                            </button>
                        </div>
                        <div class="card-detail card-time"><i class="fa-solid fa-clock"></i> {{ $log->updated_at->diffForHumans() }}</div>
                    </div>
                    @empty
                        <div class="empty-state"><i class="fa-solid fa-check-circle"></i><p>No completed batches</p></div>
                    @endforelse


                </div>
            </div>
        </div>
    </div>
</div>

<!-- RECIPE MANAGER MODAL -->
<div class="modal-container" id="recipeManagerModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <span>Recipe Manager</span>
            <button class="modal-close" id="closeRecipeManager">&times;</button>
        </div>
        <div class="modal-body modal-body-scroll">
            <div class="recipe-selector">
                <label>Select Product</label>
                <select id="recipeProductSelect" class="input searchable-select">
                    <option value="">Choose a product...</option>
                    @foreach($products->sortBy('name') as $p)
                        @if($p->category !== 'ready_made')
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->category) }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="recipe-ingredients-section" id="recipeIngredientsSection" style="display:none;">
                <h4>Current Recipe Ingredients</h4>
                <div id="currentRecipeList" class="recipe-list"></div>
                <hr>
                <h4>Add Ingredient to Recipe</h4>
                <form id="addRecipeIngredientForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ingredient</label>
                            <select id="recipeIngredientSelect" class="input searchable-select">
                                <option value="">Choose...</option>
                                @foreach(\App\Models\Ingredient::orderBy('name')->get() as $ing)
                                    <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex:0.5;">
                            <label>Qty</label>
                            <input type="number" step="0.01" min="0.01" id="recipeQuantity" class="input" placeholder="Amount">
                        </div>
                        <div class="form-group" style="flex:0.4;">
                            <label>Unit</label>
                            <select id="recipeUnitSelect" class="input">
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="ml">ml</option>
                                <option value="L">L</option>
                                <option value="pcs">pcs</option>
                            </select>
                        </div>
                        <div class="form-group form-group-btn">
                            <button type="submit" class="add-button">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- COOK BATCH MODAL -->
<div class="modal-container" id="addBatchModal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Cook New Batch</span>
            <button class="modal-close" id="closeAddBatch">&times;</button>
        </div>
        <div class="modal-body modal-body-scroll">
            <div class="form-group">
                <label>Select Product</label>
                <select id="batchProductSelect" class="input searchable-select">
                    <option value="">Choose a product...</option>
                    @foreach($products->sortBy('name') as $p)
                        @if($p->category !== 'ready_made' && $p->recipes->count() > 0)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Times to Cook</label>
                <input type="number" id="batchTimesCooked" class="input" value="1" min="1">
            </div>
            <div id="batchIngredientPreview" class="ingredient-preview" style="display:none;">
                <h4>Ingredients Required</h4>
                <div id="batchIngredientList"></div>
                <div id="batchCostEstimate" style="margin-top:0.75rem; padding:0.75rem 1rem; background:#f0f7ff; border-radius:0.8rem; border:1px solid #d0e3f7;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; color:#636e72; font-weight:600;"><i class="fa-solid fa-coins" style="margin-right:0.4rem;"></i>Estimated Batch Cost</span>
                        <span id="batchCostValue" style="font-size:1.1rem; font-weight:700; color:#2d3436;">₱0.00</span>
                    </div>
                </div>
            </div>
            <div id="batchError" class="alert alert-error" style="display:none;"></div>
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelBatch">Cancel</button>
                <button type="button" class="add-button" id="confirmBatch">Start Production</button>
            </div>
        </div>
    </div>
</div>

<!-- WASTE CONFIRMATION MODAL -->
<div class="modal-container" id="wasteModal">
    <div class="modal-content">
        <div class="modal-header" style="background: #dc3545;">
            <span>⚠ Mark as Wasted</span>
            <button class="modal-close" id="closeWaste">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:1rem; color:#666; font-size:0.9rem;">
                Batch <strong id="wasteProductName"></strong> will be marked as wasted. 
                Ingredients have already been deducted — no stock will be produced.
            </p>
            <div class="form-group">
                <label>Reason for Waste</label>
                <textarea id="wasteReason" class="input" rows="3" placeholder="e.g. Rejected batch, burnt, bad taste..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelWaste">Cancel</button>
                <button type="button" class="add-button" id="confirmWaste" style="background:#dc3545;">Confirm Waste</button>
            </div>
        </div>
    </div>
</div>

<!-- START SHIFT MODAL -->
<div class="modal-container" id="startShiftModal">
    <div class="modal-content modal-large">
        <div class="modal-header" style="background: #28a745;">
            <span>☀ Start Shift — Ingredient Stock-In</span>
            <button class="modal-close" id="closeStartShift">&times;</button>
        </div>
        <div class="modal-body modal-body-scroll">
            <p style="margin-bottom:1rem; color:#666; font-size:0.9rem;">
                Record ingredients received at the start of today's shift. Select ingredients and enter quantities.
            </p>
            <div id="shiftStockInRows">
                <div class="shift-stock-row">
                    <select class="input shift-ingredient-select searchable-select">
                        <option value="">Select ingredient...</option>
                        @foreach(\App\Models\Ingredient::orderBy('name')->get() as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                        @endforeach
                    </select>
                    <input type="number" class="input shift-quantity" placeholder="Qty" min="0.01" step="0.01">
                    <input type="text" class="input shift-supplier" placeholder="Supplier (optional)">
                </div>
            </div>
            <button type="button" class="add-row-btn" id="addShiftRow" style="margin-top:0.5rem; background:none; border:1px dashed #ccc; border-radius:8px; padding:0.5rem 1rem; cursor:pointer; color:#666; font-size:0.85rem; width:100%;">
                <i class="fa-solid fa-plus"></i> Add another ingredient
            </button>
            <div id="shiftError" class="alert alert-error" style="display:none; margin-top:1rem;"></div>
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelStartShift">Cancel</button>
                <button type="button" class="add-button" id="confirmStartShift" style="background:#28a745;">Confirm Stock-In</button>
            </div>
        </div>
    </div>
</div>

<!-- END SHIFT MODAL -->
<div class="modal-container" id="endShiftModal">
    <div class="modal-content">
        <div class="modal-header" style="background: #636e72;">
            <span>🌙 End Shift</span>
            <button class="modal-close" id="closeEndShift">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:1rem; color:#666; font-size:0.9rem;">
                Ending the shift will:
            </p>
            <ul style="margin-bottom:1rem; color:#555; font-size:0.85rem; padding-left:1.5rem;">
                <li>Mark all remaining <strong>queued</strong> and <strong>cooking</strong> batches as <span style="color:#dc3545; font-weight:700;">WASTED</span> (reason: "End of shift")</li>
                <li>Log the shift end time</li>
            </ul>
            <div style="background:#f8f9fa; border-radius:10px; padding:1rem; margin-bottom:1rem;">
                <div style="font-size:0.85rem; font-weight:700; margin-bottom:0.5rem;">Today's Summary</div>
                <div style="font-size:0.8rem; color:#666;">
                    <div>🟡 Queued: <strong>{{ count($queued) }}</strong></div>
                    <div>🔥 Cooking: <strong>{{ count($cooking) }}</strong></div>
                    <div>✅ Done/Served: <strong>{{ count($done) }}</strong></div>
                </div>
            </div>
            <p style="color:#dc3545; font-size:0.85rem; font-weight:600;">⚠ This action cannot be undone.</p>
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelEndShift">Cancel</button>
                <button type="button" class="add-button" id="confirmEndShift" style="background:#636e72;">End Shift</button>
            </div>
        </div>
    </div>
</div>

<div class="overlay" id="overlay"></div>

<!-- CUSTOM CONFIRM MODAL (replaces browser confirm()) -->
<div class="modal-container" id="customConfirmModal">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header" style="background: #2d3436;">
            <span id="customConfirmTitle">Confirm Action</span>
            <button class="modal-close" id="customConfirmClose">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:2rem;">
            <p id="customConfirmMessage" style="margin-bottom:1.5rem; color:#555; font-size:0.95rem; line-height:1.6;"></p>
            <div class="form-actions" style="justify-content:center;">
                <button type="button" class="cancel-button" id="customConfirmCancel">Cancel</button>
                <button type="button" class="add-button" id="customConfirmOk" style="background:#2975da;">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION (replaces browser alert()) -->
<div id="kitchenToast" class="kitchen-toast" style="display:none;">
    <span class="kitchen-toast-icon"></span>
    <span id="kitchenToastMessage"></span>
</div>

<style>
/* Kitchen Toast Notification (matches .my-alert from mp.css) */
.kitchen-toast {
    position: fixed;
    top: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: #2d3436;
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    z-index: 3000;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 0.8rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: ktSlideDown 0.4s ease-out;
}
.kitchen-toast.toast-error {
    border-left: 4px solid #d63031;
}
.kitchen-toast.toast-success {
    border-left: 4px solid #00b894;
}
.kitchen-toast-icon::before {
    content: "⚠";
    font-size: 1rem;
}
.kitchen-toast.toast-success .kitchen-toast-icon::before {
    content: "✓";
    background: #00b894;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.7rem;
}
@keyframes ktSlideDown {
    from { top: -50px; opacity: 0; }
    to { top: 2rem; opacity: 1; }
}

/* Searchable Dropdown */
.searchable-dropdown { position: relative; width: 100%; }
.searchable-dropdown .sd-display {
    width: 100%; padding: 0.7rem 1rem; border: 1px solid #e0e0e0; border-radius: 0.8rem;
    background: #fcfcfc; font-size: 0.95rem; cursor: pointer; display: flex;
    justify-content: space-between; align-items: center; box-sizing: border-box;
    color: #2d3436; font-family: 'Inter', sans-serif;
}
.searchable-dropdown .sd-display.placeholder { color: #999; }
.searchable-dropdown .sd-display:hover { border-color: #b2bec3; }
.searchable-dropdown .sd-panel {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1100;
    background: white; border: 1px solid #e0e0e0; border-radius: 0.8rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15); margin-top: 4px;
    display: none !important;
    max-height: 280px; overflow: hidden;
}
.searchable-dropdown .sd-panel.open { display: block !important; }
.searchable-dropdown .sd-search {
    width: 100%; padding: 0.6rem 1rem; border: none; border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem; outline: none; box-sizing: border-box;
}
.searchable-dropdown .sd-options {
    max-height: 220px; overflow-y: auto; padding: 0.3rem 0;
}
.searchable-dropdown .sd-option {
    padding: 0.5rem 1rem; cursor: pointer; font-size: 0.9rem; color: #2d3436;
    transition: background 0.15s;
}
.searchable-dropdown .sd-option:hover { background: #f0f4ff; }
.searchable-dropdown .sd-option.selected { background: #e3f2fd; font-weight: 600; }
.searchable-dropdown .sd-no-results {
    padding: 1rem; text-align: center; color: #999; font-size: 0.85rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function initSearchableSelects(container = document) {
        container.querySelectorAll('select.searchable-select').forEach(sel => {
            if (sel.dataset.sdInit) return; // already initialized
            sel.dataset.sdInit = '1';
            sel.style.display = 'none';

            const wrapper = document.createElement('div');
            wrapper.className = 'searchable-dropdown';

            const display = document.createElement('div');
            display.className = 'sd-display placeholder';
            display.innerHTML = `<span>${sel.options[0]?.text || 'Select...'}</span><i class="fa-solid fa-chevron-down" style="font-size:0.7rem; color:#999;"></i>`;

            const panel = document.createElement('div');
            panel.className = 'sd-panel';

            const search = document.createElement('input');
            search.className = 'sd-search';
            search.placeholder = 'Type to search...';

            const optionsList = document.createElement('div');
            optionsList.className = 'sd-options';

            function buildOptions(filter = '') {
                optionsList.innerHTML = '';
                let count = 0;
                Array.from(sel.options).forEach((opt, i) => {
                    if (i === 0) return; // skip placeholder
                    if (filter && !opt.text.toLowerCase().includes(filter.toLowerCase())) return;
                    count++;
                    const div = document.createElement('div');
                    div.className = 'sd-option' + (sel.value === opt.value ? ' selected' : '');
                    div.textContent = opt.text;
                    div.addEventListener('click', () => {
                        sel.value = opt.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        display.querySelector('span').textContent = opt.text;
                        display.classList.remove('placeholder');
                        panel.classList.remove('open');
                        search.value = '';
                    });
                    optionsList.appendChild(div);
                });
                if (count === 0) {
                    optionsList.innerHTML = '<div class="sd-no-results">No results found</div>';
                }
            }

            search.addEventListener('input', () => buildOptions(search.value));

            display.addEventListener('click', (e) => {
                e.stopPropagation();
                // close other panels
                document.querySelectorAll('.sd-panel.open').forEach(p => { if (p !== panel) p.classList.remove('open'); });
                panel.classList.toggle('open');
                if (panel.classList.contains('open')) {
                    buildOptions();
                    setTimeout(() => search.focus(), 50);
                }
            });

            panel.appendChild(search);
            panel.appendChild(optionsList);
            wrapper.appendChild(display);
            wrapper.appendChild(panel);
            sel.parentNode.insertBefore(wrapper, sel);
            wrapper.appendChild(sel); // move hidden select inside wrapper
        });
    }

    initSearchableSelects();
    // Close all panels on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.sd-panel.open').forEach(p => p.classList.remove('open'));
    });

    // Re-init when shift rows are dynamically added
    const origAddRow = window.addShiftRow;
    if (typeof origAddRow === 'function') {
        window.addShiftRow = function() {
            origAddRow();
            setTimeout(() => initSearchableSelects(), 50);
        };
    }
    // Also expose for manual re-init
    window.initSearchableSelects = initSearchableSelects;
});
</script>
@endsection