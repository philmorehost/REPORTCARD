<?php
$page_title = "Publish Reports";
include 'partials/header.php'; // Includes auth, db, etc.

$school_id = $_SESSION['school_id'];
$selected_period = $_GET['period'] ?? '';

// --- Fetch necessary data ---
try {
    // Fetch school's package info & sms credits
    $stmt_school_details = $pdo->prepare(
        "SELECT p.name as package_name, s.sms_credits
         FROM schools s
         LEFT JOIN packages p ON s.package_id = p.id
         WHERE s.id = :school_id"
    );
    $stmt_school_details->execute(['school_id' => $school_id]);
    $school_details = $stmt_school_details->fetch(PDO::FETCH_ASSOC);
    $is_freemium = ($school_details && strtolower($school_details['package_name']) === 'freemium');
    $sms_credits = $school_details['sms_credits'] ?? 0;

    // Fetch academic periods
    $stmt_periods = $pdo->prepare("SELECT DISTINCT academic_period FROM classes WHERE school_id = :school_id ORDER BY academic_period DESC");
    $stmt_periods->execute(['school_id' => $school_id]);
    $academic_periods = $stmt_periods->fetchAll(PDO::FETCH_COLUMN);
    if (!$selected_period && !empty($academic_periods)) { $selected_period = $academic_periods[0]; }

    // Handle search and filtering
    $search_term = $_GET['search'] ?? '';
    $class_filter = $_GET['class_filter'] ?? '';
    $publish_status_filter = $_GET['publish_status'] ?? '';

    $sql = "SELECT s.id, s.full_name, s.class, s.parent_email, s.parent_phone_number, pr.unique_hash
            FROM students s
            LEFT JOIN published_reports pr ON s.id = pr.student_id AND pr.academic_period = :period
            WHERE s.school_id = :school_id AND s.status = 'active'";

    $params = [':school_id' => $school_id, ':period' => $selected_period];

    if (!empty($search_term)) {
        $sql .= " AND s.full_name LIKE :search";
        $params[':search'] = '%' . $search_term . '%';
    }
    if (!empty($class_filter)) {
        $sql .= " AND s.class = :class";
        $params[':class'] = $class_filter;
    }
    if ($publish_status_filter === 'published') {
        $sql .= " AND pr.id IS NOT NULL";
    } elseif ($publish_status_filter === 'not_published') {
        $sql .= " AND pr.id IS NULL";
    }

    $sql .= " ORDER BY s.full_name ASC";

    $stmt_students = $pdo->prepare($sql);
    $stmt_students->execute($params);
    $students_with_status = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

    // Fetch unique classes for the filter dropdown
    $stmt_classes = $pdo->prepare("SELECT DISTINCT class FROM students WHERE school_id = :school_id ORDER BY class ASC");
    $stmt_classes->execute(['school_id' => $school_id]);
    $classes = $stmt_classes->fetchAll(PDO::FETCH_COLUMN);

    // Fetch email templates for the notification modal
    $stmt_templates = $pdo->prepare("SELECT id, name FROM email_templates WHERE school_id = :school_id");
    $stmt_templates->execute(['school_id' => $school_id]);
    $email_templates = $stmt_templates->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $academic_periods = []; $students_with_status = []; $email_templates = []; $is_freemium = false; $sms_credits = 0;
    echo '<div class="alert alert-danger">Could not fetch data. Error: ' . $e->getMessage() . '</div>';
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message']; $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Publish Student Reports</h1>
        <div>
            <span class="me-3"><strong>SMS Credits:</strong> <?php echo number_format($sms_credits); ?></span>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkPublishModal" <?php if ($is_freemium) echo 'disabled'; ?>>
                <i class="bi bi-cloud-upload-fill me-2"></i>Publish All Reports
            </button>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php
            if ($message_type === 'success') echo 'success';
            elseif ($message_type === 'warning') echo 'warning';
            else echo 'danger';
        ?> alert-dismissible fade show" role="alert">
            <?php echo $message; // The message is now pre-sanitized in the controller ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by Student Name..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3">
                    <select name="class_filter" class="form-select">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" <?php if ($class_filter == $class) echo 'selected'; ?>><?php echo htmlspecialchars($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="publish_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="published" <?php if ($publish_status_filter == 'published') echo 'selected'; ?>>Published</option>
                        <option value="not_published" <?php if ($publish_status_filter == 'not_published') echo 'selected'; ?>>Not Published</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/views/school_admin/publish_reports.php?period=<?php echo urlencode($selected_period); ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Student List for <?php echo htmlspecialchars($selected_period ?: 'N/A'); ?> (<?php echo count($students_with_status); ?> found)</h6>
            <?php if (!empty($academic_periods)): ?>
            <div class="dropdown"><button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Change Period</button>
                <ul class="dropdown-menu"><?php foreach ($academic_periods as $period): ?><li><a class="dropdown-item" href="?period=<?php echo urlencode($period); ?>"><?php echo $period; ?></a></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Student Name</th><th>Parent Contact</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($students_with_status as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td>
                                <?php if (!empty($student['parent_email'])): ?><i class="bi bi-envelope-fill text-muted"></i> <?php echo htmlspecialchars($student['parent_email']); ?><br><?php endif; ?>
                                <?php if (!empty($student['parent_phone_number'])): ?><i class="bi bi-telephone-fill text-success"></i> <?php echo htmlspecialchars($student['parent_phone_number']); ?><?php endif; ?>
                            </td>
                            <td><?php if ($student['unique_hash']): ?><span class="badge bg-success">Published</span><?php else: ?><span class="badge bg-warning">Not Published</span><?php endif; ?></td>
                            <td>
                                <?php if ($is_freemium): ?>
                                    <a href="/controllers/publish_controller.php?action=view_report&student_id=<?php echo $student['id']; ?>&period=<?php echo urlencode($selected_period); ?>" class="btn btn-sm btn-primary" target="_blank"><i class="bi bi-eye"></i> View Report</a>
                                <?php else: ?>
                                    <?php if ($student['unique_hash']): ?>
                                        <button class="btn btn-sm btn-info" onclick="viewLink('<?php echo $student['unique_hash']; ?>')"><i class="bi bi-eye"></i> View Link</button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-<?php echo $student['unique_hash'] ? 'secondary' : 'primary'; ?>" onclick='openPublishModal(<?php echo json_encode($student); ?>)' title="Publish">
                                        <i class="bi bi-cloud-upload"></i> <?php echo $student['unique_hash'] ? 'Re-publish' : 'Publish'; ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Publish Single Modal -->
<div class="modal fade" id="publishModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="publishForm" action="/controllers/publish_controller.php" method="GET">
        <div class="modal-header"><h5 class="modal-title" id="publishModalTitle">Publish Report Card</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="publish_single">
            <input type="hidden" name="student_id" id="publishStudentId">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($selected_period); ?>">
            <p>You are about to publish the report card for <strong id="publishStudentName"></strong>.</p>
            <div class="mb-3">
                <label for="emailTemplate" class="form-label">Email Notification</label>
                <select class="form-select" name="email_template_id" id="emailTemplate">
                    <option value="0" selected>No, do not send an email</option>
                    <?php foreach ($email_templates as $template): ?><option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option><?php endforeach; ?>
                </select>
                <small class="text-muted">To: <strong id="publishParentEmail"></strong></small>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label">SMS Notification</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="sendSms" name="send_sms" value="1">
                    <label class="form-check-label" for="sendSms">Send SMS Notification</label>
                </div>
                <small class="text-muted">To: <strong id="publishParentSms"></strong></small>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" id="publishSubmitBtn">Publish Now</button></div>
    </form>
</div></div></div>

<!-- Bulk Publish Modal -->
<div class="modal fade" id="bulkPublishModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form action="/controllers/publish_controller.php" method="GET">
        <div class="modal-header"><h5 class="modal-title">Bulk Publish Reports</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="publish_bulk">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($selected_period); ?>">
            <p>You are about to publish reports for all unpublished students for the period <strong><?php echo htmlspecialchars($selected_period); ?></strong>.</p>
            <div class="mb-3">
                <label for="bulkEmailTemplate" class="form-label">Email Notifications</label>
                <select class="form-select" name="email_template_id" id="bulkEmailTemplate">
                    <option value="0">No, do not send emails</option>
                    <?php foreach ($email_templates as $template): ?><option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option><?php endforeach; ?>
                </select>
            </div>
             <hr>
            <div class="mb-3">
                <label class="form-label">SMS Notifications</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="bulkSendSms" name="send_sms" value="1" <?php if ($sms_credits <= 0) echo 'disabled'; ?>>
                    <label class="form-check-label" for="bulkSendSms">Send SMS Notifications</label>
                </div>
                 <small class="text-muted">Notifications will only be sent to parents with a valid phone number. Requires credits.</small>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success" onclick="this.form.submit(); this.disabled=true; this.innerHTML='Publishing...';">Publish All</button></div>
    </form>
</div></div></div>

<!-- View Link Modal -->
<div class="modal fade" id="viewLinkModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Public Report Card Link</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p>Share this link with the student or their parents:</p>
        <div class="input-group"><input type="text" class="form-control" id="reportLinkInput" readonly><button class="btn btn-outline-secondary" onclick="copyLink()">Copy</button></div>
    </div>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const publishModalEl = document.getElementById('publishModal');
    const viewLinkModalEl = document.getElementById('viewLinkModal');
    const sms_credits = <?php echo $sms_credits; ?>;

    if (!publishModalEl || !viewLinkModalEl) {
        console.error("Required modal elements not found for initialization.");
        return;
    }

    const publishModal = new bootstrap.Modal(publishModalEl);
    const viewLinkModal = new bootstrap.Modal(viewLinkModalEl);

    window.openPublishModal = function(student) {
        if (!student || !student.id) return;

        document.getElementById('publishStudentId').value = student.id;
        document.getElementById('publishStudentName').innerText = student.full_name;

        const emailSelect = document.getElementById('emailTemplate');
        const emailInfo = document.getElementById('publishParentEmail');
        emailInfo.innerText = student.parent_email || 'N/A';
        emailSelect.disabled = !student.parent_email;
        emailSelect.value = "0";

        const smsCheckbox = document.getElementById('sendSms');
        const smsInfo = document.getElementById('publishParentSms');
        smsInfo.innerText = student.parent_phone_number || 'N/A';

        const canSendSms = student.parent_phone_number && sms_credits > 0;
        smsCheckbox.disabled = !canSendSms;
        smsCheckbox.checked = canSendSms; // Default to checked if possible

        publishModal.show();
    }

    document.getElementById('publishForm').addEventListener('submit', function(e) {
        const emailTemplateId = document.getElementById('emailTemplate').value;
        const sendSms = document.getElementById('sendSms').checked;

        if (emailTemplateId === '0' && !sendSms) {
            e.preventDefault();
            alert('Please select an email template or check the SMS notification box to publish.');
            return;
        }

        // To prevent sending email_template_id=0 when no email is desired,
        // we can dynamically build the URL or simply let the backend handle it,
        // which it now does. The logic is kept for clarity.
    });

    window.viewLink = function(hash) {
        const system_url = "<?php echo s_get($pdo, 'system_url', (defined('APP_URL') ? APP_URL : '')); ?>";
        if (!system_url) {
            alert('CRITICAL ERROR: System Base URL is not configured.');
            return;
        }
        const url = `${system_url.replace(/\/+$/, '')}/public/report.php?id=${hash}`;
        document.getElementById('reportLinkInput').value = url;
        viewLinkModal.show();
    }

    window.copyLink = function() {
        const input = document.getElementById('reportLinkInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => alert('Link copied!'));
    }
});
</script>

<?php include 'partials/footer.php'; ?>