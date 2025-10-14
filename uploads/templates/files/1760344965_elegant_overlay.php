<?php
// Template: Elegant Overlay Design
// A refined and beautiful modern design with sophisticated aesthetics and optimized space.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .elegant-container {
        font-family: 'Lato', sans-serif;
        background-color: #fcfdfe; /* Very subtle off-white background */
        color: #333333;
        max-width: 900px; /* Slightly wider for a more spacious feel */
        margin: 2rem auto;
        border-radius: 12px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1); /* Deeper shadow for elegance */
        overflow: hidden;
        position: relative;
    }

    /* Dynamic Brand Color with a softer touch */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#8b5e83'); ?>; /* Default to a soft purple */
        --brand-color-gradient-start: <?php echo d_get('school_info.brand_color', '#8b5e83'); ?>;
        --brand-color-gradient-end: <?php echo d_get('school_info.brand_color', '#c098bb'); ?>; /* Lighter shade for gradient */
        --accent-light-bg: <?php echo d_get('school_info.brand_color', '#8b5e83') . '0F'; ?>; /* Very light transparent tint */
    }

    /* --- Elegant Header Section --- */
    .elegant-header {
        background: linear-gradient(135deg, var(--brand-color-gradient-start) 0%, var(--brand-color-gradient-end) 100%);
        color: white;
        padding: 3rem 2.5rem 2rem 2.5rem; /* More generous padding */
        text-align: center;
        position: relative;
        z-index: 2;
        border-bottom-left-radius: 70% 30px; /* More pronounced curve */
        border-bottom-right-radius: 70% 30px; /* More pronounced curve */
        margin-bottom: -1.5rem; /* Creates the subtle overlay */
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2); /* Shadow for depth */
    }
    .elegant-logo-wrapper {
        background-color: rgba(255, 255, 255, 0.98);
        border-radius: 50%; /* Circular logo */
        padding: 10px;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem auto;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.2);
    }
    .elegant-logo {
        max-height: 70px;
        display: block;
    }
    .elegant-header h1 {
        font-family: 'Playfair Display', serif; /* Elegant serif for titles */
        font-size: 2.8rem;
        font-weight: 700;
        margin: 0 0 0.6rem 0;
        line-height: 1.1;
        letter-spacing: -0.5px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .elegant-header p {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0;
    }

    /* --- Main Content Area --- */
    .elegant-main-content {
        padding: 2.5rem;
        position: relative;
        z-index: 1;
    }

    /* Student Information Section */
    .student-detail-card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem 2.2rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    }
    .card-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--brand-color);
        margin-bottom: 1.5rem;
        text-align: center;
        position: relative;
        padding-bottom: 0.7rem;
    }
    .card-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 2px;
        background-color: var(--accent-light-bg);
        border-radius: 1px;
    }
    .elegant-student-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem 2.5rem;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .info-label {
        font-weight: 700;
        color: #4a5568;
        display: block;
        margin-bottom: 0.2rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-value {
        color: #333333;
    }

    /* Grades Table */
    .grades-section {
        margin-bottom: 2.5rem;
    }
    .elegant-grades-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden; /* Ensures rounded corners */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    .elegant-grades-table th, .elegant-grades-table td {
        padding: 12px 18px; /* Slightly more padding for elegance */
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.92rem;
    }
    .elegant-grades-table thead th {
        background-color: var(--brand-color);
        color: white;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-size: 0.8rem;
    }
    .elegant-grades-table tbody tr:last-child td {
        border-bottom: none;
    }
    .elegant-grades-table tbody tr:hover {
        background-color: var(--accent-light-bg);
    }
    .elegant-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
        color: #444;
    }

    /* Comments & Signatures */
    .elegant-footer-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2.5rem; /* More space between sections */
        padding-top: 2rem;
        border-top: 1px solid #e0e0e0; /* Solid line for classic feel */
    }
    .comment-card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .comment-card h5 {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--brand-color);
        margin: 0 0 0.8rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--accent-light-bg);
    }
    .comment-card p {
        font-size: 0.9rem;
        font-style: italic;
        color: #555555;
        margin: 0;
        line-height: 1.7;
    }

    .signature-area {
        text-align: center;
        padding-top: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-end; /* Push signature to bottom if comments are long */
    }
    .signature-image {
        max-height: 60px;
        margin-bottom: 0.5rem;
    }
    /* IMPORTANT: Kept signature font styles as requested */
    .signature-text {
        font-family: 'Great Vibes', cursive;
        font-size: 3rem; /* Larger and more prominent */
        color: #000033;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signature-line {
        border-top: 1px solid #b0b0b0;
        margin-top: 0.6rem;
    }
    .signature-title {
        margin-top: 0.7rem;
        font-size: 0.9rem;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
    }
</style>

<div class="elegant-container">
    <header class="elegant-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <div class="elegant-logo-wrapper">
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="elegant-logo">
            </div>
        <?php endif; ?>
        <h1><?php echo d_get('school_info.name'); ?></h1>
        <p><?php echo d_get('school_info.address'); ?></p>
    </header>

    <main class="elegant-main-content">
        <section class="student-detail-card">
            <h4 class="card-title">Academic Progress Report</h4>
            <div class="elegant-student-info-grid">
                <div>
                    <span class="info-label">Student Name</span>
                    <span class="info-value"><?php echo d_get('student_info.full_name'); ?></span>
                </div>
                <div>
                    <span class="info-label">Student ID</span>
                    <span class="info-value"><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span>
                </div>
                <div>
                    <span class="info-label">Class Level</span>
                    <span class="info-value"><?php echo d_get('student_info.class'); ?></span>
                </div>
                <div>
                    <span class="info-label">Reporting Period</span>
                    <span class="info-value"><?php echo d_get('academic_period'); ?></span>
                </div>
            </div>
        </section>

        <section class="grades-section">
            <h4 class="card-title">Grades Overview</h4>
            <table class="elegant-grades-table">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2rem; color: #777;">No grade data available for this period.</td></tr>
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
        </section>

        <div class="elegant-footer-layout">
            <?php
                $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
                $grades_comments = json_decode($grades_json, true);
                $teacher_comment = $grades_comments['teacher_comment'] ?? '';
                $principal_comment = $grades_comments['principal_comment'] ?? '';
            ?>
            <div class="comment-card">
                <?php if (!empty($teacher_comment)): ?>
                    <h5 style="<?php echo empty($principal_comment) ? '' : 'margin-bottom: 1.5rem;'; ?>">Teacher's Remarks</h5>
                    <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
                <?php endif; ?>
                <?php if (!empty($principal_comment)): ?>
                    <h5 style="<?php echo empty($teacher_comment) ? '' : 'margin-top: 2rem;'; ?>">Principal's Remarks</h5>
                    <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                <?php endif; ?>
            </div>
            
            <div class="signature-area">
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
    </main>
</div>