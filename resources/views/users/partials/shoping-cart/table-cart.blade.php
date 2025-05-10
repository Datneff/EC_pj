<meta name="csrf-token" content="{{ csrf_token() }}">

<table>
    <thead>
        <tr>
            <th class="shoping__product">Products</th>
            <th>Status</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($cartItems as $item)
        @php
            $product = $item->product ?? null;
            $isOutOfStock = !$product || $product->soluong <= 0;
            $isLimitedStock = $product && $product->soluong < $item->soluong;
        @endphp
        
        <tr id="cart-item-{{ $item->id_sp_giohang }}" class="{{ $isLimitedStock ? 'limited-stock' : '' }} {{ $isOutOfStock ? 'out-of-stock' : '' }}">
            <td class="shoping__cart__item" 
                @if($product && !empty($product->slug))
                    onclick="window.location={{ json_encode(route('shop_details', $product->slug)) }}"
                @endif
                style="cursor: pointer;">
                <img src="{{ $product ? asset('storage/' . ($product->images->first()->duongdan ?? 'img/products/default.jpg')) : asset('img/products/default.jpg') }}" 
                     alt="{{ $product->tensanpham ?? 'No name' }}" 
                     width="100">
                <h5>{{ $product->tensanpham ?? 'No name' }}</h5>
            </td>
            <td class="item__status">
                @if($isOutOfStock)
                    <span class="out-stock">Out of Stock</span>
                @else
                    <span class="in-stock">In Stock ({{ $product->soluong }})</span>
                    @if($isLimitedStock)
                        <div class="stock-warning">Only {{ $product->soluong }} available</div>
                    @endif
                @endif
            </td>
            <td class="shoping__cart__price">
                @if ($product && $product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                    {{ number_format($product->gia_khuyen_mai, 0, ',', '.') }}đ
                    <span class="text-decoration-line-through text-muted">{{ number_format($product->gia, 0, ',', '.') }}đ</span>
                @else
                    {{ number_format($product->gia ?? 0, 0, ',', '.') }}đ
                @endif
            </td>

            <td class="shoping__cart__quantity">
                <div class="pro-qty">
                    <button type="button" class="dec qtybtn">-</button>
                    <input
                        type="text"
                        name="soluong"
                        value="{{ $item->soluong }}"
                        class="quantity-input"
                        data-id="{{ $item->id_sp_giohang }}"
                        data-max="{{ $product->soluong ?? 1 }}"
                        {{ $isOutOfStock ? 'disabled' : '' }}>
                    <button type="button" class="inc qtybtn">+</button>
                </div>
            </td>
            <td class="shoping__cart__total">

                @if ($product && $product->gia_khuyen_mai !== null && $product->gia_khuyen_mai >= 0)
                   
                    {{ number_format($product->gia_khuyen_mai * $item->soluong, 0, ',', '.') }}đ
                @else
                   
                    {{ number_format(($product->gia ?? 0) * $item->soluong, 0, ',', '.') }}đ
                @endif
            </td>
            <td class="shoping__cart__item__close">
                <form action="{{ route('cart.remove', $item->id_sp_giohang) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon_close" style="background: none; border: none; cursor: pointer;"></button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center range-cart-favorites">Your cart is empty</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if ($cartItems instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="mt-4" style="text-align: center">
    @include('users.components.pagination', ['paginator' => $cartItems, 'customUrl' => route('cart.index')])
</div>
@endif

<style>
    .qtybtn {
        cursor: pointer;
        padding: 0 10px;
        user-select: none;
        border: none; /* Loại bỏ viền */
        background: none; /* Loại bỏ màu nền */
    }

    .qtybtn:focus {
        outline: none; /* Loại bỏ viền khi nhấn vào nút */
    }

    .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.pro-qty .qtybtn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.stopPropagation(); // Ngăn chặn sự kiện lan truyền lên phần tử cha

            const input = this.parentNode.querySelector('.quantity-input');
            const maxQuantity = parseInt(input.getAttribute('data-max')) || Infinity;
            const minQuantity = 1;

            let currentValue = parseInt(input.value) || 1;

            if (this.classList.contains('inc') && currentValue < maxQuantity) {
                currentValue++;
            } else if (this.classList.contains('dec') && currentValue > minQuantity) {
                currentValue--;
            }

            input.value = currentValue;

            // Gửi yêu cầu AJAX để cập nhật số lượng
            updateCartQuantity(input);
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function () {
            const maxQuantity = parseInt(this.getAttribute('data-max')) || Infinity;
            const minQuantity = 1;

            let currentValue = parseInt(this.value) || 1;

            if (currentValue > maxQuantity) {
                this.value = maxQuantity;
            } else if (currentValue < minQuantity) {
                this.value = minQuantity;
            }

            // Gửi yêu cầu AJAX để cập nhật số lượng
            updateCartQuantity(this);
        });
    });

    function updateCartQuantity(input) {
        const cartItemId = input.getAttribute('data-id');
                
        const newQuantity = parseInt(input.value);
        const maxQuantity = parseInt(input.getAttribute('data-max')) || Infinity; // Lấy số lượng tối đa
        const minQuantity = 1; // Số lượng tối thiểu

        // Kiểm tra nếu số lượng không hợp lệ
        if (isNaN(newQuantity) || newQuantity < minQuantity || newQuantity > maxQuantity) {
            console.error(`Invalid quantity for cartItemId ${cartItemId}: ${newQuantity}. Must be between ${minQuantity} and ${maxQuantity}.`);
            return; // Không gửi yêu cầu nếu số lượng không hợp lệ
        }
        // console.log(csrfToken); // Kiểm tra xem csrfToken có giá trị không

        // Gửi yêu cầu AJAX để cập nhật số lượng
        fetch(`/Ecommerce_project/public/cart/items/${cartItemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ soluong: newQuantity })
        })
            .then(response => {
                console.log(response); // Kiểm tra xem phản hồi có hợp lệ không
                return response.json();})
           
            .then(data => {
                
            
                if (data.success) {
                    // Cập nhật tổng tiền của sản phẩm
                    const element = document.querySelector(`#cart-item-${cartItemId} .shoping__cart__total`);
                    console.log(element); // Kiểm tra xem phần tử có tồn tại không
                    // console.log(document.querySelector('.shoping__cart__total')); // Kiểm tra phần tử class
                    document.querySelector(`#cart-item-${cartItemId} .shoping__cart__total`).innerText = data.newTotal;
                    
                    // document



                    console.log(data);
                    // console.log(data.newTotal);

                    // Cập nhật tổng tiền của giỏ hàng
                    document.querySelector('#cart-total').innerText = data.cartTotal;
                    console.log(data.cartTotal);
                } else {
                    alert(data.message || 'Đã xảy ra lỗi khi cập nhật số lượng.');
                    
                    
                    
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi khi cập nhật số lượng.');
                
            });
    }
</script>