<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryManagerController;
use App\Http\Controllers\Admin\CommentManagerController;
use App\Http\Controllers\Admin\OrderManagerController;
use App\Http\Controllers\Admin\ProductManagerController;
use App\Http\Controllers\Admin\ContactManagerController;
use App\Http\Controllers\Admin\DashboardManagerController;
use App\Http\Controllers\Admin\StatisticalManagerController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
//client
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Client\CheckoutMomoController;
use App\Http\Controllers\Client\FavoriteController;

Route::middleware('check.user')->group(function () {
    //review
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');

    //profile
    Route::resource('profile', ProfileController::class)->names([
        'index'   => 'profile.index',
    ]);
    Route::delete('/favorites/{id}', [ProfileController::class, 'destroy'])->name('favorites.destroy');
    //order
    Route::get('/orders/{id}', [OrderController::class, 'orderDetail'])->name('orders.detail');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::resource('contact', ContactController::class)->names([
        'store' => 'contact.store',
    ]);

    Route::get('/', [HomeController::class, 'index'])->name('users.home');

    //shop
    Route::get('/shop', [ProductController::class, 'index'])->name('users.shop');
    Route::get('/shop/category/{slug}', [ProductController::class, 'showCategory'])->name('shop.category');
    Route::get('/shop/search', [ProductController::class, 'search'])->name('shop.search');
    Route::get('/shop/{slug}', [ProductController::class, 'show'])->name('shop_details');

    //blog
    Route::get('/blogs', [BlogController::class, 'index'])->name('users.blogs');
    Route::get('blogs/{slug}', [BlogController::class, 'show'])->name('users.blog_details');

    //contact
    Route::get('/contact', function () {
        return view('users.pages.contact');
    })->name('users.contact');

    //checkout
    Route::group(['prefix' => 'checkout', 'as' => 'checkout.'], function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('index');
        Route::post('/checkoutCOD', [CheckoutController::class, 'checkoutCOD'])->name('checkoutCOD');

        Route::post('/momo-payment', [CheckoutMomoController::class, 'payWithMomo'])->name('momo.payment');
        Route::get('/momo-return', [CheckoutMomoController::class, 'momoReturn'])->name('momo.return');
        Route::post('/momo-ipn', [CheckoutMomoController::class, 'momoIPN'])->name('momo.ipn');
    });

    //about us
    Route::get('/about-us', function () {
        return view('users.pages.about-us');
    });
    //footer
    Route::get('/footer', [DashboardManagerController::class, 'showFooter']);

    //login, register, forgot password
    Route::middleware('guest')->group(function () {
        Route::match(['get', 'post'], '/login', [LoginController::class, 'login'])->name('login');
        Route::get('forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::post('verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
        Route::post('resend-otp', [ForgotPasswordController::class, 'sendResetLink'])->name('password.resend-otp');
        Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register']); ;
    });

    //logout
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::post('/fire/message', [ChatController::class, 'fireMessage'])->name('sent.message');
    });

    //cart
    Route::group(['prefix' => 'cart', 'middleware' => 'check.guest'], function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('/items', [CartController::class, 'addToCart'])->name('cart.add');
        Route::patch('/items/{id}', [CartController::class, 'updateQuantity'])->name('cart.update');
        Route::delete('/items/{id}', [CartController::class, 'removeItem'])->name('cart.remove');
        Route::delete('/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    })->name('cart.');

    Route::group(['prefix' => 'favorites', 'middleware' => 'check.guest'], function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/toggle/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::delete('/items/{favorite}', [FavoriteController::class, 'remove'])->name('favorites.remove');
        Route::delete('/clear', [FavoriteController::class, 'clear'])->name('favorites.clear');
    })->name('favorites.');

    //Dat: thêm tiếp các route client ở đây
});
// Admin Routes
Route::prefix('admin')->middleware('check.admin')->name('admin.')->group(function () {
    Route::middleware('check.admin.login')->group(function () {
        // Category Routes
        Route::resource('categories', CategoryManagerController::class)->names([
            'index'   => 'category.index',
            'create'  => 'category.create',
            'store'   => 'category.store',
            'show'    => 'category.show',
            'edit'    => 'category.edit',
            'update'  => 'category.update',
            'destroy' => 'category.destroy',
        ]);

        // Order Routes
        Route::resource('orders', OrderManagerController::class)->names([
            'index'   => 'order.index',
            'create'  => 'order.create',
            'store'   => 'order.store',
            'show'    => 'order.show',
            'edit'    => 'order.edit',
            'update'  => 'order.update',
            'destroy' => 'order.destroy',

        ]);

        // Product Routes
        Route::resource('products', ProductManagerController::class)->names([
            'index'   => 'product.index',
            'create'  => 'product.create',
            'store'   => 'product.store',
            'show'    => 'product.show',
            'edit'    => 'product.edit',
            'update'  => 'product.update',
            'destroy' => 'product.destroy',
        ]);

        // Route::delete('products/image/{imageId}/delete', [ProductManagerController::class, 'deleteImage'])->name('product.image.delete');
        Route::delete('product/image/bulk-delete', function () {
            Log::info('Route admin.product.image.bulk-delete được gọi.');
            return app(ProductManagerController::class)->bulkDeleteImages(request());
        })->name('product.image.bulk-delete');

        // Comment Routes
        Route::resource('comments', CommentManagerController::class)->names([
            'index'   => 'comment.index',
            'create'  => 'comment.create',
            'store'   => 'comment.store',
            'show'    => 'comment.show',
            'edit'    => 'comment.edit',
            'update'  => 'comment.update',
            'destroy' => 'comment.destroy',
        ]);

        // Contact Routes
        Route::resource('contacts', ContactManagerController::class)->names([
            'index'   => 'contact.index',
            'create'  => 'contact.create',
            'store'   => 'contact.store',
            'show'    => 'contact.show',
            'edit'    => 'contact.edit',
            'update'  => 'contact.update',
            'destroy' => 'contact.destroy',
        ]);



        Route::patch('products/{product}/hide', [ProductManagerController::class, 'hide'])->name('product.hide');

        // Dashboard Routes
        Route::get('dashboard', [DashboardManagerController::class, 'index'])->name('dashboard.index');
        Route::put('dashboard/website-info', [DashboardManagerController::class, 'updateWebsiteInfo'])
            ->name('dashboard.update-website-info');
        // Statistical Routes
        Route::get('statistics', [StatisticalManagerController::class, 'sales'])->name('statistics.index');
        Route::get('statistics/sales', [StatisticalManagerController::class, 'sales'])->name('statistics.sales');
        Route::get('statistics/products', [StatisticalManagerController::class, 'productSales'])->name('statistics.productSales');
        // index2
        Route::get('statistics2', [StatisticalManagerController::class, 'index'])->name('statistics.index2');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        //Dat: thêm tiếp các route admin ở đây
    });
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
});

//Các route không liên quan
Route::get('/get-sales-data', [StatisticalManagerController::class, 'getSalesData']);

// admin
Route::get('/administrator', function () {
    return view('admin.pages.category'); // giao diện mẫu = Category
})->name('dashboard');

Route::get('/mau', function () {
    return view('admin.pages.category'); // giao diện mẫu = Category
})->name('mau');
