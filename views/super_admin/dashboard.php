<?php
$page_title = "Dashboard";
include 'partials/header.php'; // Includes auth_check.php
require_once __DIR__ . '/../../config/config.php'; // Ensure access to $pdo

// --- Fetch dynamic data for dashboard widgets ---
try {
    // Platform-wide counts
    $total_schools_count = $pdo->query("SELECT COUNT(id) FROM schools")->fetchColumn();
    $total_students_count = $pdo->query("SELECT COUNT(id) FROM students WHERE status='active'")->fetchColumn();
    $total_teachers_count = $pdo->query("SELECT COUNT(id) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();
    $pending_regs_count = $pdo->query("SELECT COUNT(id) FROM schools WHERE status = 'pending_payment'")->fetchColumn();

    // Recent Sign-ups
    $recent_schools_stmt = $pdo->query("SELECT name, created_at FROM schools ORDER BY created_at DESC LIMIT 5");
    $recent_schools = $recent_schools_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // On error, set defaults and show an error message
    $total_schools_count = 0; $total_students_count = 0; $total_teachers_count = 0; $pending_regs_count = 0;
    $recent_schools = [];
    echo '<div class="alert alert-danger">Could not fetch dashboard data. Error: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Platform Dashboard</h1>
    </div>

    <!-- Key Metrics & Analytics -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Schools</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_schools_count); ?></div></div>
                <div class="col-auto"><i class="bi bi-building fs-2 text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Students</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_students_count); ?></div></div>
                <div class="col-auto"><i class="bi bi-people-fill fs-2 text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Teachers</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_teachers_count); ?></div></div>
                <div class="col-auto"><i class="bi bi-person-video3 fs-2 text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pending Registrations</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($pending_regs_count); ?></div></div>
                <div class="col-auto"><i class="bi bi-person-check-fill fs-2 text-gray-300"></i></div>
            </div></div></div>
        </div>
    </div>

    <!-- System Health & Management -->
    <div class="row">
        <div class="col-lg-7">
            <!-- Recent Sign-ups -->
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Recent School Sign-ups</h6></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($recent_schools)): ?>
                            <li class="list-group-item">No new schools have registered recently.</li>
                        <?php else: ?>
                            <?php foreach ($recent_schools as $school): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($school['name']); ?>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($school['created_at'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <!-- Platform-wide Announcements -->
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Platform-wide Announcements</h6></div>
                <div class="card-body">
                    <p class="text-muted small">This tool sends a broadcast message to all School Administrators dashboards.</p>
                    <form action="/controllers/announcement_controller.php" method="POST">
                        <input type="hidden" name="action" value="create_platform_announcement">
                        <div class="mb-3"><label for="announcement_title" class="form-label">Title</label><input type="text" name="title" id="announcement_title" class="form-control" required></div>
                        <div class="mb-3"><label for="announcement_content" class="form-label">Message</label><textarea class="form-control" name="content" id="announcement_content" rows="3" required></textarea></div>
                        <button type="submit" class="btn btn-primary w-100">Post Announcement</button>
                    </form>
                    <a href="/views/super_admin/announcements.php" class="btn btn-sm btn-outline-secondary w-100 mt-2">Manage All Announcements</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'partials/footer.php'; ?>