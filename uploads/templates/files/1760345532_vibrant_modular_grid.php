<?php
// Template: Vibrant Modular Grid Design
// A multi-color, space-efficient modular layout with subtle shadows and dynamic colors.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;700&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .vibrant-grid-report {
        font-family: 'Quicksand', sans-serif; /* Friendly and modern font */
        background-color: #eef2f6; /* Soft, light background */
        color: #3f51b5; /* Default text color, inspired by a deep blue */
        max-width: 950px; /* Slightly wider for color emphasis */
        margin: 2rem auto;
        padding: 1.8rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    /* Dynamically set the main brand color */
    :root {
        --brand-main-color: <?php echo d_get('school_info.brand_color', '#673ab7'); ?>; /* Default to a deep purple */
    }

    /* --- Define Module Colors --- */
    /* These colors are chosen to be harmonious but distinct */
    .color-module-1 { background-color: #ffffff; border-left: 5px solid var(--brand-main-color); }
    .color-module-2 { background-color: #ffffff; border-left: 5px solid #00acc1; } /* Cyan */
    .color-module-3 { background-color: #ffffff; border-left: 5px solid #ff9800; } /* Orange */
    .color-module-4 { background-color: #ffffff; border-left: 5px solid #4caf50; } /* Green */
    .color-module-5 { background-color: #ffffff; border-left: 5px solid #e91e63; } /* Pink */

    /* --- CSS Grid Layout --- */
    .vibrant-grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* Same 4-column base */
        gap: 1.5rem; /* Slightly more space between vibrant cards */
    }

    /* --- Module Styling --- */
    .vibrant-module {
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 6px 15px rgba(0,0,0,0.06); /* Subtle shadow for depth */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .vibrant-module:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .vibrant-module-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid rgba(0,0,0,0.08); /* Lighter border for contrast */
    }
    .vibrant-module-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #263238; /* Darker text for titles */
        margin: 0;
    }

    /* --- Grid Module Placement --- */
    .grid-school-info { grid-column: span 2; }
    .grid-student-info { grid-column: span 2; }
    .grid-grades { grid-column: span 4; }
    .grid-comments { grid-column: span 2; }
    .grid-signature { grid-column: span 2; }

    /* --- Specific Module Content Styling --- */
    /* School Info */
    .vibrant-school-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .vibrant-school-logo {
        max-height: 60px;
        flex-shrink: 0;
        border-radius: 8px; /* Slightly rounded logo */
    }
    .vibrant-school-details h3 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 0.3rem 0;
        color: var(--brand-main-color);
        line-height: 1.2;
    }
    .vibrant-school-details p {
        font-size: 0.85rem;
        color: #546e7a;
        margin: 0;
    }

    /* Student Info */
    .vibrant-student-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.7rem 1.2rem;
        font-size: 0.9rem;
    }
    .vibrant-info-item strong {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #78909c;
        margin-bottom: 2px;
    }
    .vibrant-info-item span {
        color: #455a64;
    }

    /* Grades Table */
    .vibrant-grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    .vibrant-grades-table th, .vibrant-grades-table td {
        padding: 9px 12px; /* Very compact padding */
        text-align: left;
        border-bottom: 1px solid #eceff1;
        font-size: 0.88rem;
        color: #455a64;
    }
    .vibrant-grades-table thead th {
        background-color: var(--brand-main-color);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .vibrant-grades-table tbody tr:last-child td {
        border-bottom: none;
    }
    .vibrant-grades-table tbody tr:hover {
        background-color: rgba(103, 58, 183, 0.05); /* Light hover from brand color */
    }
    .vibrant-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* Comments & Signatures */
    .vibrant-comment-content p {
        font-size: 0.82rem;
        font-style: italic;
        color: #546e7a;
        margin: 0 0 0.8rem 0;
        line-height: 1.6;
    }
    .vibrant-comment-content h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #37474f;
        margin: 0 0 0.5rem 0;
    }
    
    .vibrant-signature-content {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        min-height: 140px; /* Ensures minimum height for alignment */
    }
    .vibrant-signature-image {
        max-height: 40px;
        margin-bottom: 0.4rem;
    }
    .vibrant-signature-text { /* Kept as requested */
        font-family: 'Great Vibes', cursive;
        font-size: 2rem;
        color: #000033;
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .vibrant-signature-line {
        border-top: 1px solid #b0bec5;
        margin-top: 0.4rem;
    }
    .vibrant-signature-title {
        margin-top: 0.5rem;
        font-size: 0.75rem;
        color: #78909c;
    }

</style>

<div class="vibrant-grid-report">
    <div class="vibrant-grid-container">
        
        <div class="vibrant-module grid-school-info color-module-1">
            <div class="vibrant-school-content">
                <?php if (d_get('school_info.logo_url')): ?>
                    <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="vibrant-school-logo">
                <?php endif; ?>
                <div class="vibrant-school-details">
                    <h3><?php echo d_get('school_info.name'); ?></h3>
                    <p><?php echo d_get('school_info.address'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="vibrant-module grid-student-info color-module-2">
            <div class="vibrant-module-header">
                <h4 class="vibrant-module-title">Student Profile</h4>
            </div>
            <div class="vibrant-student-grid">
                <div class="vibrant-info-item"><strong>Student Name:</strong> <span><?php echo d_get('student_info.full_name'); ?></span></div>
                <div class="vibrant-info-item"><strong>Student ID:</strong> <span><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span></div>
                <div class="vibrant-info-item"><strong>Class Level:</strong> <span><?php echo d_get('student_info.class'); ?></span></div>
                <div class="vibrant-info-item"><strong>Period:</strong> <span><?php echo d_get('academic_period'); ?></span></div>
            </div>
        </div>

        <div class="vibrant-module grid-grades color-module-3">
            <div class="vibrant-module-header">
                 <h4 class="vibrant-module-title">Academic Performance</h4>
            </div>
            <table class="vibrant-grades-table">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2rem; color: #777;">No grade data available.</td></tr>
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

        <div class="vibrant-module grid-comments color-module-4">
            <div class="vibrant-module-header">
                <h4 class="vibrant-module-title">Remarks</h4>
            </div>
            <div class="vibrant-comment-content">
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

        <div class="vibrant-module grid-signature color-module-5">
            <div class="vibrant-signature-content">
                <?php
                    $principal_sig = d_get('principal_signature', null, false);
                    $signature_content = '<div class="vibrant-signature-text">Not Signed</div>';
                    if (is_array($principal_sig) && !empty($principal_sig['signature_data'])) {
                        if ($principal_sig['signature_type'] === 'image') {
                            $signature_content = '<img src="/' . htmlspecialchars($principal_sig['signature_data']) . '" alt="Principal Signature" class="vibrant-signature-image">';
                        } else {
                            $signature_content = '<div class="vibrant-signature-text">' . htmlspecialchars($principal_sig['signature_data']) . '</div>';
                        }
                    }
                    echo $signature_content;
                ?>
                <div class="vibrant-signature-line"></div>
                <p class="vibrant-signature-title">Principal's Signature</p>
            </div>
        </div>
    </div>
</div>