<?php
// Template: Minimalist Design
// A clean, typography-focused design with an emphasis on readability and professionalism.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Great+Vibes&display=swap');

    /* --- Minimalist Report Card Styles --- */
    .minimalist-container {
        font-family: 'Inter', sans-serif;
        background-color: #ffffff;
        color: #2d3748; /* A softer black */
        padding: 2.5rem;
        border: 1px solid #e2e8f0;
        max-width: 800px;
        margin: 2rem auto;
    }

    /* --- Dynamic Accent Color --- */
    :root {
        --accent-color: <?php echo d_get('school_info.brand_color', '#3182ce'); ?>;
    }

    /* --- Header --- */
    .minimal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    .header-logo {
        max-height: 70px;
    }
    .header-info {
        text-align: right;
    }
    .header-info h2 {
        margin: 0 0 0.25rem 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a202c;
    }
    .header-info p {
        margin: 0;
        font-size: 0.9rem;
        color: #4a5568;
    }

    /* --- Report Title & Student Info --- */
    .report-title {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: var(--accent-color);
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .student-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem 2rem;
        margin-bottom: 2.5rem;
        font-size: 0.95rem;
    }
    .student-info-grid p {
        margin: 0;
        padding: 0.25rem 0;
    }
    .student-info-grid strong {
        color: #4a5568;
        font-weight: 500;
    }

    /* --- Grades Table --- */
    .minimal-grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2.5rem;
    }
    .minimal-grades-table th, .minimal-grades-table td {
        padding: 14px 10px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }
    .minimal-grades-table thead th {
        font-size: 0.9rem;
        font-weight: 700;
        color: #2d3748;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--accent-color);
    }
    .minimal-grades-table tbody td {
        font-size: 1rem;
    }
    .minimal-grades-table .subject-name {
        font-weight: 500;
        color: #1a202c;
    }
    .minimal-grades-table .grade-value {
        text-align: center;
        font-weight: 500;
    }
    
    /* --- Comments --- */
    .comments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    .comment-block {
        border-left: 3px solid var(--accent-color);
        padding-left: 1.25rem;
    }
    .comment-block h5 {
        margin: 0 0 0.5rem 0;
        font-weight: 700;
        font-size: 1rem;
    }
    .comment-block p {
        margin: 0;
        font-size: 0.9rem;
        color: #4a5568;
        line-height: 1.6;
    }

    /* --- Signature Area --- */
    .minimal-footer {
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
        text-align: right; /* Places signature to the right */
    }
    .signature-area {
        display: inline-block;
        width: 250px;
        text-align: center;
    }
    .signature-image {
        max-height: 45px;
        margin-bottom: 0.5rem;
    }
    /* IMPORTANT: Kept signature font styles as requested */
    .signature-text {
        font-family: 'Great Vibes', cursive;
        font-size: 2.25rem;
        color: #000033;
        min-height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signature-line {
        border-top: 1px solid #718096;
        margin-top: 0.5rem;
    }
    .signature-title {
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: #718096;
    }

</style>

<div class="minimalist-container">

    <header class="minimal-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="header-logo">
        <?php endif; ?>
        <div class="header-info">
            <h2><?php echo d_get('school_info.name'); ?></h2>
            <p><?php echo d_get('school_info.address'); ?></p>
        </div>
    </header>

    <main>
        <h3 class="report-title">ACADEMIC PROGRESS REPORT</h3>

        <section class="student-info-grid">
            <p><strong>Student Name:</strong> <?php echo d_get('student_info.full_name'); ?></p>
            <p><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
            <p><strong>Class Level:</strong> <?php echo d_get('student_info.class'); ?></p>
            <p><strong>Reporting Period:</strong> <?php echo d_get('academic_period'); ?></p>
        </section>

        <section>
            <table class="minimal-grades-table">
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
                                <td class="subject-name"><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                                <?php
                                    $grades = json_decode($grade_row['grades'], true);
                                    foreach ($data['report_structure']['columns'] as $column):
                                        $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                                ?>
                                    <td class="grade-value"><?php echo htmlspecialchars($grades[$key] ?? '–'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <?php
            $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
            $grades_comments = json_decode($grades_json, true);
            $teacher_comment = $grades_comments['teacher_comment'] ?? '';
            $principal_comment = $grades_comments['principal_comment'] ?? '';
        ?>
        <?php if (!empty($teacher_comment) || !empty($principal_comment)): ?>
        <section class="comments-grid">
            <?php if (!empty($teacher_comment)): ?>
                <div class="comment-block">
                    <h5>Teacher's Comment</h5>
                    <p><?php echo htmlspecialchars($teacher_comment); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($principal_comment)): ?>
                <div class="comment-block">
                    <h5>Principal's Comment</h5>
                    <p><?php echo htmlspecialchars($principal_comment); ?></p>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </main>

    <footer class="minimal-footer">
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

</div>