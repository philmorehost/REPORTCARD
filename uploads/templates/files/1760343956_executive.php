<?php
// Template: Executive Design
// A sophisticated and professional report card with structured sections and classic typography.
?>
<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600;700&family=Great+Vibes&display=swap');

    /* --- Executive Report Card Styles --- */
    .executive-container {
        font-family: 'Open Sans', sans-serif; /* Primary font for body text */
        background-color: #f8f9fa; /* Light grey background */
        color: #343a40; /* Dark grey text */
        border: 1px solid #e9ecef;
        padding: 3rem;
        max-width: 850px;
        margin: 2rem auto;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    /* Subtle background texture */
    .executive-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cuw3MyLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBmaWxsPSIjRkFGRkIyIiBkPSJNMjkuOTg0IDBjLjU2OCAwIDEuMDk5LjA2NiAxLjU5My4xOTdsLTIuMDgyIDQuNjA4Yy0uNDIzLjE0Mi0uODI3LjI2My0xLjE3OC4zOTYtLjI1My4xMDItLjQ3Mi4yMTEtLjY1OC4zMDZsLS4xNTcuMDc1LTkuMzgzIDQuNTE1LTQuNTg1IDktNC41ODUtOS05LjM4My00LjUxNS0uMTU3LS4wNzVjLS4xODYtLjA5NS0uNDA1LS4yMDQtLjY1OC0uMzA2LS4zNTItLjEzMy0uNzU1LS4yNTQtMS4xNzgtLjM5NkwuNjk2LjE5N2MxLjEzOS0uMjIyIDIuMzUxLS4zNTUgMy41NzYtLjM1NUwyOS45ODQgMHptLTguMzQxIDkuNTIxbC0zLjk3IDcuNzQzLTcuNzQyIDMuOTcgMy45Ny03Ljc0MyA3Ljc0Mi0zLjk3em0xNi41NzcgMGwyLjQ1NSAzLjkxNiA0LjU5My00LjU5My0zLjkxNi0yLjQ1NkwyOS45ODQgMHoiIG9wYWNpdHk9Ii42IiAvPjxwYXRoIGQ9Ik0wIDYwYzYuODczLTIxLjI4MiAxOS4xMjctMzEuNTIgMzcuNTgyLTMxLjUyIDQuMjY2IDAgNy42NDggMS42NTIgOS42ODcgNC45NzVsNy4yMzkgMy45MjItMi41ODYtNS4xMDgtNC41ODUtOS05LjM4My00LjUxNS0uMTU3LS4wNzVjLS4xODYtLjA5NS0uNDA1LS4yMDQtLjY1OC0uMzA2LS4zNTItLjEzMy0uNzU1LS4yNTQtMS4xNzgtLjM5NkwuNjk2LjE5N2MxLjEzOS0uMjIyIDIuMzUxLS4zNTUgMy41NzYtLjM1NUwyOS45ODQgMHoiIGZpbGw9IiNGRkZGRkYiIG9wYWNpdHk9Ii4wMyIvPjwvZz48L3N2Zz4=');
        opacity: 0.2; /* Adjust opacity as needed */
        z-index: 0;
    }

    /* Dynamically set the primary color from school settings */
    :root {
        --primary-color: <?php echo d_get('school_info.brand_color', '#0056b3'); ?>;
    }

    .executive-header {
        text-align: center;
        margin-bottom: 2.5rem;
        position: relative;
        z-index: 1; /* Ensure header content is above texture */
    }
    .executive-logo {
        max-height: 90px;
        margin-bottom: 1rem;
    }
    .executive-header h1 {
        font-family: 'Merriweather', serif; /* Serif font for main title */
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
    }
    .executive-header p {
        font-size: 1rem;
        color: #6c757d;
        margin: 0;
    }
    .executive-header hr {
        border-color: #e9ecef;
        margin: 1.5rem auto;
        width: 80%;
    }

    /* Section styling */
    .executive-section {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        position: relative;
        z-index: 1; /* Ensure section content is above texture */
    }
    .executive-section-title {
        font-family: 'Merriweather', serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-top: 0;
        margin-bottom: 1.2rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-color);
        display: inline-block; /* Makes border-bottom only as wide as text */
    }

    /* Student Info Grid */
    .executive-student-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 0.8rem 2rem;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .executive-student-info strong {
        color: #495057;
        font-weight: 600;
    }

    /* Grades Table */
    .executive-grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1.5rem;
    }
    .executive-grades-table th, .executive-grades-table td {
        padding: 12px 18px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }
    .executive-grades-table thead th {
        background-color: var(--primary-color);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: none; /* No double border with tbody */
    }
    .executive-grades-table tbody td {
        font-size: 0.95rem;
    }
    .executive-grades-table td:not(:first-child) {
        text-align: center;
    }
    .executive-grades-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Comments */
    .executive-comments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }
    .executive-comment-box {
        padding: 1.2rem;
        background-color: #f8f9fa;
        border-left: 4px solid var(--primary-color);
        border-radius: 4px;
    }
    .executive-comment-box h5 {
        font-weight: 700;
        color: var(--primary-color);
        margin-top: 0;
        margin-bottom: 0.6rem;
        font-size: 1rem;
    }
    .executive-comment-box p {
        font-style: italic;
        color: #495057;
        margin-bottom: 0;
        line-height: 1.6;
        font-size: 0.9rem;
    }

    /* Signatures */
    .executive-signatures {
        margin-top: 3rem;
        text-align: right;
        position: relative;
        z-index: 1; /* Ensure signature content is above texture */
    }
    .executive-signature-block {
        display: inline-block;
        width: 280px;
        text-align: center;
    }
    .executive-signature-image {
        max-height: 60px;
        margin-bottom: 0.5rem;
    }
    /* IMPORTANT: Kept signature font styles as requested */
    .executive-signature-text {
        font-family: 'Great Vibes', cursive;
        font-size: 2.8rem;
        color: #000033;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .executive-signature-line {
        border-top: 1px solid #adb5bd;
        margin-top: 0.5rem;
    }
    .executive-signature-title {
        margin-top: 0.6rem;
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="executive-container">
    <header class="executive-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="executive-logo">
        <?php endif; ?>
        <h1><?php echo d_get('school_info.name'); ?></h1>
        <p><?php echo d_get('school_info.address'); ?></p>
        <hr>
        <p style="font-size: 1.2rem; font-weight: 600; color: #495057;">OFFICIAL ACADEMIC REPORT</p>
    </header>

    <section class="executive-section">
        <h3 class="executive-section-title">Student Information</h3>
        <div class="executive-student-info">
            <p><strong>Student Name:</strong> <?php echo d_get('student_info.full_name'); ?></p>
            <p><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
            <p><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
            <p><strong>Academic Period:</strong> <?php echo d_get('academic_period'); ?></p>
        </div>
    </section>

    <section class="executive-section">
        <h3 class="executive-section-title">Grades Overview</h3>
        <table class="executive-grades-table">
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
                    <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No grade data available for this period.</td></tr>
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
    <section class="executive-section">
        <h3 class="executive-section-title">Comments</h3>
        <div class="executive-comments-grid">
            <?php if (!empty($teacher_comment)): ?>
                <div class="executive-comment-box">
                    <h5>Teacher's Remarks</h5>
                    <p>"<?php echo htmlspecialchars($teacher_comment); ?>"</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($principal_comment)): ?>
                <div class="executive-comment-box">
                    <h5>Principal's Remarks</h5>
                    <p>"<?php echo htmlspecialchars($principal_comment); ?>"</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="executive-signatures">
        <div class="executive-signature-block">
            <?php
                // ROBUST SIGNATURE LOGIC (UNCHANGED)
                $principal_sig = d_get('principal_signature', null, false);
                $signature_content = '<div class="executive-signature-text">Not Signed</div>';

                if (is_array($principal_sig) && !empty($principal_sig['signature_data'])) {
                    if ($principal_sig['signature_type'] === 'image') {
                        $signature_content = '<img src="/' . htmlspecialchars($principal_sig['signature_data']) . '" alt="Principal Signature" class="executive-signature-image">';
                    } else {
                        $signature_content = '<div class="executive-signature-text">' . htmlspecialchars($principal_sig['signature_data']) . '</div>';
                    }
                }
                echo $signature_content;
            ?>
            <div class="executive-signature-line"></div>
            <p class="executive-signature-title">Principal's Signature</p>
        </div>
    </div>
</div>