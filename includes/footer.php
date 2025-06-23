<?php
?>
    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3><?= t('about_us') ?></h3>
                <p><?= t('about_us_text') ?></p>
            </div>
            <div class="footer-section">
                <h3><?= t('contacts') ?></h3>
                <p>Email: info@gamestore.com</p>
                <p><?= t('phone') ?>: +7 (123) 456-78-90</p>
            </div>
            <div class="footer-section">
                <h3><?= t('quick_links') ?></h3>
                <ul>
                    <li><a href="/"><?= t('home') ?></a></li>
                    <li><a href="/catalog.php"><?= t('catalog') ?></a></li>
                    <li><a href="/about.php"><?= t('about') ?></a></li>
                    <li><a href="/contact.php"><?= t('contact') ?></a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>© 2025 GameStore. <?= t('all_rights_reserved') ?></p>
        </div>
    </footer>
    <script src="/assets/js/script.js"></script>
</body>
</html>