<?php
// Template 2: Modern Minimalist

// Logic to find first available comments from any subject's grade entry
$principal_comment_found = '';
$teacher_comment_found = '';
if (!empty($data['grades_data'])) {
    foreach ($data['grades_data'] as $grade_row) {
        if (!empty($grade_row['grades'])) {
            $grades = json_decode($grade_row['grades'], true);
            if (empty($teacher_comment_found) && !empty($grades['teacher_comment'])) {
                $teacher_comment_found = $grades['teacher_comment'];
            }
            if (empty($teacher_comment_found) && !empty($grades['remark'])) {
                $teacher_comment_found = $grades['remark'];
            }
            if (empty($principal_comment_found) && !empty($grades['principal_comment'])) {
                $principal_comment_found = $grades['principal_comment'];
            }
        }
    }
}
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Cedarville+Cursive&display=swap');
    .modern-container {
        font-family: 'Inter', sans-serif;
        border: 1px solid #e0e0e0;
        padding: 2rem;
        border-radius: 8px;
    }
    .modern-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    .school-logo-modern {
        max-height: 70px;
    }
    .modern-table {
        border: none;
    }
    .modern-table th {
        background-color: <?php echo d_get('school_info.brand_color', '#4a4a4a'); ?> !important;
        color: white;
        font-weight: 600;
        border: none !important;
    }
    .modern-table td {
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        border-left: none;
        border-right: none;
        vertical-align: middle;
        padding: 1rem;
    }
    .modern-table tbody tr:first-child td { border-top: none; }
    .modern-table tbody tr:last-child td { border-bottom: none; }
    .comment-box {
        background-color: #f8f9fa;
        border-left: 4px solid <?php echo d_get('school_info.brand_color', '#4a4a4a'); ?>;
        padding: 1rem;
        margin-top: 1rem;
        height: 100%;
    }
    .signature-text-modern {
        font-family: 'Cedarville Cursive', cursive;
        font-size: 1.8rem;
        line-height: 1;
    }
    .signature-image-modern {
        max-height: 60px;
    }
</style>

<div class="modern-container">
    <!-- Header -->
    <header class="modern-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0 fw-bold"><?php echo d_get('school_info.name'); ?></h2>
             <?php if (d_get('school_info.address')): ?>
                <p class="mb-0 text-muted"><?php echo d_get('school_info.address'); ?></p>
            <?php endif; ?>
        </div>
        <?php if (d_get('school_info.logo_url') && !str_contains(d_get('school_info.logo_url'), 'placeholder')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-modern">
        <?php endif; ?>
    </header>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <p class="mb-1"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></p>
            <p class="mb-1"><strong>ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
        </div>
        <div class="col-md-6 text-md-end">
            <p class="mb-1"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
            <p class="mb-1"><strong>Period:</strong> <?php echo d_get('academic_period'); ?></p>
        </div>
    </div>

    <!-- Grades Table -->
    <table class="table modern-table">
        <thead>
            <tr>
                <th style="width: 30%;">Subject</th>
                <?php foreach (d_get('report_structure.columns', []) as $column): ?>
                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty(d_get('grades_data', []))): ?>
                <tr><td colspan="<?php echo count(d_get('report_structure.columns', [])) + 1; ?>" class="text-center py-5">No grade data available.</td></tr>
            <?php else: ?>
                 <?php foreach (d_get('grades_data', []) as $grade_row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <?php
                            $grades = json_decode($grade_row['grades'], true) ?: [];
                            foreach (d_get('report_structure.columns', []) as $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                        ?>
                            <td class="text-center"><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Comments Section -->
    <div class="row mt-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="comment-box">
                <strong>Teacher's Comment:</strong>
                <p class="mb-0"><?php echo htmlspecialchars($teacher_comment_found ?: 'No comment provided.'); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="comment-box">
                <strong>Principal's Comment:</strong>
                <p class="mb-0"><?php echo htmlspecialchars($principal_comment_found ?: 'No comment provided.'); ?></p>
            </div>
        </div>
    </div>

    <!-- Signature Area -->
    <footer class="mt-5 pt-4 text-center">
         <?php
        $signature = d_get('principal_signature', null, false);
        if ($signature && !empty($signature['signature_data'])) {
            if ($signature['signature_type'] === 'image') {
                echo '<img src="/' . htmlspecialchars($signature['signature_data']) . '" alt="Principal\'s Signature" class="signature-image-modern">';
            } elseif ($signature['signature_type'] === 'text') {
                echo '<p class="mb-0 signature-text-modern">' . htmlspecialchars($signature['signature_data']) . '</p>';
            }
        } else {
            echo '<div style="height: 60px;"></div>';
        }
        ?>
        <hr class="mx-auto mt-1" style="width: 50%;">
        <p class="text-muted">Principal's Signature</p>
    </footer>
</div>