<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController
{
    public function showRegistrationForm()
    {
        return view('users.pages.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'hoten' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:nguoi_dung',
            'matkhau' => 'required|string|min:6|confirmed',
            'sodienthoai' => 'nullable|string|max:20',
            'diachi' => 'nullable|string',
            'ngaysinh' => 'nullable|date',
            'gioitinh' => 'nullable|in:male,female',
        ], [
            'hoten.required' => 'Vui lòng nhập họ tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'matkhau.required' => 'Vui lòng nhập mật khẩu',
            'matkhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'matkhau.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        // Tạo người dùng mới
        $user = User::create([
            'hoten' => $validated['hoten'],
            'email' => $validated['email'],
            'matkhau' => Hash::make($validated['matkhau']),
            'sodienthoai' => $validated['sodienthoai'] ?? null,
            'diachi' => $validated['diachi'] ?? null,
            'ngaysinh' => $validated['ngaysinh'] ?? null,
            'gioitinh' => $validated['gioitinh'] ?? null,
            'loai_nguoidung' => 'user',
            'trangthai' => 'active',
            'email_verified_at' => now(), // Đánh dấu email đã được xác minh
        ]);

        // Chuyển hướng sau khi đăng ký thành công
        return redirect()->route('login')
            ->with('status', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }
}

