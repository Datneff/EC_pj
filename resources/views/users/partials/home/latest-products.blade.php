<!-- Latest Section Begin -->
<section class="featured spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>Latest Products</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="categories__slider owl-carousel">
                @foreach ($new_products as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 mix oranges fresh-meat">
                        <div class="featured__item">
                            <div class="featured__item__pic set-bg bg-blend"
                            data-setbg="{{ asset('storage/' . ($product->images->isNotEmpty() ? $product->images->first()->duongdan : 'img/products/default.jpg')) }}">
                            @if ($product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                                <div class="product__discount__percent">-{{floor(($product->gia - $product->gia_khuyen_mai) / $product->gia * 100)}}%</div>
                            @endif
                            <ul class="featured__item__pic__hover">
                                @include('users.partials.pic-hover', ['product' => $product])
                            </ul>
                            </div>
                            <div class="featured__item__text">
                                <span>{{$product->category->tendanhmuc}}</span>
                                <h6><a href="{{ route('shop_details', $product->slug) }}">{{ $product->tensanpham }}</a></h6>
                                <h5>
                                    <div class="featured__item__price">
                                        @if ($product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                                            {{ number_format($product->gia_khuyen_mai, 0, ',', '.') }}đ
                                            <span>{{ number_format($product->gia, 0, ',', '.') }}đ</span>
                                            <div class="discount">Giảm đến {{floor(($product->gia - $product->gia_khuyen_mai) / $product->gia * 100)}}%</div>
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
    </div>
</section>
<!-- Latest Section End -->
