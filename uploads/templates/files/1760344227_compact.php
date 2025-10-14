<?php
// Template: Compact Sidebar Design
// A space-efficient, two-column layout ideal for reports with many subjects.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .compact-container {
        font-family: 'Lato', sans-serif;
        display: flex;
        max-width: 900px;
        min-height: 1122px; /* A4 paper height approximation */
        margin: 2rem auto;
        background: #fff;
        box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#1E88E5'); ?>;
        --brand-color-light: <?php echo d_get('school_info.brand_color', '#1E88E5') . '15'; ?>; /* ~8% opacity */
    }

    /* --- Sidebar Column --- */
    .compact-sidebar {
        flex: 0 0 260px; /* Fixed width for the sidebar */
        background-color: #f4f7fa;
        padding: 1.5rem;
        color: #334;
        display: flex;
        flex-direction: column;
    }
    .sidebar-school-info {
        text-align: center;
        border-bottom: 1px solid #d8e0e8;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .sidebar-logo {
        max-width: 80px;
        margin-bottom: 0.75rem;
    }
    .sidebar-school-info h2 {
        font-family: 'Lato', sans-serif;
        font-weight: 900;
        font-size: 1.25rem;
        color: var(--brand-color);
        margin: 0 0 0.25rem 0;
    }
    .sidebar-school-info p {
        font-size: 0.8rem;
        line-height: 1.4;
        color: #6c757d;
        margin: 0;
    }
    
    /* Student Details in Sidebar */
    .sidebar-student-info h3 {
        font-weight: 700;
        font-size: 1rem;
        color: #334;
        border-bottom: 2px solid var(--brand-color);
        padding-bottom: 0.4rem;
        margin-bottom: 1rem;
    }
    .student-detail-item {
        margin-bottom: 0.6rem;
        font-size: 0.9rem;
    }
    .student-detail-item strong {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 700;
    }

    /* --- Main Content Column --- */
    .compact-main-content {
        flex: 1; /* Takes up the remaining space */
        padding: 1.5rem 2rem;
        display: flex;
        flex-direction: column;
    }
    .main-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .main-header h1 {
        font-size: 1.8rem;
        font-weight: 900;
        color: #212529;
        margin: 0;
    }
    .main-header p {
        font-size: 1rem;
        font-weight: 700;
        background-color: var(--brand-color-light);
        color: var(--brand-color);
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        margin: 0;
    }

    /* Space-efficient Grades Table */
    .grades-section {
        flex-grow: 1; /* Allows this section to fill available space */
    }
    .compact-grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    .compact-grades-table th, .compact-grades-table td {
        padding: 8px 12px; /* Reduced padding */
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9rem; /* Slightly smaller font */
    }
    .compact-grades-table thead th {
        background-color: var(--brand-color-light);
        font-weight: 700;
        color: var(--brand-color);
    }
    .compact-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 700;
    }

    /* Footer: Comments and Signature Side-by-Side */
    .compact-footer {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem; /* Reduced top margin */
        padding-top: 1rem;
        border-top: 1px solid #e0e0e0;
        align-items: flex-end;
    }
    .footer-comments {
        flex: 1; /* Takes up available space */
    }
    .footer-comments h5 {
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: #212529;
    }
    .footer-comments p {
        font-size: 0.85rem;
        margin: 0 0 0.8rem 0;
        color: #495057;
        font-style: italic;
    }

    /* Compact Signature Block */
    .footer-signature {
        flex: 0 0 220px; /* Fixed width for signature block */
        text-align: center;
    }
    .signature-image {
        max-height: 40px; /* Reduced size */
        margin-bottom: 0.25rem;
    }
    .signature-text { /* Kept as requested */
        font-family: 'Great Vibes', cursive;
        font-size: 2rem; /* Reduced size */
        color: #000033;
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signature-line {
        border-top: 1px solid #adb5bd;
        margin-top: 0.25rem;
    }
    .signature-title {
        margin-top: 0.4rem;
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>

<div class="compact-container">
    <aside class="compact-sidebar">
        <div class="sidebar-school-info">
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="sidebar-logo">
            <?php endif; ?>
            <h2><?php echo d_get('school_info.name'); ?></h2>
            <p><?php echo d_get('school_info.address'); ?></p>
        </div>
        
        <div class="sidebar-student-info">
            <h3>Student Profile</h3>
            <div class="student-detail-item">
                <strong>Student Name</strong>
                <?php echo d_get('student_info.full_name'); ?>
            </div>
            <div class="student-detail-item">
                <strong>Student ID</strong>
                <?php echo d_get('student_info.student_id_number', 'N/A'); ?>
            </div>
            <div class="student-detail-item">
                <strong>Class</strong>
                <?php echo d_get('student_info.class'); ?>
            </div>
        </div>
    </aside>

    <main class="compact-main-content">
        <header class="main-header">
            <h1>Academic Report</h1>
            <p><?php echo d_get('academic_period'); ?></p>
        </header>

        <section class="grades-section">
            <table class="compact-grades-table">
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
        </section>

        <footer class="compact-footer">
            <div class="footer-comments">
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
                    <div>
                        <h5>Principal's Comment</h5>
                        <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer-signature">
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