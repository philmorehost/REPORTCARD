<?php
// Template 12: Secondary - Two-Column Professional
?>
<style>
    .twocol-container {
        font-family: Arial, sans-serif;
        padding: 2rem;
        background-color: #fff;
    }
    .twocol-header {
        border-bottom: 4px solid <?php echo d_get('school_info.brand_color', '#003366'); ?>;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }
    .twocol-header h2 {
        font-weight: bold;
        color: <?php echo d_get('school_info.brand_color', '#003366'); ?>;
    }
    .subject-row {
        border-bottom: 1px solid #eee;
        padding: 1.5rem 0;
    }
    .subject-row:last-child {
        border-bottom: none;
    }
    .subject-title {
        font-size: 1.2rem;
        font-weight: bold;
    }
    .grades-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
        gap: 1rem;
        text-align: center;
    }
    .grade-box {
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-radius: 4px;
    }
    .grade-box .value {
        font-size: 1.4rem;
        font-weight: bold;
    }
    .grade-box .label {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .teacher-comment {
        font-style: italic;
        color: #333;
    }
</style>

<div class="twocol-container">
    <!-- Header -->
    <header class="twocol-header">
        <div class="row align-items-center">
            <div class="col-8">
                <h2 class="mb-0"><?php echo d_get('school_info.name'); ?></h2>
                <p class="mb-0 text-muted">Student Performance Report - <?php echo d_get('academic_period'); ?></p>
            </div>
            <div class="col-4 text-end">
                <?php if (d_get('school_info.logo_url')): ?>
                    <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" style="max-height: 50px;">
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Student Info -->
    <div class="row mb-4">
        <div class="col"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
        <div class="col"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></div>
    </div>

    <!-- Grade Rows -->
    <?php if (empty($data['grades_data'])): ?>
        <div class="text-center p-5">No subjects recorded for this period.</div>
    <?php else: ?>
        <?php foreach ($data['grades_data'] as $grade_row): ?>
            <div class="row subject-row">
                <div class="col-md-6">
                    <p class="subject-title"><?php echo htmlspecialchars($grade_row['class_name']); ?></p>
                    <div class="grades-grid">
                        <?php
                            $grades = json_decode($grade_row['grades'], true);
                            foreach ($data['report_structure']['columns'] as $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                                if (strtolower($column) !== 'remark'):
                        ?>
                            <div class="grade-box">
                                <div class="value"><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></div>
                                <div class="label"><?php echo htmlspecialchars($column); ?></div>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <p><strong>Teacher's Comment:</strong></p>
                    <p class="teacher-comment">
                        <?php
                            $remark_key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', 'Remark'));
                            echo htmlspecialchars($grades[$remark_key] ?? 'No comment provided.');
                        ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>