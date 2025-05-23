@extends('users.layouts.layout')

@section('title', $Product->tensanpham)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/comment.css') }}" type="text/css">
@endpush
@section('content')
    <!-- Product Details Section Begin -->
    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        <div class="product__details__pic__item image-remove-bg">
                            <img class="product__details__pic__item--large"
                                src="{{ asset('storage/' . ($Product->images->isNotEmpty() ? $Product->images->first()->duongdan : 'img/products/default.jpg')) }}"
                                alt="{{ $Product->tensanpham }}">
                        </div>
                        <div class="product__details__pic__slider owl-carousel">
                            @foreach($Product->images as $image)
                            <div class="image-remove-bg">
                                <img data-imgbigurl="{{ asset('storage/' . $image->duongdan) }}"
                                    src="{{ asset('storage/' . $image->duongdan) }}"
                                    alt="{{ $Product->tensanpham }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        <h3>{{ $Product->tensanpham }}</h3>
                        <!-- Phần chấm điểm -->
                        <div class="product__details__rating">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half-o"></i>
                            <span>({{ $totalReviews }} đánh giá)</span>
                        </div>
                        <!-- phần giá -->
                        <div class="product__details__price">
                            @if ($Product->gia_khuyen_mai !== null && $Product->gia_khuyen_mai >= 0)
                                {{ number_format($Product->gia_khuyen_mai, 0, ',', '.') }}đ
                                <span>{{ number_format($Product->gia, 0, ',', '.') }}đ</span>
                            @else
                                {{ number_format($Product->gia, 0, ',', '.') }}đ
                            @endif
                        </div>
                        <!-- phần mô tả ngắn -->
                        {{-- <p>{!! nl2br(e(Str::limit($Product->mota, 300, '...'))) !!}</p> --}}
                        <p><?php
                            $mota = $Product->mota;
                            $sentences = preg_split('/(?<=[.?!])\s+/', $mota); // Chia mô tả thành các câu
                            $first_three_sentences = implode(' ', array_slice($sentences, 0, 3)); // Lấy 3 câu đầu
                            echo nl2br(e($first_three_sentences)); // In ra với định dạng line break
                            ?></p>
                        <!-- phần số lượng -->
                        <div class="product__details__quantity">
                            <div class="quantity">
                                <div class="pro-qty">
                                    <span class="dec qtybtn">-</span>
                                    @if ($Product->soluong > 0)
                                    <input id="quantity" value="1" min="1"
                                        max="{{ $Product->soluong }}">
                                    @else
                                    <input value="0" min="0"
                                        max="{{ $Product->soluong }}">
                                    @endif
                                    <span class="inc qtybtn">+</span>
                                </div>
                            </div>
                        </div>
                        <button id="addToCartButton" {{ $Product->soluong > 0 ? '' : 'disabled' }} onclick="addToCart({{ $Product->id_sanpham }})" class="primary-btn no-border">ADD TO CART</button>
                        <div class="favorite-btn-wrapper" onclick="toggleFavorite({{ $Product->id_sanpham }})">
                            <button type="button"
                                    class="favorite-btn {{ $isFavorited ? 'active' : '' }}"
                                    data-id="{{ $Product->id_sanpham }}"
                                    >
                                <i class="fa fa-heart-o heart-empty"></i>
                                <i class="fa fa-heart heart-filled"></i>
                            </button>
                        </div>
                        <ul>
                            <li><b>Tình Trạng</b>
                                <span>
                                    @if ($Product->soluong > 0)
                                        <span class="stock-status in-stock">Còn hàng ({{ $Product->soluong }} sản phẩm)</span>
                                    @else
                                        <span class="stock-status out-of-stock">Hết hàng</span>
                                    @endif
                                </span>
                            </li>
                            <li><b>Giao hàng</b> <span>01 ngày vận chuyển. <samp>Free pickup today!</samp></span></li>
                            {{-- <li><b>Trọng lượng</b> <span>0.5 kg</span></li> --}}
                            <li><b>Xuất xứ</b> <span><samp>{{$Product->xuatxu}}</samp></span></li>
                            <li><b>Chia sẻ</b>
                                <div class="share">
                                    <a href="#"><i class="fa fa-facebook"></i></a>
                                    <a href="#"><i class="fa fa-twitter"></i></a>
                                    <a href="#"><i class="fa fa-instagram"></i></a>
                                    <a href="#"><i class="fa fa-pinterest"></i></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab"
                                    aria-selected="true">Mô tả</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab"
                                    aria-selected="false">Thông tin</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab"
                                    aria-selected="false">Đánh giá <span>({{$totalReviews}})</span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <h6>Mô tả sản phẩm</h6>
                                    <div class="product-description">
                                        @if(strlen($Product->mota) > 300)
                                            <div class="description-container">
                                                <p class="description-short">
                                                    {{ Str::limit($Product->mota, 300, '...') }}
                                                </p>
                                                <p class="description-full">
                                                    {{-- {!! nl2br(e($Product->mota)) !!} --}}
                                                    {{ $Product->mota }}
                                                </p>
                                            </div>
                                            <button class="read-more-btn" onclick="toggleDescription(this)">
                                                <span class="text">Xem thêm</span>
                                                <i class="fa fa-chevron-down icon"></i>
                                            </button>
                                        @else
                                            <div class="description-text">
                                                {!! nl2br(e($Product->mota)) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-2" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <h6>Thông tin sản phẩm<h6>
                                    <p>{{$Product->thongtin_kythuat}}</p>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <!-- phần bình luận -->
                                @include('users.partials.shop-details.comment', [
                                    'product' => $Product,
                                    'reviews' => $reviews,
                                    'totalReviews' => $totalReviews,
                                    'averageRating' => $averageRating,
                                    'ratingStats' => $ratingStats,
                                    'userReview' => $userReview,
                                ])
                                <!-- Pagination Section Begin -->
                            <div class="product__pagination">
                                @if ($reviews->lastPage() > 1)
                                    @if ($reviews->currentPage() > 1)
                                        <a href="{{ $reviews->appends(request()->except('page'))->previousPageUrl() }}">
                                            <i class="fa fa-long-arrow-left"></i>
                                        </a>
                                    @endif

                                    @php
                                        $start = max($reviews->currentPage() - 2, 1);
                                        $end = min($start + 4, $reviews->lastPage());
                                        $start = max(min($start, $reviews->lastPage() - 4), 1);
                                    @endphp

                                    @if ($start > 1)
                                        <a href="{{ $reviews->appends(request()->except('page'))->url(1) }}">1</a>
                                        @if ($start > 2)
                                            <span>...</span>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        <a href="{{ $reviews->appends(request()->except('page'))->url($i) }}"
                                            class="{{ $reviews->currentPage() == $i ? 'active' : '' }}">
                                            {{ $i }}
                                        </a>
                                    @endfor

                                    @if ($end < $reviews->lastPage())
                                        @if ($end < $reviews->lastPage() - 1)
                                            <span>...</span>
                                        @endif
                                        <a href="{{ $reviews->appends(request()->except('page'))->url($reviews->lastPage()) }}">
                                            {{ $reviews->lastPage() }}
                                        </a>
                                    @endif

                                    @if ($reviews->hasMorePages())
                                        <a href="{{ $reviews->appends(request()->except('page'))->nextPageUrl() }}">
                                            <i class="fa fa-long-arrow-right"></i>
                                        </a>
                                    @endif
                                @endif
                            </div>
                            <!-- Pagination Section End -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Details Section End -->

    <!-- Related Product Section Begin -->
    <section class="related-product">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title related__product__title">
                        <h2>Sản phẩm liên quan</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach ($relatedProducts as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product__item">
                            <div class="product__item__pic set-bg bg-blend"
                                data-setbg="{{ asset('storage/' . ($product->images->isNotEmpty() ? $product->images->first()->duongdan : 'img/products/default.jpg')) }}">
                                @if ($product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                                    <div class="product__discount__percent">-{{floor(($product->gia - $product->gia_khuyen_mai) / $product->gia * 100)}}%</div>
                                @endif
                                <ul class="product__item__pic__hover">
                                    @include('users.partials.pic-hover', ['product' => $product])
                                </ul>
                            </div>
                            <div class="product__item__text">
                                <h6><a href="{{ route('shop_details', $product->slug) }}">{{ $product->tensanpham }}</a></h6>
                                <h5>
                                    <div class="product__item__price">
                                        @if ($product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                                            {{ number_format($product->gia_khuyen_mai, 0, ',', '.') }}đ
                                            <span>{{ number_format($product->gia, 0, ',', '.') }}đ</span>
                                        @else
                                            {{ number_format($product->gia, 0, ',', '.') }}đ
                                        @endif
                                    </div>
                                </h5>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Related Product Section End -->
@endsection

@push('scripts')
    {{-- <script>
        function addToCart() {
            @if (!Auth::check())
                window.location.href = "{{ route('login') }}";
                return;
            @endif

            const quantity = parseInt($('#quantity').val());
            const productId = '{{ $Product->id_sanpham }}';

            $.ajax({
                url: '{{ route('cart.add') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    id_sanpham: productId,
                    soluong: quantity
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart count in header if needed

                        // Show success message
                        alert(response.message);
                        // Redirect to cart page
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Error adding product to cart');
                    }
                },
                error: function(xhr) {
                    console.error('Cart error:', xhr);
                    alert('Error adding product to cart. Please try again.');
                }
            });
        }
    </script> --}}
    <script src="{{ asset('js/shop-details.js') }}"></script>
@endpush
