<?php
// Template: Luminous Glass Design
// A beautiful, attractive, and space-efficient design using a modern "Glassmorphism" effect.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .luminous-container {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f8ff;
        color: #1c2b4e; /* Deep navy text for contrast */
        max-width: 850px;
        margin: 2rem auto;
        border-radius: 20px; /* Softer, larger radius */
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden; /* Crucial for background effect */
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#6a11cb'); ?>; /* Default to a vibrant purple */
    }

    /* --- Abstract Gradient Background --- */
    .luminous-background {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 0;
    }
    .luminous-background .shape {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px); /* Creates the soft, blended effect */
    }
    .shape1 { width: 300px; height: 300px; background: rgba(106, 17, 203, 0.2); top: -50px; left: -100px; }
    .shape2 { width: 250px; height: 250px; background: rgba(37, 117, 252, 0.2); bottom: -80px; right: -80px; }
    .shape3 { width: 200px; height: 200px; background: rgba(252, 37, 104, 0.15); bottom: 150px; left: 50px; }


    /* --- Main Content Area --- */
    .luminous-main-content {
        position: relative;
        z-index: 1;
        padding: 2rem;
    }

    /* --- "Frosted Glass" Panel Style --- */
    .frosted-panel {
        background: rgba(255, 255, 255, 0.6); /* Semi-transparent white */
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px); /* For Safari compatibility */
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        padding: 1.5rem;
    }

    /* --- Header & Student Info (Combined Panel) --- */
    .header-info-panel {
        margin-bottom: 2rem;
    }
    .school-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }
    .school-logo {
        max-height: 55px;
        background: white;
        border-radius: 10px;
        padding: 5px;
    }
    .school-name h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--brand-color);
        margin: 0;
        line-height: 1.1;
    }
    .school-name p {
        font-size: 0.85rem;
        color: #5a6789;
        margin: 0;
    }
    .student-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.8rem 1.5rem;
        font-size: 0.9rem;
    }
    .student-info-grid strong {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #5a6789;
        margin-bottom: 2px;
    }
    .student-info-grid span {
        font-weight: 600;
    }

    /* --- Section Title --- */
    .section-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--brand-color);
        margin-bottom: 1.5rem;
        text-align: center;
    }

    /* --- Grades Table --- */
    .grades-table-wrapper {
        margin-bottom: 2rem;
    }
    .luminous-grades-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.8); /* More opaque for readability */
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
    }
    .luminous-grades-table th, .luminous-grades-table td {
        padding: 12px 15px; /* Compact padding */
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        font-size: 0.9rem;
    }
    .luminous-grades-table thead th {
        background-color: var(--brand-color);
        color: white;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .luminous-grades-table tbody tr:last-child td {
        border-bottom: none;
    }
    .luminous-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* --- Footer (Comments & Signature) --- */
    .luminous-footer {
        display: grid;
        grid-template-columns: 2fr 1fr; /* 2/3 for comments, 1/3 for signature */
        gap: 2rem;
        align-items: stretch;
    }
    .comment-panel h5 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--brand-color);
        margin: 0 0 0.8rem 0;
    }
    .comment-panel p {
        font-size: 0.85rem;
        color: #3b4a74;
        margin: 0 0 1rem 0;
        line-height: 1.7;
    }
    .signature-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    .signature-image {
        max-height: 50px;
        margin-bottom: 0.5rem;
    }
    .signature-text { /* Kept as requested */
        font-family: 'Great Vibes', cursive;
        font-size: 2.5rem;
        color: #000033;
        min-height: 50px;
    }
    .signature-line {
        border-top: 1px solid rgba(0, 0, 0, 0.2);
        margin-top: 0.5rem;
        width: 100%;
    }
    .signature-title {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #5a6789;
    }
</style>

<div class="luminous-container">
    <div class="luminous-background">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <main class="luminous-main-content">
        <section class="frosted-panel header-info-panel">
            <div class="school-header">
                <?php if (d_get('school_info.logo_url')): ?>
                    <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo">
                <?php endif; ?>
                <div class="school-name">
                    <h1><?php echo d_get('school_info.name'); ?></h1>
                    <p><?php echo d_get('school_info.address'); ?></p>
                </div>
            </div>
            <div class="student-info-grid">
                <div><strong>Student Name:</strong> <span><?php echo d_get('student_info.full_name'); ?></span></div>
                <div><strong>Student ID:</strong> <span><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span></div>
                <div><strong>Class Level:</strong> <span><?php echo d_get('student_info.class'); ?></span></div>
                <div><strong>Period:</strong> <span><?php echo d_get('academic_period'); ?></span></div>
            </div>
        </section>

        <section class="grades-table-wrapper">
            <h3 class="section-title">Performance Overview</h3>
            <table class="luminous-grades-table">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2.5rem;">No grade data available for this period.</td></tr>
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

        <footer class="luminous-footer">
            <div class="frosted-panel comment-panel">
                 <?php
                    $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
                    $grades_comments = json_decode($grades_json, true);
                    $teacher_comment = $grades_comments['teacher_comment'] ?? '';
                    $principal_comment = $grades_comments['principal_comment'] ?? '';
                ?>
                <?php if (!empty($teacher_comment)): ?>
                    <div>
                        <h5>Teacher's Remarks</h5>
                        <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($principal_comment)): ?>
                    <div style="<?php echo empty($teacher_comment) ? '' : 'margin-top: 1.5rem;'; ?>">
                        <h5>Principal's Remarks</h5>
                        <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="frosted-panel signature-panel">
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
        </footer>
    </main>
</div>