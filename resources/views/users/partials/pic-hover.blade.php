<li><a href="javascript:void(0)" onclick="quickToggleFavorite('{{ $product->id_sanpham }}')"><i class="fa fa-heart"></i></a></li>
<li><a href="#"><i class="fa fa-retweet"></i></a></li>
<li><a href="javascript:void(0)" onclick="quickAddToCart('{{ $product->id_sanpham }}')"><i class="fa fa-shopping-cart"></i></a></li>

<script>
    function quickAddToCart(productId) {
        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id_sanpham: productId,
                soluong: 1 // Mặc định thêm 1 sản phẩm
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("Sản phẩm đã được thêm vào giỏ hàng!");
                // Cập nhật giao diện giỏ hàng nếu cần
                updateCartUI(data.cartCount, data.cartTotal);
            } else {
                alert(data.message || "Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.");
            }
        })
        .catch(error => {
            console.error("Lỗi:", error);
            alert("Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại sau.");
        });
    }

    // Hàm cập nhật giao diện giỏ hàng (nếu cần)
    function updateCartUI(cartCount, cartTotal) {
        const cartCountElement = document.getElementById("cart-count");
        const cartTotalElement = document.getElementById("cart-total");

        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
        }

        if (cartTotalElement) {
            cartTotalElement.textContent = cartTotal;
        }
    }
</script>
