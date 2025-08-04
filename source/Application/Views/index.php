<?= $view->render('includes/header'); ?>

<main>
    <div class="container">
        <div class="hero">
            <h1>Welcome to Hoist PHP Framework</h1>
            <p class="lead">A lightweight, powerful PHP MVC framework designed for rapid development.</p>

            <?php if (isset($viewCount)): ?>
                <div class="cache-demo">
                    <h3>Cache Demo</h3>
                    <p>This page has been viewed <strong><?= $viewCount ?></strong> times (cached for 1 hour)</p>
                    <a href="/index/clear-cache" class="btn btn-sm">Clear Cache</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="features">
            <div class="feature">
                <h3>🚀 Zero Configuration</h3>
                <p>Start building immediately with smart defaults and automatic service discovery.</p>
            </div>

            <div class="feature">
                <h3>💾 FileDatabase System</h3>
                <p>Build applications without database setup using our JSON-based storage.</p>
            </div>

            <div class="feature">
                <h3>⚡ High-Performance Caching</h3>
                <p>Multi-tier caching with Redis, Memcached, and file-based storage options.</p>
            </div>

            <div class="feature">
                <h3>🔐 Built-in Authentication</h3>
                <p>Secure user management with modern password hashing and session handling.</p>
            </div>
        </div>
    </div>
</main>

<style>
    .hero {
        text-align: center;
        padding: 2rem 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 2rem;
    }

    .lead {
        font-size: 1.2rem;
        color: #666;
        margin: 1rem 0;
    }

    .cache-demo {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem auto;
        max-width: 400px;
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .feature {
        padding: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-align: center;
    }

    .feature h3 {
        margin-bottom: 1rem;
        color: #333;
    }

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .btn:hover {
        background: #0056b3;
    }
</style>

<?= $view->render('includes/footer'); ?>

