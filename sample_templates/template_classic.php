<?php
// Template 1: Classic Professional
// This file is included by public/report.php and has access to the $data variable
// and the d_get() helper function.

// Logic to find first available comments from any subject's grade entry
$principal_comment_found = '';
$teacher_comment_found = '';
if (!empty($data['grades_data'])) {
    foreach ($data['grades_data'] as $grade_row) {
        if (!empty($grade_row['grades'])) {
            $grades = json_decode($grade_row['grades'], true);
            // Use 'teacher_comment' and 'principal_comment' as the canonical keys.
            // These keys are generated from the column names "Teacher Comment" and "Principal Comment".
            if (empty($teacher_comment_found) && !empty($grades['teacher_comment'])) {
                $teacher_comment_found = $grades['teacher_comment'];
            }
            // Fallback for older or alternatively named columns like "Remark"
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
    @import url('https://fonts.googleapis.com/css2?family=Cedarville+Cursive&display=swap');
    .classic-table th, .classic-table td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    .classic-header {
        border-bottom: 2px solid <?php echo d_get('school_info.brand_color', '#333'); ?>;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
    .school-logo {
        max-height: 100px;
        max-width: 250px;
    }
    .signature-text {
        font-family: 'Cedarville Cursive', cursive;
        font-size: 1.8rem;
        line-height: 1;
    }
    .signature-image {
        max-height: 60px;
    }
</style>

<!-- Header -->
<header class="classic-header d-flex justify-content-between align-items-center">
    <div>
        <?php if (d_get('school_info.logo_url') && !str_contains(d_get('school_info.logo_url'), 'placeholder')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo">
        <?php endif; ?>
    </div>
    <div class="text-end">
        <h2 class="mb-1 fw-bold"><?php echo d_get('school_info.name'); ?></h2>
        <?php if (d_get('school_info.address')): ?>
            <p class="mb-1 text-muted"><?php echo d_get('school_info.address'); ?></p>
        <?php endif; ?>
        <p class="mb-0 text-muted">Student Academic Report</p>
    </div>
</header>

<!-- Title Bar -->
<div class="title-bar text-center p-2 my-3">
    <h4 class="mb-0">REPORT CARD</h4>
</div>

<!-- Student Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <p class="mb-1"><strong>Student Name:</strong> <?php echo d_get('student_info.full_name'); ?></p>
        <p class="mb-1"><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
    </div>
    <div class="col-md-6 text-md-end">
        <p class="mb-1"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
        <p class="mb-1"><strong>Academic Period:</strong> <?php echo d_get('academic_period'); ?></p>
    </div>
</div>

<!-- Grades Table -->
<table class="table table-bordered classic-table">
    <thead class="table-light">
        <tr>
            <th style="width: 30%;">Subject</th>
            <?php foreach (d_get('report_structure.columns', []) as $column): ?>
                <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty(d_get('grades_data', []))): ?>
            <tr><td colspan="<?php echo count(d_get('report_structure.columns', [])) + 1; ?>" class="text-center">No grades have been recorded for this period.</td></tr>
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
    <div class="col-md-6">
        <h5>Teacher's General Comment:</h5>
        <p class="border p-2" style="min-height: 80px;">
            <?php echo htmlspecialchars($teacher_comment_found ?: 'No comment provided.'); ?>
        </p>
    </div>
    <div class="col-md-6">
        <h5>Principal's Comment:</h5>
        <p class="border p-2" style="min-height: 80px;">
            <?php echo htmlspecialchars($principal_comment_found ?: 'No comment provided.'); ?>
        </p>
    </div>
</div>

<!-- Signature Area -->
<div class="row mt-5 pt-5">
    <div class="col-12 text-center">
        <?php
        $signature = d_get('principal_signature', null, false);
        if ($signature && !empty($signature['signature_data'])) {
            if ($signature['signature_type'] === 'image') {
                echo '<img src="/' . htmlspecialchars($signature['signature_data']) . '" alt="Principal\'s Signature" class="signature-image">';
            } elseif ($signature['signature_type'] === 'text') {
                echo '<p class="mb-0 signature-text">' . htmlspecialchars($signature['signature_data']) . '</p>';
            }
        } else {
            // Provide an empty space for a manual signature if none is set
            echo '<div style="height: 60px;"></div>';
        }
        ?>
        <hr class="mx-auto mt-1" style="width: 60%;">
        <p>Principal's Signature</p>
    </div>
</div>