<?php
// Template: Modern Redesign
// A clean, professional, and modern take on the report card.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Great+Vibes&display=swap');

    /* --- Modern Report Card Styles --- */
    :root {
        /* Dynamically set the primary color from school settings */
        --primary-color: <?php echo d_get('school_info.brand_color', '#4a90e2'); ?>;
        --background-color: #f7f9fc;
        --card-background: #ffffff;
        --text-color: #333333;
        --header-text-color: #ffffff;
        --border-color: #e0e0e0;
    }

    .modern-report-card {
        font-family: 'Poppins', sans-serif;
        background-color: var(--background-color);
        color: var(--text-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        max-width: 800px;
        margin: 2rem auto;
    }

    /* --- Header Section --- */
    .modern-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        background-color: var(--primary-color);
        color: var(--header-text-color);
        padding: 1.5rem 2rem;
        margin: -2rem -2rem 2rem -2rem; /* Extend to edges */
        border-radius: 12px 12px 0 0;
    }
    .school-logo-modern {
        max-height: 80px;
        background-color: white;
        border-radius: 50%;
        padding: 5px;
    }
    .school-info h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
    }
    .school-info p {
        margin: 0;
        opacity: 0.9;
    }

    /* --- Student Details --- */
    .student-details-card {
        background-color: var(--card-background);
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }
    .student-details-card h4 {
        margin-bottom: 1rem;
        font-weight: 600;
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 0.5rem;
        display: inline-block;
    }
    .student-info p {
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    /* --- Grades Table --- */
    .grades-table-modern {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }
    .grades-table-modern th, .grades-table-modern td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .grades-table-modern thead th {
        background-color: var(--primary-color);
        color: var(--header-text-color);
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
    }
    .grades-table-modern thead th:first-child {
        text-align: left;
    }
    .grades-table-modern tbody tr:nth-of-type(even) {
        background-color: #f8f9fa;
    }
    .grades-table-modern tbody tr:hover {
        background-color: #e9ecef;
    }
    .grades-table-modern td:not(:first-child) {
        text-align: center;
    }

    /* --- Comments Section --- */
    .comments-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .comment-box {
        background-color: var(--card-background);
        padding: 1.5rem;
        border-left: 4px solid var(--primary-color);
        border-radius: 0 8px 8px 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    .comment-box h5 {
        font-weight: 600;
        color: var(--primary-color);
        margin-top: 0;
        margin-bottom: 0.5rem;
    }
    .comment-box p {
        font-style: italic;
        color: #555;
        margin-bottom: 0;
        font-size: 0.9rem;
    }

    /* --- Footer & Signatures --- */
    .modern-footer {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    .signature-block {
        display: inline-block;
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
        border-top: 1px solid #333;
        margin: 0.5rem auto;
        width: 80%;
    }
    .signature-title {
        font-size: 0.9rem;
        color: #666;
    }
</style>

<div class="modern-report-card">

    <header class="modern-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-modern">
        <?php endif; ?>
        <div class="school-info">
            <h1><?php echo d_get('school_info.name'); ?></h1>
            <p><?php echo d_get('school_info.address'); ?></p>
        </div>
    </header>

    <section class="student-details-card">
        <h4>Student Progress Report</h4>
        <div class="row student-info">
            <div class="col-6">
                <p><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></p>
                <p><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
            </div>
            <div class="col-6 text-end">
                <p><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
                <p><strong>Period:</strong> <?php echo d_get('academic_period'); ?></p>
            </div>
        </div>
    </section>

    <section class="grades-section">
        <table class="table grades-table-modern">
            <thead>
                <tr>
                    <th>Subject</th>
                    <?php foreach ($data['report_structure']['columns'] as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['grades_data'])): ?>
                    <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No grade data available.</td></tr>
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

    <?php
        $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
        $grades_comments = json_decode($grades_json, true);
        $teacher_comment = $grades_comments['teacher_comment'] ?? '';
        $principal_comment = $grades_comments['principal_comment'] ?? '';
    ?>

    <?php if (!empty($teacher_comment) || !empty($principal_comment)): ?>
    <section class="comments-section">
        <?php if (!empty($teacher_comment)): ?>
            <div class="comment-box">
                <h5>Teacher's Comment</h5>
                <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($principal_comment)): ?>
            <div class="comment-box">
                <h5>Principal's Comment</h5>
                <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <footer class="modern-footer">
        <div class="signature-block">
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