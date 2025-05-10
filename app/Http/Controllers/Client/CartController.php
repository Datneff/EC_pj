<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.guest');
    }

    private function getOrCreateCart()
    {
        $cart = Cart::where('id_nguoidung', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create(['id_nguoidung' => Auth::id()]);
        }
        return $cart;
    }

    private function calculateTotal($cartItems)
    {
        return $cartItems->sum(function ($item) {
            $price = ($item->product->gia_khuyen_mai !== null && $item->product->gia_khuyen_mai >= 0)
                ? $item->product->gia_khuyen_mai
                : $item->product->gia;
            return $price * $item->soluong;
        });
    }

    private function calculateSubTotal($cartItems)
    {
        return $cartItems->sum(function ($item) {
            $price = $item->product->gia;
            return $price * $item->soluong;
        });
    }

    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cartItemsQuery = CartItem::with(['product.images'])
            ->where('id_giohang', $cart->id_giohang)
            ->orderBy('created_at', 'desc');

        $allCartItems = $cartItemsQuery->get();
        $overStockItems = $allCartItems->filter(function ($item) {
            return $item->soluong > $item->product->soluong;
        });

        $cartItems = $cartItemsQuery->paginate(3);

        $subTotal = $this->calculateSubTotal($allCartItems);
        $total = $this->calculateTotal($allCartItems);
        $discount = $subTotal > 0 ? ($subTotal - $total) / $subTotal * 100 : 0;

        return view('users.pages.shoping-cart', compact('cartItems', 'subTotal', 'total', 'discount', 'overStockItems'));
    }

    public function addToCart(Request $request)
    {
        try {
            Log::info('Phương thức addToCart được gọi.', ['request' => $request->all()]);

            $request->validate([
                'id_sanpham' => 'required|exists:san_pham,id_sanpham',
                'soluong' => 'required|integer|min:1'
            ]);

            Log::info('Dữ liệu đầu vào hợp lệ.', ['id_sanpham' => $request->id_sanpham, 'soluong' => $request->soluong]);

            $product = Product::findOrFail($request->id_sanpham);
            if ($request->soluong > $product->soluong) {
                Log::warning('Số lượng sản phẩm không đủ.', [
                    'id_sanpham' => $request->id_sanpham,
                    'soluong_yc' => $request->soluong,
                    'soluong_ton' => $product->soluong
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm không đủ.'
                ]);
            }

            $cart = $this->getOrCreateCart();
            Log::info('Giỏ hàng được lấy hoặc tạo.', ['id_giohang' => $cart->id_giohang]);

            $productIsExist = CartItem::where('id_giohang', $cart->id_giohang)
                ->where('id_sanpham', $request->id_sanpham)
                ->first();

            if ($productIsExist) {
                if ($productIsExist->soluong + $request->soluong > $product->soluong) {
                    $availableQuantity = $product->soluong - $productIsExist->soluong;
                    Log::warning('Số lượng vượt quá tồn kho khi cập nhật giỏ hàng.', [
                        'id_sanpham' => $request->id_sanpham,
                        'soluong_hien_tai' => $productIsExist->soluong,
                        'soluong_them' => $request->soluong,
                        'soluong_ton' => $product->soluong
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Số lượng còn lại trong kho là {$availableQuantity} sản phẩm."
                    ]);
                }

                $productIsExist->update([
                    'soluong' => $productIsExist->soluong + $request->soluong
                ]);
                Log::info('Cập nhật số lượng sản phẩm trong giỏ hàng.', [
                    'id_sanpham' => $request->id_sanpham,
                    'soluong_moi' => $productIsExist->soluong
                ]);
            } else {
                CartItem::create([
                    'id_giohang' => $cart->id_giohang,
                    'id_sanpham' => $request->id_sanpham,
                    'soluong' => $request->soluong
                ]);
                Log::info('Thêm sản phẩm mới vào giỏ hàng.', [
                    'id_sanpham' => $request->id_sanpham,
                    'soluong' => $request->soluong
                ]);
            }

            $cartTotal = $this->calculateTotal($cart->fresh()->cartItems);
            $cartCount = $cart->cartItems->count();
            Log::info('Tính toán giỏ hàng sau khi thêm sản phẩm.', [
                'cartTotal' => $cartTotal,
                'cartCount' => $cartCount
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('cart.index'),
                'cartTotal' => number_format($cartTotal, 0, ',', '.') . 'đ',
                'cartCount' => $cartCount
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xảy ra trong phương thức addToCart.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.'
            ]);
        }
    }

    public function removeItem(Request $request, $id)
    {
        try {
            $cart = $this->getOrCreateCart();
            CartItem::destroy($id);
            // $cartItem = CartItem::findOrFail($id);
            // $itemTotal = ($cartItem->product->gia_khuyen_mai ?? $cartItem->product->gia) * $cartItem->soluong;
            // Recalculate totals
            $subTotal = $this->calculateSubTotal($cart->fresh()->cartItems);
            $total = $this->calculateTotal($cart->fresh()->cartItems);
            $discount = $subTotal > 0 ? ($subTotal - $total) / $subTotal * 100 : 0;
            $cartCount = $cart->cartItems->count();

            return response()->json([
                'success' => true,
                'cartTotal' => number_format($total, 0, ',', '.') . 'đ',
                'subTotal' => number_format($subTotal, 0, ',', '.') . 'đ',
                // 'newTotal' => number_format($itemTotal, 0, ',', '.') . 'đ',
                'discount' => floor($discount),
                'cartCount' => $cartCount
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xảy ra trong phương thức removeItem.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa sản phẩm.'
            ], 500);
        }
    }

    public function clearCart()
    {
        try {
            $cart = $this->getOrCreateCart();
            $cartItems = CartItem::where('id_giohang', $cart->id_giohang);

            if ($cartItems->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng của bạn đã trống.'
                ]);
            }

            $cartItems->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa toàn bộ sản phẩm khỏi giỏ hàng thành công.'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xảy ra trong phương thức clearCart.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa toàn bộ giỏ hàng.'
            ], 500);
        }
    }

    public function updateQuantity(Request $request, $id_sp_giohang)
    {
        try {
            // Validate input
            $request->validate([
                'soluong' => 'required|integer|min:1'
            ]);

            // Find cart item and product
            $cartItem = CartItem::findOrFail($id_sp_giohang);
            $product = Product::findOrFail($cartItem->id_sanpham);

            // Check stock availability
            if ($request->soluong > $product->soluong) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng vượt quá tồn kho'
                ], 400);
            }

            // Update cart item quantity
            $cartItem->update([
                'soluong' => $request->soluong
            ]);

            // Recalculate totals
            $cart = $cartItem->cart;
            $cartTotal = $this->calculateTotal($cart->cartItems);
            $subTotal = $this->calculateSubTotal($cart->cartItems);
            $discount = $subTotal > 0 ? ($subTotal - $cartTotal) / $subTotal * 100 : 0;
            $itemTotal = (($cartItem->product->gia_khuyen_mai !== null && $cartItem->product->gia_khuyen_mai >= 0)
                ? $cartItem->product->gia_khuyen_mai
                : $cartItem->product->gia) * $cartItem->soluong;

            // Return JSON response
            return response()->json([
                'success' => true,
                'cartTotal' => number_format($cartTotal, 0, ',', '.') . 'đ',
                'itemTotal' => number_format($itemTotal, 0, ',', '.') . 'đ',
                'subTotal' => number_format($subTotal, 0, ',', '.') . 'đ',
                'discount' => floor($discount)
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xảy ra trong phương thức updateQuantity.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật số lượng.'
            ], 500);
        }
    }
}
