<?php
// Template 11: Secondary - Formal Transcript Style
?>
<style>
    .transcript-container {
        border: 1px solid #000;
        padding: 1.5rem;
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
    }
    .transcript-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .transcript-header h3 {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .transcript-header h5 {
        font-weight: normal;
    }
    .student-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 2rem;
        margin-bottom: 2rem;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 0.5rem 0;
    }
    .transcript-table {
        width: 100%;
        border-collapse: collapse;
    }
    .transcript-table th, .transcript-table td {
        border-bottom: 1px solid #ccc;
        padding: 0.6rem;
        text-align: left;
    }
    .transcript-table th {
        font-weight: bold;
        border-bottom: 2px solid #000;
    }
    .transcript-summary {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 2px solid #000;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.25rem 0;
    }
</style>

<div class="transcript-container">
    <!-- Header -->
    <header class="transcript-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" style="max-height: 75px; margin-bottom: 1rem;">
        <?php endif; ?>
        <h3><?php echo d_get('school_info.name'); ?></h3>
        <h5>Official Student Transcript</h5>
    </header>

    <!-- Student Information -->
    <div class="student-info-grid">
        <div><strong>Student Name:</strong> <?php echo d_get('student_info.full_name'); ?></div>
        <div><strong>Class Level:</strong> <?php echo d_get('student_info.class'); ?></div>
        <div><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></div>
        <div><strong>Reporting Period:</strong> <?php echo d_get('academic_period'); ?></div>
    </div>

    <!-- Grades Table -->
    <table class="transcript-table">
        <thead>
            <tr>
                <th style="width: 40%;">Course/Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No academic data for this period.</td></tr>
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

    <!-- Summary Section -->
    <div class="row transcript-summary">
        <div class="col-md-6">
            <p><strong>Comments:</strong></p>
            <p>A commendable effort this term. Continued dedication will lead to further success.</p>
        </div>
        <div class="col-md-6">
            <div class="summary-item">
                <span>Term GPA:</span>
                <strong>3.85</strong>
            </div>
            <div class="summary-item">
                <span>Cumulative GPA:</span>
                <strong>3.72</strong>
            </div>
             <div class="summary-item">
                <span>Attendance:</span>
                <strong>98%</strong>
            </div>
        </div>
    </div>

    <!-- Signature Area -->
    <div class="row" style="margin-top: 4rem;">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <div style="border-bottom: 1px solid #000; margin-bottom: 0.5rem;"></div>
            <p style="margin:0;">Registrar's Signature</p>
        </div>
    </div>
</div>