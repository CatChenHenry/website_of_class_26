<?php
/** @var array<int, array<string, mixed>> $recentActivities */
require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 首页</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft Yahei", sans-serif;
        }

        body {
            background: #f5f5f5;
        }

        .carousel {
            position: relative;
            width: 50%;
            height: 500px;
            overflow: hidden;
            margin: 60px auto;
        }

        .carousel-images {
            display: flex;
            height: 100%;
            transition: transform 0.6s ease;
        }

        .carousel-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.3);
            color: white;
            border: none;
            font-size: 18px;
            width: 36px;
            height: 36px;
            line-height: 1;
            text-align: center;
            padding: 0;
            cursor: pointer;
            z-index: 10;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .carousel-btn:hover {
            background: rgba(0, 0, 0, 0.5);
        }

        .prev {
            left: 20px;
        }

        .next {
            right: 20px;
        }

        .carousel-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.2s;
        }

        .dot.active {
            background: white;
        }

        /* 最近活动区 */
        .recent-section {
            max-width: 1100px;
            margin: 0 auto;
            padding: 36px 24px 48px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .section-header h2 {
            font-size: 22px;
            color: #333;
            position: relative;
            padding-left: 14px;
        }

        .section-header h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 4px;
            bottom: 4px;
            width: 4px;
            background: #007bff;
            border-radius: 2px;
        }

        .view-all {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .view-all:hover {
            opacity: 0.75;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .activity-card {
            background: #fff;
            border-radius: 10px;
            padding: 22px 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .activity-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .activity-card .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #222;
            margin-bottom: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .activity-card .card-time {
            font-size: 13px;
            color: #888;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .activity-card .card-time svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .no-activities {
            text-align: center;
            color: #aaa;
            padding: 40px 0;
            font-size: 15px;
        }

        @media (max-width: 768px) {
            .carousel {
                width: 100%;
                height: 240px;
                margin: 40px auto;
            }

            .carousel-btn {
                width: 30px;
                height: 30px;
                font-size: 14px;
            }

            .prev { left: 10px; }
            .next { right: 10px; }

            .recent-section {
                padding: 24px 16px 36px;
            }

            .section-header h2 {
                font-size: 18px;
            }

            .activity-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .activity-card {
                padding: 16px 18px;
            }
        }

        @media (max-width: 480px) {
            .carousel {
                height: 180px;
                margin: 30px auto;
            }

            .carousel-btn {
                width: 26px;
                height: 26px;
                font-size: 12px;
            }

            .section-header h2 {
                font-size: 16px;
            }

            .view-all {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

    <!-- 走马灯 -->
    <div class="carousel" id="carousel">
        <div class="carousel-images">
            <img src="/static/banners/home.jpg" alt="轮播1">
            <img src="/static/banners/image_class.jpg" alt="轮播2">
        </div>

        <button class="carousel-btn prev" onclick="prevSlide()">&#10094;</button>
        <button class="carousel-btn next" onclick="nextSlide()">&#10095;</button>

        <div class="carousel-dots">
            <span class="dot active" onclick="goToSlide(0)"></span>
            <span class="dot" onclick="goToSlide(1)"></span>
        </div>
    </div>

    <!-- 最近活动 -->
    <?php if (isset($_SESSION['username'])): ?>
    <div class="recent-section">
        <div class="section-header">
            <h2>最近活动</h2>
            <a href="/activity/index" class="view-all">查看全部 &rarr;</a>
        </div>

        <?php if (!empty($recentActivities)): ?>
        <div class="activity-grid">
            <?php foreach ($recentActivities as $act): ?>
            <a href="/activity/show?id=<?php echo (int) $act['id']; ?>" class="activity-card">
                <div class="card-title"><?php echo htmlspecialchars($act['name']); ?></div>
                <div class="card-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo htmlspecialchars($act['activity_time']); ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-activities">暂无活动</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-images img');
        const totalSlides = slides.length;
        const images = document.querySelector('.carousel-images');
        const dots = document.querySelectorAll('.dot');

        function updateSlide() {
            images.style.transform = `translateX(-${currentSlide * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlide();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateSlide();
        }

        // 自动轮播
        // let autoPlay = setInterval(nextSlide, 4000);
        // document.getElementById('carousel').addEventListener('mouseenter', () => clearInterval(autoPlay));
        // document.getElementById('carousel').addEventListener('mouseleave', () => { autoPlay = setInterval(nextSlide, 4000); });
    </script>

</body>

</html>
