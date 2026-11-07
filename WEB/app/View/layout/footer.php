</main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="footer-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>RealEstate</span>
                    </a>
                    <p class="footer-tagline">Your trusted partner in finding the perfect property.</p>
                </div>
                
                <nav class="footer-links" aria-label="Footer navigation">
                    <div class="footer-column">
                        <h4>Buy & Rent</h4>
                        <ul>
                            <li><a href="/properties?price_type=sale">Properties for Sale</a></li>
                            <li><a href="/properties?price_type=rent">Properties for Rent</a></li>
                            <li><a href="/properties?category_id=1">Apartments</a></li>
                            <li><a href="/properties?category_id=2">Houses</a></li>
                            <li><a href="/properties?category_id=3">Commercial</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Support</h4>
                        <ul>
                            <li><a href="/contact">Contact Us</a></li>
                            <li><a href="/faq">FAQ</a></li>
                            <li><a href="/terms">Terms of Service</a></li>
                            <li><a href="/privacy">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Company</h4>
                        <ul>
                            <li><a href="/about">About Us</a></li>
                            <li><a href="/careers">Careers</a></li>
                            <li><a href="/blog">Blog</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Real Estate Portal. All rights reserved.</p>
                <p class="footer-credit">CIS324 Web Engineering Project - Student: 241-16-034, Section: 19_A</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>