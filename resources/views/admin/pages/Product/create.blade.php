@extends('Admin.Layout.Layout')
@section('title', 'Thêm Sản Phẩm')

@section('content')
            <h1 class="my-4">Thêm Sản Phẩm Mới</h1>
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Tên sản phẩm -->
                <div class="form-group">
                    <label for="tensanpham">Tên sản phẩm</label>
                    <input type="text" id="tensanpham" name="tensanpham" class="form-control" 
                        value="{{ old('tensanpham', $product->tensanpham ?? '') }}" required>
                    @error('tensanpham')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label"></label>
                    <input type="hidden" id="slug" name="slug" >
                </div>

            <div class="mb-3">
                <label for="mota" class="form-label">Mô Tả</label>
                <textarea class="form-control @error('mota') is-invalid @enderror" id="mota" name="mota" required>{{ old('mota') }}</textarea>
                @error('mota')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="thongtin_kythuat" class="form-label">Mô Tả Kỹ Thuật</label>
                <textarea class="form-control @error('thongtin_kythuat') is-invalid @enderror" id="thongtin_kythuat" name="thongtin_kythuat" required>{{ old('thongtin_kythuat') }}</textarea>
                @error('thongtin_kythuat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Chọn danh mục -->
            <div class="mb-3">
                <label for="id_danhmuc" class="form-label">Danh Mục</label>
                <select class="form-control" id="id_danhmuc" name="id_danhmuc" required>
                    <option value="" disabled selected>Chọn danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->tendanhmuc }}">{{ $category->tendanhmuc }}</option>
                    @endforeach
                </select>
            </div>


            <!-- Các trường thông tin khác -->
            <div class="mb-3">
                <label for="gia" class="form-label">Giá</label>
                <input type="number" class="form-control @error('gia') is-invalid @enderror" id="gia" name="gia" value="{{ old('gia') }}" required>
                @error('gia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="gia_khuyen_mai" class="form-label">Giá Khuyến Mãi</label>
                <input type="number" class="form-control @error('gia_khuyen_mai') is-invalid @enderror" id="gia_khuyen_mai" name="gia_khuyen_mai" value="{{ old('gia_khuyen_mai') }}">
                @error('gia_khuyen_mai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="donvitinh" class="form-label">Đơn Vị Tính</label>
                <input type="text" class="form-control @error('donvitinh') is-invalid @enderror" id="donvitinh" name="donvitinh" value="{{ old('donvitinh') }}" required>
                @error('donvitinh')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="xuatxu" class="form-label">Xuất Xứ</label>
                <input type="text" class="form-control @error('xuatxu') is-invalid @enderror" id="xuatxu" name="xuatxu" value="{{ old('xuatxu') }}" required>
                @error('xuatxu')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="soluong" class="form-label">Số Lượng</label>
                <input type="number" class="form-control @error('soluong') is-invalid @enderror" id="soluong" name="soluong" value="{{ old('soluong') }}" required>
                @error('soluong')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="trangthai" class="form-label">Trạng Thái</label>
                <select class="form-control @error('trangthai') is-invalid @enderror" id="trangthai" name="trangthai" required>
                    <option value="active" {{ old('trangthai') === 'active' ? 'selected' : '' }}>Hoạt động</option>
    <option value="inactive" {{ old('trangthai') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
                @error('trangthai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                            <label for="images">Thêm hình ảnh mới</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple>
                        </div>
            <div class="mb-3">
                <div id="preview-container" class="d-flex flex-wrap mt-3"></div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const imagesInput = document.getElementById('images');
                    const previewContainer = document.getElementById('preview-container');

                    if (!previewContainer) {
                        console.error('Phần tử #preview-container không tồn tại.');
                        return;
                    }

                    imagesInput.addEventListener('change', function (event) {
                        const files = event.target.files;

                        // Xóa hình ảnh xem trước hiện tại
                        previewContainer.innerHTML = '';

                        // Duyệt qua danh sách file được chọn
                        Array.from(files).forEach((file, index) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();

                                // Đọc file và hiển thị hình ảnh
                                reader.onload = function (e) {
                                    const div = document.createElement('div');
                                    div.className = 'image-item position-relative me-3 mb-3';
                                    div.style.width = '100px';
                                    div.style.height = '100px';

                                    div.innerHTML = `
                                        <img src="${e.target.result}" alt="Hình ảnh xem trước" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px;" data-index="${index}">
                                            X
                                        </button>
                                    `;

                                    previewContainer.appendChild(div);
                                };

                                reader.readAsDataURL(file);
                            }
                        });

                        // Gán sự kiện xóa hình ảnh
                        setTimeout(() => attachDeleteHandlers(files), 100);
                    });
                });

                function attachDeleteHandlers(files) {
                    document.querySelectorAll('#preview-container button').forEach((btn) => {
                        btn.addEventListener('click', function () {
                            const index = parseInt(this.getAttribute('data-index'));
                            const dt = new DataTransfer();

                            // Lấy các file trừ file bị xóa
                            Array.from(files).forEach((file, i) => {
                                if (i !== index) {
                                    dt.items.add(file);
                                }
                            });

                            // Cập nhật danh sách file
                            document.getElementById('images').files = dt.files;

                            // Xóa hình ảnh xem trước
                            this.closest('.image-item').remove();
                        });
                    });
                }
            </script>


            <button type="submit" class="btn btn-primary">Lưu Sản Phẩm</button>
        </form>
    </div>
@endsection
