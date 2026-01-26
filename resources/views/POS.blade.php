    @extends('main')
    @section('pos', 'System 1')
    @section('content')
    @vite(['resources/css/pos.css'])
        <div class="pos-main-container">
            <div class="food-options-container">
                <div class="header-container">
                    <div class="input-wrapper">
                        <input type="text" placeholder="Search">
                    </div>
                    <div class="filter-wrapper">
                        <button>Meals</button>
                        <button>Drinks</button>
                        <button>Snacks</button>
                    </div>
                </div>
                <div class="food-options">
                    <div class="food-box">
                        <div class="image-container">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                            <span>Corned Beef</span>
                        </div>
                        <div class="price-container">
                            <span>P75.00</span>
                        </div>                       
                    </div>
                    <div class="food-box">
                        <div class="image-container">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                            <span>Corned Beef</span>
                        </div>
                        <div class="price-container">
                            <span>P75.00</span>
                        </div>                       
                    </div>
                    <div class="food-box">
                        <div class="image-container">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                            <span>Corned Beef</span>
                        </div>
                        <div class="price-container">
                            <span>P75.00</span>
                        </div>                       
                    </div>
                    <div class="food-box">
                        <div class="image-container">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                            <span>Corned Beef</span>
                        </div>
                        <div class="price-container">
                            <span>P75.00</span>
                        </div>                       
                    </div>
                </div>
            </div>

            <div class="transaction-container">
                <div class="icon-container">
                    <span>New Transaction</span>
                    <i class="fa-solid fa-trash"></i>
                </div>
                <div class="added-product-container">
                    <div class="added-food-box">
                        <div class="added-food-box-photo">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                        </div>
                        <div class="added-food-box-name">
                            <span>Corned Beef</span>
                            <div class="added-food-box-controls">
                                <span>-</span>
                                <span>1</span>
                                <span>+</span>
                            </div>
                        </div>
                        <div class="added-food-box-price">
                            <span>P75.00</span>
                        </div>
                    </div>
                    
                    <div class="added-food-box">
                        <div class="added-food-box-photo">
                            <img src="{{asset('photos/siomai.jpg')}}" alt="">
                        </div>
                        <div class="added-food-box-name">
                            <span>Corned Beef</span>
                            <div class="added-food-box-controls">
                                <span>-</span>
                                <span>1</span>
                                <span>+</span>
                            </div>
                        </div>
                        <div class="added-food-box-price">
                            <span>P75.00</span>
                        </div>
                    </div>
                </div>
                <div class="checkout-container">
                    <div class="added-food-box-price">
                        <div class="added-food-price-container">
                            <span>P75.00</span>
                        </div>
                        <div class="checkout-container">
                            <span>Checkout</span>
                            <i class="fa-solid fa-angles-left drop-down-container-button"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection