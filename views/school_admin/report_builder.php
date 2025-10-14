<?php
$page_title = "Report Card Builder";
include 'partials/header.php'; // Includes auth, db, school info, etc.

$school_id = $_SESSION['school_id'];
$admin_user_id = $_SESSION['user_id'];

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// --- Fetch all necessary data ---
try {
    // Fetch school's package info
    $stmt_package = $pdo->prepare(
        "SELECT p.name as package_name FROM schools s JOIN packages p ON s.package_id = p.id WHERE s.id = :school_id"
    );
    $stmt_package->execute(['school_id' => $school_id]);
    $package_info = $stmt_package->fetch(PDO::FETCH_ASSOC);
    $is_freemium = ($package_info && strtolower($package_info['package_name']) === 'freemium');

    // Fetch report card settings
    $stmt_settings = $pdo->prepare("SELECT * FROM report_card_settings WHERE school_id = :school_id");
    $stmt_settings->execute(['school_id' => $school_id]);
    $settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        $default_columns = ['1st CA', '2nd CA', 'Exam', 'Total', 'Grade', 'Remark'];
        $settings = ['template_id' => 1, 'columns' => json_encode($default_columns)];
    }
    $report_columns = json_decode($settings['columns'], true);

    // Fetch all available templates
    $stmt_templates = $pdo->query("SELECT id, name, thumbnail_url, file_path FROM report_templates ORDER BY id ASC");
    $templates = $stmt_templates->fetchAll(PDO::FETCH_ASSOC);

    // Fetch site name for watermark
    $site_name = s_get($pdo, 'site_name', 'Freemium Plan');

    // --- Fetch data for new customization options ---
    $school_address = $school['address'] ?? '';

    $stmt_comments = $pdo->prepare("SELECT comment_text FROM report_comments WHERE school_id = :school_id AND comment_type = 'principal'");
    $stmt_comments->execute(['school_id' => $school_id]);
    $principal_comments_array = $stmt_comments->fetchAll(PDO::FETCH_COLUMN);
    $principal_comments = implode("\n", $principal_comments_array);

    $stmt_sig = $pdo->prepare("SELECT * FROM report_card_signatures WHERE user_id = :user_id");
    $stmt_sig->execute(['user_id' => $admin_user_id]);
    $signature = $stmt_sig->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Could not load report card settings. Error: ' . $e->getMessage() . '</div>';
    $report_columns = []; $settings = []; $templates = []; $is_freemium = false;
    $school_address = ''; $principal_comments = ''; $signature = false;
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Report Card Builder</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="/controllers/report_builder_controller.php" method="POST" enctype="multipart/form-data" id="builderForm">
        <input type="hidden" name="action" value="save_settings">
        <input type="hidden" name="columns" id="columnsInput">
        <input type="hidden" name="existing_logo_url" value="<?php echo htmlspecialchars($school['logo_url'] ?? ''); ?>">
        <input type="hidden" name="existing_signature_data" value="<?php echo htmlspecialchars($signature['signature_data'] ?? ''); ?>">

        <div class="row">
            <!-- Left Panel: Settings -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">1. Select a Base Template</h6></div>
                    <div class="card-body">
                        <?php if ($is_freemium): ?>
                            <div class="alert alert-info">As a Freemium user, you have access to one standard template. <a href="billing.php">Upgrade to Premium</a> to unlock all templates.</div>
                        <?php endif; ?>
                        <div class="row">
                            <?php foreach ($templates as $index => $template):
                                $is_first_template = ($index === 0);
                                $is_disabled = ($is_freemium && !$is_first_template);
                                $is_checked = ($is_freemium) ? $is_first_template : (($settings['template_id'] ?? 1) == $template['id']);
                            ?>
                            <div class="col-4 text-center <?php if ($is_disabled) echo 'opacity-50'; ?>" title="<?php if ($is_disabled) echo 'Upgrade to Premium to use this template.'; ?>">
                                <img src="/<?php echo htmlspecialchars($template['thumbnail_url']); ?>" class="img-thumbnail mb-2" alt="<?php echo htmlspecialchars($template['name']); ?>" style="height:120px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="template_id" value="<?php echo $template['id']; ?>" id="template<?php echo $template['id']; ?>" <?php if ($is_checked) echo 'checked'; ?> <?php if ($is_disabled) echo 'disabled'; ?>>
                                    <label class="form-check-label" for="template<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">2. Branding & Colors</h6></div>
                    <div class="card-body">
                        <div class="mb-3"><label for="schoolLogo" class="form-label">Upload School Logo</label><input class="form-control" type="file" name="logo" id="schoolLogo"></div>
                        <div class="mb-3"><label for="brandColor" class="form-label">Primary Brand Color</label><input type="color" class="form-control form-control-color" name="brand_color" id="brandColor" value="<?php echo htmlspecialchars($school['brand_color'] ?? '#0d6efd'); ?>"></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">3. Dynamic Field Builder</h6></div>
                    <div class="card-body">
                        <p class="text-muted">Define the columns for your report card's grading table. Drag to reorder.</p>
                        <ul class="list-group" id="field-list"><?php foreach ($report_columns as $column): ?><li class="list-group-item d-flex justify-content-between align-items-center"><span><?php echo htmlspecialchars($column); ?></span><div><i class="bi bi-grip-vertical me-2" style="cursor: grab;"></i><i class="bi bi-trash-fill text-danger" style="cursor: pointer;" onclick="removeField(this)"></i></div></li><?php endforeach; ?></ul>
                        <div class="input-group mt-3"><input type="text" id="new-column-input" class="form-control" placeholder="Add new column..."><button class="btn btn-outline-secondary" type="button" id="add-column-btn">Add</button></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">4. School Address</h6></div>
                    <div class="card-body">
                        <label for="schoolAddress" class="form-label">Enter the school's full address as it should appear on the report card.</label>
                        <textarea class="form-control" id="schoolAddress" name="school_address" rows="3"><?php echo htmlspecialchars($school_address); ?></textarea>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">5. Principal's Comment Bank</h6></div>
                    <div class="card-body">
                        <label for="principalComments" class="form-label">Enter a list of pre-defined comments for the principal. Separate each comment with a new line.</label>
                        <textarea class="form-control" id="principalComments" name="principal_comments" rows="5"><?php echo htmlspecialchars($principal_comments); ?></textarea>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">6. Principal's Signature</h6></div>
                    <div class="card-body">
                        <p>Set the principal's signature. This will be linked to your (school admin) account.</p>
                        <div class="mb-3">
                            <label class="form-label">Signature Type</label>
                            <div class="form-check"><input class="form-check-input" type="radio" name="signature_type" id="sigTypeText" value="text" <?php if (empty($signature) || $signature['signature_type'] === 'text') echo 'checked'; ?>><label class="form-check-label" for="sigTypeText">Typed Signature</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="signature_type" id="sigTypeImage" value="image" <?php if (!empty($signature) && $signature['signature_type'] === 'image') echo 'checked'; ?>><label class="form-check-label" for="sigTypeImage">Upload Image</label></div>
                        </div>
                        <div id="typedSignatureSection">
                            <label for="signatureText" class="form-label">Principal's Name (Typed)</label>
                            <input type="text" class="form-control" id="signatureText" name="signature_text" value="<?php if (!empty($signature) && $signature['signature_type'] === 'text') echo htmlspecialchars($signature['signature_data']); ?>">
                        </div>
                        <div id="imageSignatureSection" class="mt-3" style="display: none;">
                            <label for="signatureImage" class="form-label">Upload Signature Image (PNG recommended)</label>
                            <input class="form-control" type="file" name="signature_image" id="signatureImage">
                            <?php if (!empty($signature) && $signature['signature_type'] === 'image'): ?>
                                <div class="mt-2"><small>Current signature:</small><br><img src="/<?php echo htmlspecialchars($signature['signature_data']); ?>" alt="Signature" style="max-height: 50px; border: 1px solid #ddd; padding: 2px;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="d-grid"><button type="submit" class="btn btn-lg btn-success">Save All Settings</button></div>
            </div>

            <!-- Right Panel: Live Preview -->
            <div class="col-lg-7">
                <div class="card shadow">
                    <div class="card-header"><h6 class="m-0 fw-bold text-primary">Live Preview</h6></div>
                    <div class="card-body">
                        <style>.preview-container{position:relative;}.watermark{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-45deg);font-size:4rem;color:rgba(0,0,0,.08);font-weight:700;pointer-events:none;z-index:1000;text-transform:uppercase;opacity:0;transition:opacity .3s}</style>
                        <div id="preview-container" class="preview-container">
                            <div id="preview-pane" class="border p-1"></div>
                            <div id="watermark" class="watermark"><span><?php echo htmlspecialchars($site_name); ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewPane = document.getElementById('preview-pane');
    const isFreemium = <?php echo json_encode($is_freemium); ?>;

    async function updatePreview() {
        const selectedTemplateRadio = (isFreemium) ? document.querySelector('input[name="template_id"]') : document.querySelector('input[name="template_id"]:checked');
        if (!selectedTemplateRadio) {
            previewPane.innerHTML = "<div class='alert alert-danger'>No template selected.</div>";
            return;
        }
        const selectedTemplateId = selectedTemplateRadio.value;
        previewPane.innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        try {
            const response = await fetch(`/controllers/template_preview_controller.php?template_id=${selectedTemplateId}`);
            if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
            previewPane.innerHTML = await response.text();
            applyBrandingToPreview();
        } catch (error) {
            previewPane.innerHTML = `<div class='alert alert-danger'>Failed to load preview: ${error.message}</div>`;
        }
    }

    function applyBrandingToPreview() {
        const logoInput = document.getElementById('schoolLogo');
        const colorInput = document.getElementById('brandColor');
        const watermark = document.getElementById('watermark');
        const previewLogo = previewPane.querySelector('.school-logo, .school-logo-modern, .school-logo-vibrant, .school-logo-elegant, .school-logo-compact, .uni-logo');
        if (watermark) watermark.style.opacity = isFreemium ? '1' : '0';
        const brandColor = colorInput.value;
        previewPane.querySelectorAll('.title-bar, .modern-table th, .vibrant-table th, .review-header').forEach(el => el.style.backgroundColor = brandColor);
        previewPane.querySelectorAll('.vibrant-header h2, .comment-box h5, .goals-header .school-name').forEach(el => el.style.color = brandColor);
        if (logoInput.files && logoInput.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { if (previewLogo) previewLogo.src = e.target.result; };
            reader.readAsDataURL(logoInput.files[0]);
        }
    }

    document.querySelectorAll('input[name="template_id"]').forEach(radio => radio.addEventListener('change', updatePreview));
    document.getElementById('brandColor').addEventListener('input', applyBrandingToPreview);
    document.getElementById('schoolLogo').addEventListener('change', applyBrandingToPreview);

    // --- Signature Type Toggle ---
    function toggleSignatureSections() {
        const selectedType = document.querySelector('input[name="signature_type"]:checked').value;
        document.getElementById('typedSignatureSection').style.display = (selectedType === 'text') ? 'block' : 'none';
        document.getElementById('imageSignatureSection').style.display = (selectedType === 'image') ? 'block' : 'none';
    }
    document.querySelectorAll('input[name="signature_type"]').forEach(radio => radio.addEventListener('change', toggleSignatureSections));
    toggleSignatureSections(); // Initial call

    // --- Dynamic Field Builder ---
    const fieldList = document.getElementById('field-list');
    Sortable.create(fieldList, { handle: '.bi-grip-vertical', animation: 150 });
    document.getElementById('add-column-btn').addEventListener('click', function() {
        const input = document.getElementById('new-column-input');
        const columnName = input.value.trim();
        if (columnName) {
            fieldList.insertAdjacentHTML('beforeend', `<li class="list-group-item d-flex justify-content-between align-items-center"><span>${columnName}</span><div><i class="bi bi-grip-vertical me-2" style="cursor: grab;"></i><i class="bi bi-trash-fill text-danger" style="cursor: pointer;" onclick="this.closest('li').remove()"></i></div></li>`);
            input.value = '';
        }
    });
    window.removeField = (element) => element.closest('li').remove(); // Make it globally accessible

    // --- Form Submission ---
    document.getElementById('builderForm').addEventListener('submit', function() {
        const columns = Array.from(fieldList.querySelectorAll('li span')).map(span => span.textContent);
        document.getElementById('columnsInput').value = columns.join(',');
    });

    updatePreview(); // Initial preview load
});
</script>

<?php include 'partials/footer.php'; ?>