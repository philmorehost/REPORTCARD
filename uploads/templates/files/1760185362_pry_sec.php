<?php
// Template 3: Comprehensive
// This template is designed to display all possible dynamic fields for debugging and full-featured reports.
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Great+Vibes&display=swap');
    .comprehensive-container {
        font-family: 'Roboto', sans-serif;
        border: 2px solid #000;
        padding: 1.5rem 2rem; /* Reduced vertical padding */
        background-color: #f8f9fa;
    }
    .comp-header {
        text-align: center;
        margin-bottom: 1.5rem; /* Reduced margin */
    }
    .school-logo-comp {
        max-height: 100px;
        margin-bottom: 1rem;
    }
    .comp-grades-table {
        margin-top: 1.5rem; /* Reduced margin */
    }
    .comp-comments {
        margin-top: 1.5rem; /* Reduced margin */
    }
    .comp-comments p {
        font-size: 0.9rem;
    }
    .comp-signatures {
        margin-top: 2.5rem; /* Reduced margin */
        display: flex;
        justify-content: center;
    }
    .signature-block {
        text-align: center;
        width: 45%;
    }
    .signature-image {
        max-height: 50px;
        margin-bottom: 0.5rem;
    }
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
        border-top: 1px solid #000;
        margin-top: 0.5rem;
    }
</style>

<div class="comprehensive-container">
    <header class="comp-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-comp">
        <?php endif; ?>
        <h1 class="fw-bold" style="color: <?php echo d_get('school_info.brand_color', '#000'); ?>;"><?php echo d_get('school_info.name'); ?></h1>
        <p class="lead"><?php echo d_get('school_info.address'); ?></p>
        <hr>
        <h4>ACADEMIC REPORT</h4>
    </header>

    <div class="row">
        <div class="col-6">
            <p><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></p>
            <p><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
        </div>
        <div class="col-6 text-end">
            <p><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
            <p><strong>Period:</strong> <?php echo d_get('academic_period'); ?></p>
        </div>
    </div>

    <table class="table table-bordered comp-grades-table">
        <thead class="table-dark">
            <tr>
                <th>Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
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
                            <td class="text-center"><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
        $grades_json = $data['grades_data'][0]['grades'] ?? '[]';
        $grades_comments = json_decode($grades_json, true);
        $teacher_comment = $grades_comments['teacher_comment'] ?? '';
        $principal_comment = $grades_comments['principal_comment'] ?? '';
    ?>

    <?php if (!empty($teacher_comment) || !empty($principal_comment)): ?>
    <div class="row">
        <div class="col-6">
            <?php if (!empty($teacher_comment)): ?>
            <div class="comp-comments">
                <p class="mb-0"><strong>Teacher's Comment:</strong> <?php echo htmlspecialchars($teacher_comment); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-6">
            <?php if (!empty($principal_comment)): ?>
            <div class="comp-comments">
                <p class="mb-0"><strong>Principal's Comment:</strong> <?php echo htmlspecialchars($principal_comment); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="comp-signatures">
        <div class="signature-block">
             <?php
                // ROBUST SIGNATURE LOGIC
                $principal_sig = d_get('principal_signature', null, false);
                $signature_content = '<div class="signature-text">N/A</div>'; // Default content

                if (is_array($principal_sig) && !empty($principal_sig['signature_data'])) {
                    if ($principal_sig['signature_type'] === 'image') {
                        $signature_content = '<img src="/' . htmlspecialchars($principal_sig['signature_data']) . '" alt="Principal Signature" class="signature-image">';
                    } else { // Assumes text-based signature
                        $signature_content = '<div class="signature-text">' . htmlspecialchars($principal_sig['signature_data']) . '</div>';
                    }
                }
                echo $signature_content;
            ?>
            <div class="signature-line"></div>
            <p>Principal's Signature</p>
        </div>
    </div>
</div>