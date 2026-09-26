/**
 * main.js – Custom News Theme
 * Đề 19: Website Tin tức
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Load More Posts ──
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async () => {
            const page    = parseInt(loadMoreBtn.dataset.page) + 1;
            const maxPage = parseInt(loadMoreBtn.dataset.max);
            const grid    = document.getElementById('posts-grid');

            loadMoreBtn.textContent = 'Đang tải...';
            loadMoreBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('action', 'load_more_posts');
                formData.append('page', page);
                formData.append('nonce', customNews.nonce);

                const res  = await fetch(customNews.ajaxUrl, { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    grid.insertAdjacentHTML('beforeend', data.data.html);
                    loadMoreBtn.dataset.page = page;

                    if (!data.data.has_more || page >= maxPage) {
                        loadMoreBtn.remove();
                    } else {
                        loadMoreBtn.textContent = 'Xem thêm tin tức';
                        loadMoreBtn.disabled = false;
                    }
                } else {
                    loadMoreBtn.remove();
                }
            } catch (err) {
                console.error('Load more error:', err);
                loadMoreBtn.textContent = 'Thử lại';
                loadMoreBtn.disabled = false;
            }
        });
    }

    // ── Sticky Header Shadow ──
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.style.boxShadow = window.scrollY > 10
                ? '0 4px 20px rgba(0,0,0,.3)'
                : '0 2px 8px rgba(0,0,0,.3)';
        }, { passive: true });
    }

    // ── Lazy Image Fade-in ──
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    imgObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            img.style.opacity = '0';
            img.style.transition = 'opacity .3s';
            imgObserver.observe(img);
        });
    }

    // ── Reading Progress Bar ──
    if (document.querySelector('.single-post')) {
        const bar = document.createElement('div');
        bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--primary,#1a73e8);z-index:9999;transition:width .1s;width:0';
        document.body.prepend(bar);

        window.addEventListener('scroll', () => {
            const docHeight   = document.documentElement.scrollHeight - window.innerHeight;
            const scrolled    = (window.scrollY / docHeight) * 100;
            bar.style.width   = Math.min(scrolled, 100) + '%';
        }, { passive: true });
    }

    // ── Back to Top Button ──
    const backTop = document.createElement('button');
    backTop.innerHTML = '↑';
    backTop.title = 'Về đầu trang';
    backTop.style.cssText = `
        position:fixed;bottom:24px;right:24px;width:44px;height:44px;
        border-radius:50%;background:var(--primary,#1a73e8);color:#fff;
        border:none;font-size:1.2rem;cursor:pointer;
        box-shadow:0 4px 12px rgba(0,0,0,.2);
        opacity:0;transition:opacity .3s;z-index:999;
    `;
    document.body.appendChild(backTop);

    window.addEventListener('scroll', () => {
        backTop.style.opacity = window.scrollY > 300 ? '1' : '0';
    }, { passive: true });

    backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // ── Auto Dark Mode (theo system preference) ──
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // Uncomment nếu muốn dark mode
        // document.documentElement.setAttribute('data-theme', 'dark');
    }
});
