<?php
// Template: Modular Grid Design
// A highly organized, space-efficient layout inspired by modern dashboards.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .grid-report-card {
        font-family: 'Nunito Sans', sans-serif;
        background-color: #f4f6f9; /* Light neutral background */
        color: #3d4852;
        max-width: 900px;
        margin: 2rem auto;
        padding: 1.5rem;
        border-radius: 12px;
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#3498db'); ?>;
        --brand-color-light: <?php echo d_get('school_info.brand_color', '#3498db') . '1A'; ?>; /* 10% opacity */
    }

    /* --- CSS Grid Layout --- */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* 4-column base */
        gap: 1.2rem; /* Space between modules */
    }

    /* --- Module Styling --- */
    .grid-module {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 1.25rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 3px 10px rgba(0,0,0,0.02);
    }
    .module-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid #f1f3f5;
        padding-bottom: 0.8rem;
    }
    .module-title {
        font-size: 1rem;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    /* --- Grid Module Placement --- */
    .school-info-module { grid-column: span 2; }
    .student-info-module { grid-column: span 2; }
    .grades-module { grid-column: span 4; }
    .comments-module { grid-column: span 2; }
    .signature-module { grid-column: span 2; }

    /* --- Specific Module Content Styling --- */
    /* School Info */
    .school-info-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .school-logo {
        max-height: 50px;
        flex-shrink: 0;
    }
    .school-details h3 {
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0 0 0.2rem 0;
        color: var(--brand-color);
    }
    .school-details p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.4;
    }

    /* Student Info */
    .student-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem 1rem;
        font-size: 0.9rem;
    }
    .info-item strong {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #8492a6;
        margin-bottom: 2px;
    }

    /* Grades Table */
    .grid-grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    .grid-grades-table th, .grid-grades-table td {
        padding: 10px 12px; /* Compact padding */
        text-align: left;
        border-bottom: 1px solid #f1f3f5;
        font-size: 0.9rem;
    }
    .grid-grades-table thead th {
        background-color: #f8f9fa;
        font-weight: 700;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .grid-grades-table tbody tr:last-child td {
        border-bottom: none;
    }
    .grid-grades-table tbody tr:hover {
        background-color: var(--brand-color-light);
    }
    .grid-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* Comments & Signatures */
    .comment-content p {
        font-size: 0.85rem;
        font-style: italic;
        color: #495057;
        margin: 0 0 1rem 0;
        line-height: 1.6;
    }
    .comment-content h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 0.5rem 0;
    }
    
    .signature-content {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%; /* Important for vertical alignment */
        min-height: 150px;
    }
    .signature-image {
        max-height: 45px;
        margin-bottom: 0.5rem;
    }
    .signature-text { /* Kept as requested */
        font-family: 'Great Vibes', cursive;
        font-size: 2.2rem;
        color: #000033;
        min-height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signature-line {
        border-top: 1px solid #ced4da;
        margin-top: 0.5rem;
    }
    .signature-title {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #6c757d;
    }

</style>

<div class="grid-report-card">
    <div class="grid-container">
        <div class="grid-module school-info-module">
            <div class="school-info-content">
                <?php if (d_get('school_info.logo_url')): ?>
                    <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo">
                <?php endif; ?>
                <div class="school-details">
                    <h3><?php echo d_get('school_info.name'); ?></h3>
                    <p><?php echo d_get('school_info.address'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="grid-module student-info-module">
            <div class="module-header">
                <h4 class="module-title">Academic Report</h4>
            </div>
            <div class="student-info-grid">
                <div class="info-item"><strong>Student Name:</strong> <span><?php echo d_get('student_info.full_name'); ?></span></div>
                <div class="info-item"><strong>Student ID:</strong> <span><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span></div>
                <div class="info-item"><strong>Class Level:</strong> <span><?php echo d_get('student_info.class'); ?></span></div>
                <div class="info-item"><strong>Period:</strong> <span><?php echo d_get('academic_period'); ?></span></div>
            </div>
        </div>

        <div class="grid-module grades-module">
            <div class="module-header">
                 <h4 class="module-title">Performance Overview</h4>
            </div>
            <table class="grid-grades-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <?php foreach ($data['report_structure']['columns'] as $column): ?>
                            <th style="text-align: center;"><?php echo htmlspecialchars($column); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['grades_data'])): ?>
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2rem;">No grade data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['grades_data'] as $grade_row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                                <?php
                                    $grades = json_decode($grade_row['grades'], true);
                                    foreach ($data['report_structure']['columns'] as $column):
                                        $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                                ?>
                                    <td><?php echo htmlspecialchars($grades[$key] ?? '–'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="grid-module comments-module">
            <div class="module-header">
                <h4 class="module-title">Remarks</h4>
            </div>
            <div class="comment-content">
                 <?php
                    $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
                    $grades_comments = json_decode($grades_json, true);
                    $teacher_comment = $grades_comments['teacher_comment'] ?? '';
                    $principal_comment = $grades_comments['principal_comment'] ?? '';
                ?>
                <?php if (!empty($teacher_comment)): ?>
                    <div>
                        <h5>Teacher's Comment</h5>
                        <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($principal_comment)): ?>
                    <div style="<?php echo empty($teacher_comment) ? '' : 'margin-top: 1.5rem;'; ?>">
                        <h5>Principal's Comment</h5>
                        <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid-module signature-module">
            <div class="signature-content">
                <?php
                    $principal_sig = d_get('principal_signature', null, false);
                    $signature_content = '<div class="signature-text">Not Signed</div>';
                    if (is_array($principal_sig) && !empty($principal_sig['signature_data'])) {
                        if ($principal_sig['signature_type'] === 'image') {
                            $signature_content = '<img src="/' . htmlspecialchars($principal_sig['signature_data']) . '" alt="Principal Signature" class="signature-image">';
                        } else {
                            $signature_content = '<div class="signature-text">' . htmlspecialchars($principal_sig['signature_data']) . '</div>';
                        }
                    }
                    echo $signature_content;
                ?>
                <div class="signature-line"></div>
                <p class="signature-title">Principal's Signature</p>
            </div>
        </div>
    </div>
</div>