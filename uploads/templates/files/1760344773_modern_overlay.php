<?php
// Template: Modern Overlay Design
// A contemporary, space-efficient design with an integrated header and streamlined sections.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600;700;800&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .overlay-container {
        font-family: 'Urbanist', sans-serif;
        background-color: #f7faff; /* Very light blue-grey background */
        color: #2c3e50;
        max-width: 850px;
        margin: 2rem auto;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden; /* Crucial for header overlay effect */
        position: relative;
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#0a5286'); ?>;
        --brand-color-light: <?php echo d_get('school_info.brand_color', '#0a5286') . '1A'; ?>; /* 10% opacity */
    }

    /* --- Header Section --- */
    .overlay-header {
        background-color: var(--brand-color);
        color: white;
        padding: 2.5rem 2rem 1.5rem 2rem; /* Adjusted padding for subtle overlay */
        text-align: center;
        position: relative;
        z-index: 2; /* Ensures it sits above some main content elements */
        border-bottom-left-radius: 50% 20px; /* Modern curve effect */
        border-bottom-right-radius: 50% 20px; /* Modern curve effect */
        margin-bottom: -1rem; /* Creates the subtle overlay */
    }
    .overlay-logo {
        max-height: 80px;
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 1rem;
    }
    .overlay-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin: 0 0 0.4rem 0;
        line-height: 1.2;
    }
    .overlay-header p {
        font-size: 0.95rem;
        opacity: 0.9;
        margin: 0;
    }

    /* --- Main Content Area --- */
    .overlay-main-content {
        padding: 1.5rem 2rem 2.5rem 2rem;
        position: relative;
        z-index: 1; /* Below header */
    }

    /* Student Information Section */
    .student-info-section {
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.2rem 1.8rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    .student-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.7rem 2rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    .student-info-grid strong {
        font-weight: 700;
        color: #495057;
    }
    .info-item span {
        display: block;
        font-weight: 600;
        color: var(--brand-color); /* Highlight key info */
        font-size: 0.85rem;
        margin-bottom: 0.2rem;
    }

    /* Section Titles */
    .section-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--brand-color);
        margin-bottom: 1.5rem;
        text-align: center;
        position: relative;
        padding-bottom: 0.5rem;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background-color: var(--brand-color-light);
        border-radius: 2px;
    }

    /* Grades Table */
    .overlay-grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }
    .overlay-grades-table th, .overlay-grades-table td {
        padding: 10px 14px; /* Optimized padding */
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    .overlay-grades-table thead th {
        background-color: var(--brand-color-light);
        color: var(--brand-color);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .overlay-grades-table tbody tr:hover {
        background-color: #f0f3f7;
    }
    .overlay-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* Comments & Signatures */
    .overlay-footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.8rem;
        padding-top: 1.5rem;
        border-top: 1px dashed #ced4da; /* Dashed line for a modern touch */
    }
    .comment-area {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 1.2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .comment-area h5 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--brand-color);
        margin: 0 0 0.6rem 0;
    }
    .comment-area p {
        font-size: 0.85rem;
        font-style: italic;
        color: #495057;
        margin: 0;
        line-height: 1.6;
    }

    .signature-wrapper {
        text-align: center;
        padding-top: 0.5rem;
    }
    .signature-image {
        max-height: 50px;
        margin-bottom: 0.4rem;
    }
    /* IMPORTANT: Kept signature font styles as requested */
    .signature-text {
        font-family: 'Great Vibes', cursive;
        font-size: 2.5rem;
        color: #000033;
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signature-line {
        border-top: 1px solid #adb5bd;
        margin-top: 0.4rem;
    }
    .signature-title {
        margin-top: 0.6rem;
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="overlay-container">
    <header class="overlay-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="overlay-logo">
        <?php endif; ?>
        <h1><?php echo d_get('school_info.name'); ?></h1>
        <p><?php echo d_get('school_info.address'); ?></p>
    </header>

    <main class="overlay-main-content">
        <section class="student-info-section">
            <h4 class="section-title">Academic Report</h4>
            <div class="student-info-grid">
                <div class="info-item">
                    <span>Student Name</span>
                    <strong><?php echo d_get('student_info.full_name'); ?></strong>
                </div>
                <div class="info-item">
                    <span>Student ID</span>
                    <strong><?php echo d_get('student_info.student_id_number', 'N/A'); ?></strong>
                </div>
                <div class="info-item">
                    <span>Class Level</span>
                    <strong><?php echo d_get('student_info.class'); ?></strong>
                </div>
                <div class="info-item">
                    <span>Reporting Period</span>
                    <strong><?php echo d_get('academic_period'); ?></strong>
                </div>
            </div>
        </section>

        <section>
            <h4 class="section-title">Grades Overview</h4>
            <table class="overlay-grades-table">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2rem;">No grade data available for this period.</td></tr>
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

        <div class="overlay-footer-grid">
            <?php
                $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
                $grades_comments = json_decode($grades_json, true);
                $teacher_comment = $grades_comments['teacher_comment'] ?? '';
                $principal_comment = $grades_comments['principal_comment'] ?? '';
            ?>
            <div class="comment-area">
                <?php if (!empty($teacher_comment)): ?>
                    <h5 style="border-bottom: 2px solid var(--brand-color-light); padding-bottom: 0.4rem; margin-bottom: 1rem;">Teacher's Comment</h5>
                    <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
                <?php endif; ?>
                <?php if (!empty($principal_comment)): ?>
                    <h5 style="border-bottom: 2px solid var(--brand-color-light); padding-bottom: 0.4rem; margin-bottom: 1rem; <?php echo empty($teacher_comment) ? '' : 'margin-top: 1.5rem;'; ?>">Principal's Comment</h5>
                    <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                <?php endif; ?>
            </div>
            
            <div class="signature-wrapper">
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