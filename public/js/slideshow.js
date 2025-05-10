document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero__item');
    const dots = document.querySelectorAll('.hero__nav-dot');
    
    // Kiểm tra xem có slides và dots không
    if (slides.length === 0 || dots.length === 0) {
        console.error('Không tìm thấy slides hoặc dots');
        return;
    }

    let currentSlide = 0;
    let slideInterval;
    const slideDuration = 5000; // 5 giây

    function showSlide(index) {
        // Kiểm tra index hợp lệ
        if (index < 0 || index >= slides.length) {
            console.error('Index không hợp lệ:', index);
            return;
        }

        // Xóa class active an toàn
        slides.forEach(slide => {
            if (slide.classList) slide.classList.remove('active');
        });
        dots.forEach(dot => {
            if (dot.classList) dot.classList.remove('active');
        });

        // Thêm class active với kiểm tra an toàn
        if (slides[index] && slides[index].classList) {
            slides[index].classList.add('active');
        }
        if (dots[index] && dots[index].classList) {
            dots[index].classList.add('active');
        }

        currentSlide = index;
    }

    function nextSlide() {
        const newIndex = (currentSlide + 1) % slides.length;
        showSlide(newIndex);
    }

    function startSlideshow() {
        // Dừng interval hiện tại nếu có
        if (slideInterval) {
            clearInterval(slideInterval);
        }
        showSlide(0); // Hiển thị slide đầu tiên
        slideInterval = setInterval(nextSlide, slideDuration);
    }

    // Thêm sự kiện click cho dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetSlideshow();
        });
    });

    function resetSlideshow() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, slideDuration);
    }

    // Khởi động slideshow
    startSlideshow();

    // Tạm dừng khi hover (tuỳ chọn)
    const slideshowContainer = document.querySelector('.hero__slideshow');
    if (slideshowContainer) {
        slideshowContainer.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        slideshowContainer.addEventListener('mouseleave', () => {
            resetSlideshow();
        });
    }
});