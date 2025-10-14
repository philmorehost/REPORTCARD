<?php
// Template: Prestige Sidebar Design
// A sophisticated and bold redesign of the compact, two-column layout.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Source+Sans+Pro:wght@400;600;700&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .prestige-container {
        font-family: 'Source Sans Pro', sans-serif;
        display: flex;
        max-width: 900px;
        min-height: 1122px; /* A4 paper height approximation */
        margin: 2rem auto;
        background: #ffffff;
        box-shadow: 0 10px 35px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#2c3e50'); ?>;
        --brand-color-light-text: #ffffff;
        --brand-color-transparent: <?php echo d_get('school_info.brand_color', '#2c3e50') . '1A'; ?>; /* ~10% opacity */
    }

    /* --- Sidebar Column --- */
    .prestige-sidebar {
        flex: 0 0 270px; /* Slightly wider sidebar */
        background-color: var(--brand-color);
        color: var(--brand-color-light-text);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        /* Subtle texture */
        background-image: linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.03) 50%, rgba(255,255,255,0.03) 75%, transparent 75%, transparent);
        background-size: 30px 30px;
    }
    .sidebar-header {
        text-align: center;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .sidebar-logo-wrapper {
        background-color: rgba(255,255,255,0.95);
        border-radius: 50%;
        width: 90px;
        height: 90px;
        margin: 0 auto 1rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .sidebar-logo {
        max-width: 70px;
    }
    .sidebar-header h2 {
        font-family: 'Lora', serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 0.25rem 0;
        line-height: 1.2;
    }
    .sidebar-header p {
        font-size: 0.85rem;
        opacity: 0.8;
        margin: 0;
    }
    
    /* Student Details in Sidebar */
    .sidebar-student-profile {
        font-size: 0.95rem;
    }
    .sidebar-student-profile h3 {
        font-family: 'Lora', serif;
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 1.25rem;
        text-align: center;
    }
    .student-info-item {
        margin-bottom: 1rem;
    }
    .student-info-item strong {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
        margin-bottom: 0.2rem;
    }

    /* --- Main Content Column --- */
    .prestige-main {
        flex: 1;
        padding: 2rem 2.5rem;
        display: flex;
        flex-direction: column;
    }
    .main-content-header {
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
    .main-content-header h1 {
        font-family: 'Lora', serif;
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
    }
    .main-content-header p {
        font-size: 1.1rem;
        color: #718096;
        margin: 0;
    }

    /* Grades Table */
    .grades-wrapper {
        flex-grow: 1; /* Key for pushing footer down */
    }
    .prestige-grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    .prestige-grades-table th, .prestige-grades-table td {
        padding: 10px 14px; /* Compact padding */
        text-align: left;
        border-bottom: 1px solid #edf2f7;
        font-size: 0.9rem;
    }
    .prestige-grades-table thead {
        border-bottom: 2px solid var(--brand-color);
    }
    .prestige-grades-table thead th {
        font-weight: 700;
        color: #4a5568;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .prestige-grades-table tbody tr:hover {
        background-color: var(--brand-color-transparent);
    }
    .prestige-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* Footer: Comments and Signature Side-by-Side */
    .prestige-footer {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
        align-items: flex-end;
    }
    .footer-comments-section {
        flex: 1;
    }
    .footer-comments-section h5 {
        font-family: 'Lora', serif;
        font-size: 1rem;
        margin: 0 0 0.5rem 0;
        color: #2d3748;
    }
    .footer-comments-section p {
        font-size: 0.9rem;
        margin: 0 0 1rem 0;
        color: #4a5568;
        line-height: 1.6;
    }

    /* Signature Block */
    .footer-signature-section {
        flex: 0 0 240px;
        text-align: center;
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
        border-top: 1px solid #cbd5e0;
        margin-top: 0.5rem;
    }
    .signature-title {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #718096;
    }
</style>

<div class="prestige-container">
    <aside class="prestige-sidebar">
        <header class="sidebar-header">
            <?php if (d_get('school_info.logo_url')): ?>
                <div class="sidebar-logo-wrapper">
                    <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="sidebar-logo">
                </div>
            <?php endif; ?>
            <h2><?php echo d_get('school_info.name'); ?></h2>
            <p><?php echo d_get('school_info.address'); ?></p>
        </header>
        
        <section class="sidebar-student-profile">
            <h3>Student Profile</h3>
            <div class="student-info-item">
                <strong>Student Name</strong>
                <span><?php echo d_get('student_info.full_name'); ?></span>
            </div>
            <div class="student-info-item">
                <strong>Student ID</strong>
                <span><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span>
            </div>
            <div class="student-info-item">
                <strong>Class</strong>
                <span><?php echo d_get('student_info.class'); ?></span>
            </div>
        </section>
    </aside>

    <main class="prestige-main">
        <header class="main-content-header">
            <h1>Academic Report</h1>
            <p>Reporting Period: <?php echo d_get('academic_period'); ?></p>
        </header>

        <section class="grades-wrapper">
            <table class="prestige-grades-table">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2rem;">No grade data for this period.</td></tr>
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

        <footer class="prestige-footer">
            <div class="footer-comments-section">
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
                     <div>
                        <h5>Principal's Remarks</h5>
                        <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer-signature-section">
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