    </main>

    <footer style="background: #0c0c0c; color: white; padding: 40px 0; margin-top: 50px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px;">
                <div>
                    <h3 style="color: #cc0000; margin-bottom: 20px;">CNN</h3>
                    <p>CNN provides the latest news and videos from around the world.</p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px;">Categories</h4>
                    <ul style="list-style: none;">
                        <li><a href="category.php?cat=politics" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Politics</a></li>
                        <li><a href="category.php?cat=business" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Business</a></li>
                        <li><a href="category.php?cat=technology" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Technology</a></li>
                        <li><a href="category.php?cat=health" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Health</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px;">Quick Links</h4>
                    <ul style="list-style: none;">
                        <li><a href="index.php" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Home</a></li>
                        <li><a href="dashboard.php" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Dashboard</a></li>
                        <li><a href="create_post.php" style="color: white; text-decoration: none; display: block; padding: 5px 0;">Create Post</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px;">Contact</h4>
                    <p>Email: contact@cnnclone.com</p>
                    <p>Phone: (123) 456-7890</p>
                </div>
            </div>
            
            <div style="border-top: 1px solid #444; padding-top: 20px; text-align: center; font-size: 14px;">
                <p>&copy; <?php echo date('Y'); ?> CNN Clone. All rights reserved.</p>
                <p>This is a demonstration website for educational purposes.</p>
            </div>
        </div>
    </footer>

    <script>
        // Breaking News Ticker
        const breakingNews = [
            "Global Summit Addresses Climate Change Concerns",
            "Stock Markets Show Volatility Amid Economic Reports",
            "Tech Giants Announce AI Partnership",
            "Major Sports Event Final This Weekend",
            "Healthcare Breakthrough Announced"
        ];
        
        let currentNewsIndex = 0;
        const newsElement = document.getElementById('breakingNewsText');
        
        function updateBreakingNews() {
            newsElement.textContent = breakingNews[currentNewsIndex];
            currentNewsIndex = (currentNewsIndex + 1) % breakingNews.length;
        }
        
        // Change news every 10 seconds
        updateBreakingNews();
        setInterval(updateBreakingNews, 10000);
        
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.createElement('button');
            mobileMenuBtn.innerHTML = '☰ Menu';
            mobileMenuBtn.style.cssText = 'display: none; padding: 10px; background: #cc0000; color: white; border: none; border-radius: 3px; margin: 10px auto;';
            
            const navPrimary = document.querySelector('.nav-primary');
            const header = document.querySelector('.main-header .container');
            
            if (window.innerWidth <= 768) {
                header.insertBefore(mobileMenuBtn, navPrimary);
                mobileMenuBtn.style.display = 'block';
                navPrimary.style.display = 'none';
                
                mobileMenuBtn.addEventListener('click', function() {
                    navPrimary.style.display = navPrimary.style.display === 'flex' ? 'none' : 'flex';
                });
            }
            
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768) {
                    mobileMenuBtn.style.display = 'block';
                    navPrimary.style.display = 'none';
                } else {
                    mobileMenuBtn.style.display = 'none';
                    navPrimary.style.display = 'flex';
                }
            });
        });
    </script>
</body>
</html>
