@extends('Admin.Layout.Layout')

@section('title', 'Chỉnh sửa sản phẩm')

@section('namepage', 'Chỉnh sửa sản phẩm')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="container mt-4">
                    <!-- Hiển thị số lượng sản phẩm trong giỏ hàng -->
                    <div class="card-header">
                        <h3 class="text-center">Số lượng giỏ hàng đang chứa sản phẩm: <strong>{{ number_format($count) }}</strong></h3>
                        <h3 class="text-center">Thời gian chỉnh sửa gần nhất: <strong>{{ $thoigian }}</strong></h3>
                    </div>

                    <!-- Form chỉnh sửa sản phẩm -->
                    <form id="confirmUpdateForm" action="{{ route('admin.product.update', $product->id_sanpham) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Tên sản phẩm -->
                        <div class="form-group">
                            <label for="tensanpham">Tên sản phẩm</label>
                            <input type="text" name="tensanpham" id="tensanpham" class="form-control"
                                   value="{{ old('tensanpham', $product->tensanpham) }}" required>
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group">
                            <label for="mota">Mô tả</label>
                            <textarea name="mota" id="mota" class="form-control" rows="3">{{ old('mota', $product->mota) }}</textarea>
                        </div>

                        <!-- Thông tin kỹ thuật -->
                        <div class="form-group">
                            <label for="thongtin_kythuat">Thông tin kỹ thuật</label>
                            <textarea name="thongtin_kythuat" id="thongtin_kythuat" class="form-control" rows="3">{{ old('thongtin_kythuat', $product->thongtin_kythuat) }}</textarea>
                        </div>

                        <!-- Giá -->
                        <div class="form-group">
                            <label for="gia">Giá</label>
                            <input type="number" name="gia" id="gia" class="form-control"
                                   value="{{ old('gia', $product->gia) }}" required>
                        </div>

                        <!-- Giá khuyến mãi -->
                        <div class="form-group">
                            <label for="gia_khuyen_mai">Giá khuyến mãi</label>
                            <input type="number" name="gia_khuyen_mai" id="gia_khuyen_mai" class="form-control"
                                   value="{{ old('gia_khuyen_mai', $product->gia_khuyen_mai) }}">
                        </div>

                        <!-- Đơn vị tính -->
                        <div class="form-group">
                            <label for="donvitinh">Đơn vị tính</label>
                            <input type="text" name="donvitinh" id="donvitinh" class="form-control"
                                   value="{{ old('donvitinh', $product->donvitinh) }}">
                        </div>

                        <!-- Xuất xứ -->
                        <div class="form-group">
                            <label for="xuatxu">Xuất xứ</label>
                            <input type="text" name="xuatxu" id="xuatxu" class="form-control"
                                   value="{{ old('xuatxu', $product->xuatxu) }}">
                        </div>

                        <!-- Số lượng -->
                        <div class="form-group">
                            <label for="soluong">Số lượng</label>
                            <input type="number" name="soluong" id="soluong" class="form-control"
                                   value="{{ old('soluong', $product->soluong) }}" required>
                        </div>

                        <!-- Trạng thái -->
                        <div class="form-group">
                            <label for="trangthai">Trạng thái</label>
                            <select name="trangthai" id="trangthai" class="form-control">
                                <option value="active" {{ old('trangthai', $product->trangthai) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('trangthai', $product->trangthai) == 'inactive' ? 'selected' : '' }}>Ẩn</option>
                            </select>
                        </div>

                        <!-- Danh mục -->
                        <div class="form-group">
                            <label for="id_danhmuc">Danh mục</label>
                            <select name="id_danhmuc" id="id_danhmuc" class="form-control">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id_danhmuc }}"
                                            {{ old('id_danhmuc', $product->id_danhmuc) == $category->id_danhmuc ? 'selected' : '' }}>
                                        {{ $category->tendanhmuc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Thêm hình ảnh mới -->
                        <div class="form-group">
                            <label for="images">Thêm hình ảnh mới</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple>
                        </div>

                        <!-- Nút lưu thay đổi -->
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="{{ route('admin.product.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>

                    <!-- Form xóa ảnh -->
                    @if(isset($product) && $product->images)
                        <div class="mb-3">
                            <label for="current-images" class="form-label">Hình ảnh hiện tại</label>
                            <form id="deleteSelectedImagesForm" method="POST" action="{{ route('admin.product.image.bulk-delete') }}">
                                @csrf
                                @method('DELETE')
                                <div id="current-images" class="d-flex flex-wrap gap-3">
                                    @foreach($product->images as $image)
                                        <div class="image-item text-center" style="width: 120px;">
                                            <img src="{{ asset('storage/' . $image->duongdan) }}" alt="{{ $image->alt }}" class="img-thumbnail" style="width: 100%; height: 100px; object-fit: cover;">
                                            <div class="mt-2">
                                                <input type="checkbox" name="image_ids[]" value="{{ $image->id_hinhanh }}"> Chọn
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-danger mt-3">Xóa các ảnh đã chọn</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #current-images {
        display: flex !important; /* Sử dụng Flexbox để sắp xếp các ảnh theo hàng ngang */
        flex-wrap: wrap !important; /* Cho phép các ảnh tự động xuống dòng nếu không đủ không gian */
        gap: 15px !important; /* Khoảng cách giữa các ảnh */
    }

    .image-item {
        text-align: center !important;
        border: 1px solid #ddd !important;
        border-radius: 5px !important;
        padding: 10px !important;
        background-color: #f9f9f9 !important;
        transition: transform 0.2s ease-in-out !important;
        width: 120px !important; /* Đặt chiều rộng cố định cho mỗi ảnh */
    }

    .image-item:hover {
        transform: scale(1.05) !important; /* Phóng to nhẹ khi hover */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .image-item img {
        border-radius: 5px !important;
        width: 100% !important; /* Đảm bảo ảnh chiếm toàn bộ chiều rộng của khung */
        height: 100px !important; /* Đặt chiều cao cố định cho ảnh */
        object-fit: cover !important; /* Đảm bảo ảnh được cắt gọn và hiển thị đẹp */
    }
</style>

<script>
    // Kiểm tra trước khi xóa ảnh
    document.getElementById('deleteSelectedImagesForm').addEventListener('submit', function (e) {
        const checkboxes = document.querySelectorAll('input[name="image_ids[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một ảnh để xóa.');
        } else if (!confirm('Bạn có chắc chắn muốn xóa các ảnh đã chọn?')) {
            e.preventDefault();
        }
    });
</script>
@endsection