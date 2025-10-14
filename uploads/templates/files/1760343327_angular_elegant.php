<?php
// Template: Geometric Design
// A dynamic and modern layout featuring angled elements and clean typography.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Great+Vibes&display=swap');

    /* --- Geometric Report Card Styles --- */
    .geometric-container {
        font-family: 'Montserrat', sans-serif;
        background-color: #fff;
        color: #334155;
        border-radius: 8px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden; /* Important for the angled header */
        max-width: 800px;
        margin: 2rem auto;
    }

    /* --- Dynamic Brand Color --- */
    :root {
        --brand-color: <?php echo d_get('school_info.brand_color', '#8B5CF6'); ?>;
        --brand-color-light: <?php echo d_get('school_info.brand_color', '#8B5CF6') . '1A'; ?>; /* 10% opacity */
    }

    /* --- Header --- */
    .geo-header {
        position: relative;
        padding: 2.5rem 2rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .geo-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--brand-color);
        transform: skewY(-3.5deg);
        transform-origin: top left;
        z-index: 1;
    }
    .geo-header > * {
        position: relative;
        z-index: 2; /* Ensure content is above the skewed background */
    }
    .geo-logo-wrapper {
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        padding: 8px;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .geo-logo {
        max-height: 80px;
        display: block;
    }
    .geo-school-info h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }
    .geo-school-info p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    /* --- Main Content Body --- */
    .geo-body {
        padding: 2rem;
    }

    /* --- Section Titles --- */
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--brand-color);
        margin-bottom: 1rem;
        border-bottom: 2px solid var(--brand-color-light);
        padding-bottom: 0.5rem;
    }
    
    /* --- Student Info --- */
    .geo-student-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem 2rem;
        margin-bottom: 2.5rem;
        font-size: 0.9rem;
    }
    .geo-student-info span {
        font-weight: 500;
        color: #64748B;
    }

    /* --- Grades Table --- */
    .geo-grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2.5rem;
    }
    .geo-grades-table th, .geo-grades-table td {
        padding: 12px 15px;
        text-align: left;
    }
    .geo-grades-table thead {
        background-color: var(--brand-color-light);
    }
    .geo-grades-table thead th {
        font-weight: 700;
        color: var(--brand-color);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .geo-grades-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: background-color 0.2s ease-in-out;
    }
    .geo-grades-table tbody tr:last-child {
        border-bottom: none;
    }
    .geo-grades-table tbody tr:hover {
        background-color: var(--brand-color-light);
    }
    .geo-grades-table td:not(:first-child) {
        text-align: center;
        font-weight: 500;
    }
    
    /* --- Comments & Footer --- */
    .geo-footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        align-items: flex-start;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    .comment-area h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
        margin: 0 0 0.5rem 0;
    }
    .comment-area p {
        font-size: 0.9rem;
        font-style: italic;
        color: #64748B;
        margin: 0;
        line-height: 1.6;
    }
    .signature-area {
        text-align: center;
        margin-top: 1rem;
    }
    .signature-image {
        max-height: 50px;
        margin-bottom: 0.5rem;
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
        border-top: 1px solid #cbd5e1;
        margin-top: 0.5rem;
    }
    .signature-title {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="geometric-container">
    <header class="geo-header">
        <?php if (d_get('school_info.logo_url')): ?>
        <div class="geo-logo-wrapper">
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="geo-logo">
        </div>
        <?php endif; ?>
        <div class="geo-school-info">
            <h1><?php echo d_get('school_info.name'); ?></h1>
            <p><?php echo d_get('school_info.address'); ?></p>
        </div>
    </header>

    <main class="geo-body">
        <section>
            <h4 class="section-title">Student Report</h4>
            <div class="geo-student-info">
                <div><span>Student:</span> <?php echo d_get('student_info.full_name'); ?></div>
                <div><span>Student ID:</span> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></div>
                <div><span>Class:</span> <?php echo d_get('student_info.class'); ?></div>
                <div><span>Period:</span> <?php echo d_get('academic_period'); ?></div>
            </div>
        </section>

        <section>
            <h4 class="section-title">Academic Performance</h4>
            <table class="geo-grades-table">
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
                                    <td><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <footer class="geo-footer-grid">
            <?php
                $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
                $grades_comments = json_decode($grades_json, true);
                $teacher_comment = $grades_comments['teacher_comment'] ?? '';
                $principal_comment = $grades_comments['principal_comment'] ?? '';
            ?>
            <div class="comment-area">
                <?php if (!empty($teacher_comment)): ?>
                    <div style="margin-bottom: 1.5rem;">
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
            
            <div class="signature-area">
                <?php
                    // ROBUST SIGNATURE LOGIC (UNCHANGED)
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