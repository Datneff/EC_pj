<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductManagerController
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');

            $products = Product::with(['category', 'images'])
                ->when($search, function ($query, $search) {
                    $query->where('tensanpham', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->where('trangthai', '!=', 'inactive')
                ->paginate(15);

            return view('admin.pages.product.index', compact('products'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải trang danh sách sản phẩm: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.dashboard')->with('error', 'Đã xảy ra lỗi khi tải trang danh sách sản phẩm.');
        }
    }


    public function create()
    {
        try {
            $categories = Category::all();  
            return view('admin.pages.product.create', compact('categories'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải trang tạo sản phẩm: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.product.index')->with('error', 'Đã xảy ra lỗi khi tải trang tạo sản phẩm.');
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate dữ liệu
            $validated = $request->validate([
                'tensanpham' => 'required|string|max:255',
                // 'slug' => 'required|string', // Bỏ yêu cầu validate slug
                'mota' => 'required|string',
                'thongtin_kythuat' => 'required|string',
                'id_danhmuc' => 'required|string',
                'gia' => 'required|numeric|min:0',
                'gia_khuyen_mai' => 'nullable|numeric|min:0|lt:gia',
                'donvitinh' => 'required|string',
                'xuatxu' => 'required|string',
                'soluong' => 'required|numeric|min:0',
                'trangthai' => 'required|string',
                'luotxem' => 'nullable|numeric',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Ghi log dữ liệu request để debug
            Log::info('Dữ liệu form thêm sản phẩm:', $request->all());

            // Kiểm tra danh mục
            $category = Category::where('tendanhmuc', $validated['id_danhmuc'])->first();
            if (!$category) {
                return redirect()->back()->withErrors(['id_danhmuc' => 'Danh mục không tồn tại.'])->withInput();
            }

            // Tạo slug duy nhất từ tên sản phẩm
            $slug = Str::slug($validated['tensanpham']);
            $originalSlug = $slug;
            $slugCount = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $slugCount;
                $slugCount++;
            }

            // Lưu sản phẩm
            $product = new Product();
            $product->tensanpham = $validated['tensanpham'];
            $product->slug = $slug;
            $product->mota = $validated['mota'];
            $product->thongtin_kythuat = $validated['thongtin_kythuat'];
            $product->id_danhmuc = $category->id_danhmuc;
            $product->gia = $validated['gia'];
            $product->gia_khuyen_mai = $validated['gia_khuyen_mai'] ?? null;
            $product->donvitinh = $validated['donvitinh'];
            $product->xuatxu = $validated['xuatxu'];
            $product->soluong = $validated['soluong'];
            $product->trangthai = $validated['trangthai'] === 'active' ? 1 : 0;
            $product->luotxem = $validated['luotxem'] ?? 0;
            
            // Ghi log trước khi lưu
            Log::info('Chuẩn bị lưu sản phẩm với dữ liệu:', [
                'tensanpham' => $product->tensanpham,
                'slug' => $product->slug, // Thêm log cho slug
                'id_danhmuc' => $product->id_danhmuc,
                'gia' => $product->gia,
            ]);
            
            $product->save();
            
            Log::info('Đã lưu sản phẩm thành công với ID: ' . $product->id_sanpham);

            // Lưu hình ảnh (nếu có)
            if ($request->hasFile('images')) {
                Log::info('Số lượng hình ảnh cần lưu: ' . count($request->file('images')));
                
                foreach ($request->file('images') as $index => $image) {
                    try {
                        $imagePath = $image->store('img/product', 'public');
                        Log::info('Đã lưu ảnh: ' . $imagePath);

                        $productImage = new ProductImage();
                        $productImage->id_sanpham = $product->id_sanpham;
                        $productImage->duongdan = $imagePath;
                        $productImage->alt = $validated['tensanpham'];
                        $productImage->save();
                    } catch (\Exception $imageException) {
                        Log::error('Lỗi khi lưu ảnh số ' . $index . ': ' . $imageException->getMessage());
                        // Tiếp tục xử lý các ảnh khác nếu có lỗi với một ảnh
                    }
                }
            } else {
                Log::info('Không có hình ảnh nào được upload');
            }

            return redirect()->route('admin.product.index')->with('success', 'Sản phẩm đã được thêm thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Lỗi validation khi thêm sản phẩm: ', [
                'errors' => $e->errors(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi khi lưu sản phẩm: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi lưu sản phẩm: ' . $e->getMessage())->withInput();
        }
    }

    // Hiển thị chi tiết sản phẩm
    public function show($id_sanpham)
    {
        try {
            $product = Product::with('category', 'images')->findOrFail($id_sanpham);
            return view('admin.pages.product.show', compact('product'));
        } catch (\Exception $e) {
            Log::error('Lỗi khi hiển thị chi tiết sản phẩm: ' . $e->getMessage(), [
                'id_sanpham' => $id_sanpham,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            return redirect()->route('admin.product.index')->with('error', 'Không thể hiển thị chi tiết sản phẩm.');
        }
    }


    // Hiển thị form chỉnh sửa sản phẩm
    public function edit(string $id_sanpham)
    {
        try {
            // Lấy thông tin sản phẩm cùng với danh sách ảnh
            $product = Product::with('images')->findOrFail($id_sanpham);

            // Lấy danh sách danh mục để hiển thị trong form
            $categories = Category::all();

            // Đếm số lượng sản phẩm trong giỏ hàng (nếu cần)
            $count = DB::table('san_pham_gio_hang')
                ->where('id_sanpham', $id_sanpham)
                ->sum('id_sanpham');

            // Lấy thời gian cập nhật cuối cùng của sản phẩm
            $thoigian = $product->updated_at;

            // Trả về view chỉnh sửa sản phẩm
            return view('admin.pages.product.edit', compact('product', 'categories', 'count', 'thoigian'));
        } catch (\Exception $e) {
            // Ghi log lỗi nếu xảy ra vấn đề
            Log::error('Lỗi khi tải trang chỉnh sửa sản phẩm: ' . $e->getMessage(), [
                'id_sanpham' => $id_sanpham,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Chuyển hướng về danh sách sản phẩm với thông báo lỗi
            return redirect()->route('admin.product.index')->with('error', 'Không thể tải trang chỉnh sửa sản phẩm.');
        }
    }


    // Cập nhật thông tin sản phẩm
    public function update(Request $request, string $id_sanpham)
    {
        try {
            // Lấy thông tin sản phẩm hiện tại
            $product = Product::findOrFail($id_sanpham);

            // Validate dữ liệu
            $validated = $request->validate([
                'tensanpham' => 'required|string|max:255|unique:san_pham,tensanpham,' . $id_sanpham . ',id_sanpham',
                'slug' => 'nullable|string', // Slug có thể để trống
                'mota' => 'required|string',
                'gia' => 'required|numeric|min:0',
                'gia_khuyen_mai' => 'nullable|numeric|min:0|lt:gia',
                'donvitinh' => 'required|string',
                'xuatxu' => 'required|string',
                'soluong' => 'required|integer|min:0',
                'trangthai' => 'required|string|in:active,inactive',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Cập nhật thông tin sản phẩm
            $product->tensanpham = $request->tensanpham;
            $product->slug = Str::slug($request->tensanpham);
            $product->mota = $request->mota;
            $product->gia = $request->gia;
            $product->gia_khuyen_mai = $request->gia_khuyen_mai;
            $product->donvitinh = $request->donvitinh;
            $product->xuatxu = $request->xuatxu;
            $product->soluong = $request->soluong;
            $product->trangthai = $request->trangthai === 'active' ? 1 : 0;
            $product->save();

            // Lưu log cập nhật thành công
            Log::info('Đã cập nhật sản phẩm ID: ' . $id_sanpham);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    try {
                        $imagePath = $image->store('img/product', 'public');
                        $productImage = new ProductImage();
                        $productImage->id_sanpham = $product->id_sanpham;
                        $productImage->duongdan = $imagePath;
                        $productImage->alt = $product->tensanpham;
                        $productImage->save();
                        
                        Log::info('Đã lưu ảnh mới cho sản phẩm ID: ' . $id_sanpham . ', đường dẫn: ' . $imagePath);
                    } catch (\Exception $imageException) {
                        Log::error('Lỗi khi lưu ảnh cho sản phẩm ID: ' . $id_sanpham . ': ' . $imageException->getMessage());
                    }
                }
            }

            // Xử lý xóa hình ảnh nếu có yêu cầu
            if ($request->has('delete_images')) {
                foreach ($request->input('delete_images') as $imageId) {
                    try {
                        $image = ProductImage::find($imageId);
                        if ($image && Storage::disk('public')->exists($image->duongdan)) {
                            Storage::disk('public')->delete($image->duongdan);
                            Log::info('Đã xóa file ảnh: ' . $image->duongdan);
                        }
                        if ($image) {
                            $image->delete();
                            Log::info('Đã xóa bản ghi ảnh ID: ' . $imageId);
                        }
                    } catch (\Exception $deleteException) {
                        Log::error('Lỗi khi xóa ảnh ID: ' . $imageId . ': ' . $deleteException->getMessage());
                    }
                }
            }

            return redirect()->route('admin.product.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Lỗi validation khi cập nhật sản phẩm: ', [
                'id_sanpham' => $id_sanpham,
                'errors' => $e->errors(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage(), [
                'id_sanpham' => $id_sanpham,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi cập nhật sản phẩm: ' . $e->getMessage())->withInput();
        }
    }

    // Ẩn sản phẩm
    public function destroy($id_sanpham)
    {
        try {
            $product = Product::findOrFail($id_sanpham);
            $product->trangthai = 'inactive';
            $product->save();
            
            Log::info('Đã ẩn sản phẩm ID: ' . $id_sanpham);

            return redirect()->route('admin.product.index')->with('success', 'Sản phẩm đã được ẩn');
        } catch (\Exception $e) {
            Log::error('Lỗi khi ẩn sản phẩm: ' . $e->getMessage(), [
                'id_sanpham' => $id_sanpham,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.product.index')->with('error', 'Đã xảy ra lỗi khi ẩn sản phẩm.');
        }
    }

    public function deleteImage($imageId) 
    {
        try {
            $image = ProductImage::findOrFail($imageId);
            
            // Xóa file vật lý
            if (Storage::disk('public')->exists($image->duongdan)) {
                Storage::disk('public')->delete($image->duongdan);
                Log::info('Đã xóa file ảnh: ' . $image->duongdan);
            }
            
            // Xóa bản ghi
            $image->delete();
            Log::info('Đã xóa bản ghi ảnh ID: ' . $imageId);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa ảnh ID: ' . $imageId . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function bulkDeleteImages(Request $request)
    {
        try {
            $imageIds = $request->input('image_ids', []);

            // Ghi log danh sách ID ảnh nhận được
            Log::info('Danh sách ID ảnh cần xóa:', $imageIds);

            if (empty($imageIds)) {
                return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một ảnh để xóa.');
            }

            // Lấy danh sách ảnh cần xóa
            $images = ProductImage::whereIn('id_hinhanh', $imageIds)->get();

            if ($images->isEmpty()) {
                Log::warning('Không tìm thấy ảnh nào với các ID: ' . implode(', ', $imageIds));
                return redirect()->back()->with('error', 'Không tìm thấy ảnh nào để xóa.');
            }

            foreach ($images as $image) {
                // Xóa file vật lý
                if (Storage::disk('public')->exists($image->duongdan)) {
                    Storage::disk('public')->delete($image->duongdan);
                    Log::info('Đã xóa file ảnh: ' . $image->duongdan);
                }

                // Xóa bản ghi
                $image->delete();
                Log::info('Đã xóa bản ghi ảnh ID: ' . $image->id_hinhanh);
            }

            return redirect()->back()->with('success', 'Đã xóa các ảnh được chọn thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa ảnh: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xóa ảnh.');
        }
    }
}
