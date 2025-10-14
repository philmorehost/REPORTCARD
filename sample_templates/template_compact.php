<?php
// Template 5: Compact & Data-Rich
?>
<style>
    .compact-container {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 0.85rem;
    }
    .compact-header {
        margin-bottom: 1.5rem;
    }
    .school-logo-compact {
        max-height: 60px;
    }
    .compact-table {
        margin-top: 1rem;
    }
    .compact-table th, .compact-table td {
        padding: 0.4rem 0.5rem;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    .compact-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .subject-col {
        text-align: left !important;
        width: 25%;
    }
    .comment-section-compact {
        margin-top: 1.5rem;
        font-size: 0.8rem;
    }
    .comment-box-compact {
        border: 1px solid #eee;
        padding: 0.5rem;
        min-height: 60px;
    }
</style>

<div class="compact-container">
    <!-- Header -->
    <header class="compact-header d-flex justify-content-between align-items-center">
        <div>
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-compact">
            <?php endif; ?>
        </div>
        <div class="text-center">
            <h4 class="mb-0 fw-bold"><?php echo d_get('school_info.name'); ?></h4>
            <p class="mb-0">Student Academic Summary</p>
        </div>
        <div>
            <h6 class="mb-0">Report Card</h6>
            <p class="mb-0"><?php echo d_get('academic_period'); ?></p>
        </div>
    </header>

    <!-- Student Information -->
    <div class="row border-top border-bottom py-2">
        <div class="col-4"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
        <div class="col-4"><strong>ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></div>
        <div class="col-4"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></div>
    </div>

    <!-- Grades Table -->
    <table class="table table-sm compact-table">
        <thead>
            <tr>
                <th class="subject-col">Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No grades recorded.</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td class="subject-col"><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <?php
                            $grades = json_decode($grade_row['grades'], true);
                            foreach ($data['report_structure']['columns'] as $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                        ?>
                            <td><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Comments & Summary Section -->
    <div class="row comment-section-compact">
        <div class="col-8">
            <strong>General Comments:</strong>
            <div class="comment-box-compact">
                A consistent and satisfactory performance throughout the term.
            </div>
        </div>
        <div class="col-4">
            <strong>Attendance:</strong>
            <div class="comment-box-compact">
                Days Present: 58/60 <br>
                Days Absent: 2
            </div>
        </div>
    </div>

</div>