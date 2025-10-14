<?php
require_once __DIR__ . '/core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo s_get($pdo, 'site_name', 'Automated Report Card System'); ?></title>
    <meta name="description" content="<?php echo s_get($pdo, 'seo_description'); ?>">
    <meta name="keywords" content="report card, school system, education, php, automation">

    <?php if (!empty(s_get($pdo, 'site_favicon_url'))): ?>
    <link rel="icon" type="image/x-icon" href="/<?php echo s_get($pdo, 'site_favicon_url'); ?>">
    <?php endif; ?>

    <link rel="manifest" href="/manifest.php">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero {
            background-size: cover;
            color: white;
            padding: 8rem 0;
        }
        .section-icon { font-size: 3rem; color: #0d6efd; }
        .testimonial-card { border: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .pricing-card { border: 1px solid #ddd; transition: all 0.3s ease; }
        .pricing-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <?php if (!empty(s_get($pdo, 'site_logo_url'))): ?>
                    <img src="/<?php echo s_get($pdo, 'site_logo_url'); ?>" alt="<?php echo s_get($pdo, 'site_name'); ?> Logo" style="height: 30px;">
                <?php else: ?>
                    <?php echo s_get($pdo, 'site_name', 'ARS'); ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="main-nav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex">
                    <a href="/views/teacher/login.php" class="btn btn-outline-secondary me-2">Teacher Login</a>
                    <a href="/views/school_admin/login.php" class="btn btn-primary">School Admin Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero text-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo htmlspecialchars(s_get($pdo, 'hero_image_url', 'https://via.placeholder.com/1920x1080/8f8f8f/ffffff?text=School+Background')); ?>');">
        <div class="container">
            <h1 class="display-3 fw-bold"><?php echo c_get($pdo, 'hero_title'); ?></h1>
            <p class="lead col-lg-8 mx-auto"><?php echo c_get($pdo, 'hero_subtitle'); ?></p>
            <a href="/register.php" class="btn btn-primary btn-lg mt-3">Start Free Trial (20 Students)</a>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container text-center">
            <h2 class="mb-5">Why Choose ARS?</h2>
            <div class="row">
                <div class="col-md-4 mb-4"><i class="bi bi-tools section-icon mb-3"></i><h4><?php echo c_get($pdo, 'feature1_title'); ?></h4><p><?php echo c_get($pdo, 'feature1_text'); ?></p></div>
                <div class="col-md-4 mb-4"><i class="bi bi-cloud-arrow-up-fill section-icon mb-3"></i><h4><?php echo c_get($pdo, 'feature2_title'); ?></h4><p><?php echo c_get($pdo, 'feature2_text'); ?></p></div>
                <div class="col-md-4 mb-4"><i class="bi bi-shield-check section-icon mb-3"></i><h4><?php echo c_get($pdo, 'feature3_title'); ?></h4><p><?php echo c_get($pdo, 'feature3_text'); ?></p></div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Trusted by Leading Institutions</h2>
            <div class="row">
                <div class="col-md-4 mb-4"><div class="card testimonial-card p-4"><p class="mb-0">"<?php echo c_get($pdo, 'testimonial1_text'); ?>"</p><footer class="blockquote-footer mt-3"><?php echo c_get($pdo, 'testimonial1_author'); ?></footer></div></div>
                <div class="col-md-4 mb-4"><div class="card testimonial-card p-4"><p class="mb-0">"<?php echo c_get($pdo, 'testimonial2_text'); ?>"</p><footer class="blockquote-footer mt-3"><?php echo c_get($pdo, 'testimonial2_author'); ?></footer></div></div>
                <div class="col-md-4 mb-4"><div class="card testimonial-card p-4"><p class="mb-0">"<?php echo c_get($pdo, 'testimonial3_text'); ?>"</p><footer class="blockquote-footer mt-3"><?php echo c_get($pdo, 'testimonial3_author'); ?></footer></div></div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Simple, Transparent Pricing</h2>
            <p class="text-center text-muted col-lg-8 mx-auto">Choose the plan that's right for your institution.</p>
            <?php
            try {
                $stmt_packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC");
                $packages = $stmt_packages->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $packages = [];
            }
            ?>
            <?php if (!empty($packages)): ?>
            <div class="row justify-content-center mt-4">
                <?php foreach ($packages as $package): ?>
                <div class="col-lg-5 mb-4">
                    <div class="card pricing-card text-center h-100">
                        <div class="card-header bg-<?php echo strtolower($package['name']) === 'premium' ? 'success' : 'primary'; ?> text-white">
                            <h4><?php echo htmlspecialchars($package['name']); ?></h4>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h1 class="card-title">
                                <?php if ((float)$package['price'] > 0): ?>
                                    <?php echo get_currency_symbol($pdo); ?><?php echo htmlspecialchars(number_format($package['price'], 2)); ?>
                                    <small class="text-muted">/ student</small>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </h1>
                            <p class="text-muted">
                                <?php if ((int)$package['student_limit'] > 0): ?>
                                    Up to <?php echo htmlspecialchars($package['student_limit']); ?> students
                                <?php else: ?>
                                    Unlimited students
                                <?php endif; ?>
                            </p>
                            <ul class="list-unstyled mt-3 mb-4 text-start">
                                <?php
                                $features = json_decode($package['features'], true);
                                if (is_array($features)) {
                                    foreach ($features as $feature) {
                                        $is_negative = str_contains(strtolower($feature), 'cannot') || str_contains(strtolower($feature), 'no');
                                        $icon = $is_negative ? 'bi-x-circle text-danger' : 'bi-check-circle text-success';
                                        echo '<li class="mb-2"><i class="bi ' . $icon . ' me-2"></i>' . htmlspecialchars($feature) . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                            <div class="mt-auto">
                               <a href="/register.php" class="btn btn-<?php echo strtolower($package['name']) === 'premium' ? 'success' : 'primary'; ?> btn-lg w-100">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Have Questions?</h2>
                <p class="lead">Our team is here to help. Reach out to us anytime.</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5 mb-4">
                    <div class="card h-100 shadow-sm" style="border: none;">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-envelope-fill section-icon mb-3"></i>
                            <h4>Email & Phone</h4>
                            <p class="h5 mt-4"><a href="mailto:<?php echo c_get($pdo, 'contact_email'); ?>" class="text-decoration-none"><?php echo c_get($pdo, 'contact_email'); ?></a></p>
                            <p class="h5"><a href="tel:<?php echo c_get($pdo, 'contact_phone'); ?>" class="text-decoration-none"><?php echo c_get($pdo, 'contact_phone'); ?></a></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mb-4">
                    <div class="card h-100 shadow-sm" style="border: none;">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-whatsapp section-icon mb-3"></i>
                            <h4>WhatsApp</h4>
                            <p>Send us a message for a quick response.</p>
                            <?php
                            $super_admin_phone = c_get($pdo, 'contact_phone');
                            ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $super_admin_phone); ?>" class="btn btn-success btn-lg mt-3" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i> Chat Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white text-center">
        <div class="container"><p class="mb-0">Copyright &copy; <?php echo date('Y'); ?> <?php echo s_get($pdo, 'site_name'); ?>. All Rights Reserved.</p><small class="text-muted">Proudly built for educators everywhere.</small></div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>