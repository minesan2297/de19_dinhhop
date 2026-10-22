/**
 * main.js – Custom News Theme JS
 * Đề 19: Cổng Thông Tin Tin Tức – CNTT K23C
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Đồng hồ thời gian thực và ngày tháng tiếng Việt
    function updateLiveDateTime() {
        const now = new Date();
        
        // Cập nhật giờ: HH:mm:ss
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeEl = document.getElementById('current-live-time');
        if (timeEl) {
            timeEl.textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Cập nhật ngày tháng tiếng Việt
        const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        const dayName = days[now.getDay()];
        const dateStr = `${dayName}, ${now.getDate()}/${now.getMonth() + 1}/${now.getFullYear()}`;
        const dateEl = document.getElementById('current-vietnamese-date');
        if (dateEl) {
            dateEl.textContent = dateStr;
        }
    }
    updateLiveDateTime();
    setInterval(updateLiveDateTime, 1000);

    // 2. Nút Cuộn Lên Đầu Trang (Back to Top)
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });

        backToTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 3. Smooth Hover Effects & Click Analytics (Simulation for Prometheus logs)
    document.querySelectorAll('.news-card, .sub-post-item, .tech-item-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transition = 'all 0.25s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
});
