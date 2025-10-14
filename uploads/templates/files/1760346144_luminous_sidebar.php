<?php
// Template: Luminous Sidebar Design (Corrected)
// A beautiful and space-efficient fusion of the Glassmorphism aesthetic and a two-column sidebar layout.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Great+Vibes&display=swap');

    /* --- Core Styles --- */
    .luminous-sidebar-container {
        font-family: 'Poppins', sans-serif;
        background-color: #f6f9ff; /* Base background color */
        color: #1a294d; /* Deep navy text for high contrast */
        max-width: 950px;
        margin: 2rem auto;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.6);
        display: flex; /* Establishes the two-column layout */
    }

    /* Dynamically set the brand color */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#e91e63'); ?>; /* Default to a vibrant pink/magenta */
    }

    /* --- Abstract Gradient Background --- */
    .luminous-background-shapes {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 0;
    }
    .luminous-background-shapes .shape {
        position: absolute;
        border-radius: 50%;
        filter: blur(120px);
    }
    .shape1 { width: 350px; height: 350px; background: rgba(233, 30, 99, 0.15); top: -80px; left: -120px; }
    .shape2 { width: 300px; height: 300px; background: rgba(30, 136, 229, 0.15); bottom: -100px; right: -100px; }
    .shape3 { width: 250px; height: 250px; background: rgba(255, 193, 7, 0.1); top: 30%; right: 10%; }

    /* --- "Frosted Glass" Panel Style --- */
    .frosted-panel {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
    }

    /* --- Sidebar Column --- */
    .luminous-sidebar {
        flex: 0 0 280px; /* Fixed sidebar width */
        position: relative; /* Sits above background */
        z-index: 1;
        padding: 2rem;
        display: flex;
        flex-direction: column;
    }
    .sidebar-school-profile {
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .sidebar-logo {
        max-height: 80px;
        background-color: white;
        border-radius: 50%;
        padding: 8px;
        margin-bottom: 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .sidebar-school-profile h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--brand-color);
        margin: 0 0 0.3rem 0;
    }
    .sidebar-school-profile p {
        font-size: 0.8rem;
        line-height: 1.5;
        color: #3b4a74;
        margin: 0;
    }
    .sidebar-student-profile h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a294d;
        margin-bottom: 1.2rem;
    }
    .student-detail strong {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #5a6789;
        margin-bottom: 0.2rem;
    }
    .student-detail {
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    /* --- Main Content Area --- */
    .luminous-main {
        flex: 1; /* Takes remaining space */
        position: relative; /* Sits above background */
        z-index: 1;
        padding: 2rem;
        display: flex;
        flex-direction: column;
    }
    .main-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .main-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a294d;
        margin: 0 0 0.3rem 0;
    }
    .main-header p {
        font-size: 1rem;
        font-weight: 500;
        color: var(--brand-color);
    }
    .grades-table-panel {
        flex-grow: 1; /* Pushes footer down */
        margin-bottom: 2rem;
        padding: 0; /* Padding is handled by the table itself */
        border-radius: 15px;
        overflow: hidden; /* Important for table border radius */
    }
    .luminous-grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    .luminous-grades-table th, .luminous-grades-table td {
        padding: 10px 14px; /* Compact padding */
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        font-size: 0.88rem;
    }
    .luminous-grades-table thead th {
        background-color: rgba(255,255,255,0.3); /* Frosted header */
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--brand-color);
    }
    .luminous-grades-table tbody tr:last-child td {
        border-bottom: none;
    }
    .luminous-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 600;
    }

    /* Footer: Comments & Signature */
    .luminous-footer {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        align-items: stretch;
    }
    .comment-panel {
        border-radius: 15px; padding: 1.5rem;
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
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    .signature-image { max-height: 45px; margin-bottom: 0.5rem; }
    .signature-text {
        font-family: 'Great Vibes', cursive;
        font-size: 2.2rem;
        color: #000033;
        min-height: 45px;
        display: flex; /* FIX: Added */
        align-items: center; /* FIX: Added */
        justify-content: center; /* FIX: Added */
    }
    .signature-line { border-top: 1px solid rgba(0, 0, 0, 0.2); margin-top: 0.5rem; width: 100%; }
    .signature-title { margin-top: 0.5rem; font-size: 0.75rem; font-weight: 600; color: #5a6789; }
</style>

<div class="luminous-sidebar-container">
    <div class="luminous-background-shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <aside class="luminous-sidebar frosted-panel">
        <div class="sidebar-school-profile">
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="sidebar-logo">
            <?php endif; ?>
            <h2><?php echo d_get('school_info.name'); ?></h2>
            <p><?php echo d_get('school_info.address'); ?></p>
        </div>
        
        <div class="sidebar-student-profile">
            <h3>Student Profile</h3>
            <div class="student-detail">
                <strong>Student Name</strong>
                <span><?php echo d_get('student_info.full_name'); ?></span>
            </div>
            <div class="student-detail">
                <strong>Student ID</strong>
                <span><?php echo d_get('student_info.student_id_number', 'N/A'); ?></span>
            </div>
            <div class="student-detail">
                <strong>Class Level</strong>
                <span><?php echo d_get('student_info.class'); ?></span>
            </div>
        </div>
    </aside>

    <main class="luminous-main">
        <header class="main-header">
            <h1>Academic Report</h1>
            <p>Reporting Period: <?php echo d_get('academic_period'); ?></p>
        </header>

        <div class="grades-table-panel frosted-panel">
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
                        <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" style="text-align: center; padding: 2.5rem;">No grade data for this period.</td></tr>
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

        <footer class="luminous-footer">
            <div class="comment-panel frosted-panel">
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
            
            <div class="signature-panel frosted-panel">
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