<?php
// Template 4: Elegant & Traditional
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;600&display=swap');
    .elegant-container {
        font-family: 'Lora', serif;
        border: 1px solid #888;
        padding: 2.5rem;
        background-color: #fff;
    }
    .elegant-outer-border {
        border: 5px double #333;
        padding: 1rem;
    }
    .elegant-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .elegant-header h1 {
        font-weight: 600;
        letter-spacing: 2px;
    }
    .school-logo-elegant {
        max-height: 85px;
        margin-bottom: 1rem;
    }
    .elegant-table {
        font-size: 0.95rem;
    }
    .elegant-table th, .elegant-table td {
        border: 1px solid #ccc;
        padding: 0.6rem;
    }
    .elegant-table thead th {
        background-color: #f5f5f5;
        font-weight: 600;
    }
    .comment-area {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #ccc;
    }
    .signature-line {
        border-bottom: 1px solid #333;
        margin-top: 4rem;
        display: inline-block;
        width: 70%;
    }
</style>

<div class="elegant-container">
    <div class="elegant-outer-border">
        <!-- Header -->
        <header class="elegant-header">
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-elegant">
            <?php endif; ?>
            <h1 class="mb-1"><?php echo d_get('school_info.name'); ?></h1>
            <p>Academic Progress Report</p>
        </header>

        <!-- Student Information -->
        <table class="table table-sm table-borderless mb-4">
            <tbody>
                <tr>
                    <td class="px-0"><strong>Student:</strong></td>
                    <td class="px-0"><?php echo d_get('student_info.full_name'); ?></td>
                    <td class="px-0"><strong>Class:</strong></td>
                    <td class="px-0"><?php echo d_get('student_info.class'); ?></td>
                </tr>
                <tr>
                    <td class="px-0"><strong>Student ID:</strong></td>
                    <td class="px-0"><?php echo d_get('student_info.student_id_number', 'N/A'); ?></td>
                    <td class="px-0"><strong>Academic Period:</strong></td>
                    <td class="px-0"><?php echo d_get('academic_period'); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Grades Table -->
        <table class="table elegant-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Subject</th>
                    <?php foreach ($data['report_structure']['columns'] as $column): ?>
                        <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['grades_data'])): ?>
                    <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No grades recorded.</td></tr>
                <?php else: ?>
                     <?php foreach ($data['grades_data'] as $grade_row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                            <?php
                                $grades = json_decode($grade_row['grades'], true);
                                foreach ($data['report_structure']['columns'] as $column):
                                    $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                            ?>
                                <td class="text-center"><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Comments -->
        <div class="comment-area">
            <p><strong>General Remarks:</strong> A commendable performance this term. Continued focus will yield even greater results.</p>
        </div>

        <!-- Signatures -->
        <div class="row" style="margin-top: 5rem;">
            <div class="col-6 text-center">
                <span class="signature-line"></span>
                <p class="mt-2 mb-0">Head of School</p>
            </div>
            <div class="col-6 text-center">
                <span class="signature-line"></span>
                <p class="mt-2 mb-0">Date</p>
            </div>
        </div>
    </div>
</div>