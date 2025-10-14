<?php
// Template 13: Secondary - Modern & Clean
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    .clean-container {
        font-family: 'Roboto', sans-serif;
        background-color: #fff;
        padding: 2.5rem;
    }
    .clean-header {
        text-align: right;
        margin-bottom: 3rem;
    }
    .clean-header h2 {
        font-weight: 700;
        color: #111;
    }
    .clean-header p {
        color: #777;
    }
    .student-info-clean {
        margin-bottom: 2rem;
    }
    .student-info-clean strong {
        display: block;
        font-size: 1rem;
        color: #888;
        font-weight: 400;
        margin-bottom: 0.25rem;
    }
     .student-info-clean span {
        font-size: 1.2rem;
        font-weight: 500;
        color: #111;
    }
    .clean-table {
        border-top: 2px solid #000;
    }
    .clean-table th {
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #555;
        border-bottom: 2px solid #000 !important;
    }
    .clean-table td {
        vertical-align: middle;
        font-weight: 400;
    }
    .final-grade {
        font-weight: 700 !important;
        font-size: 1.1rem;
    }
</style>

<div class="clean-container">
    <!-- Header -->
    <header class="clean-header">
        <h2 class="mb-0"><?php echo d_get('school_info.name'); ?></h2>
        <p>Student Performance Review</p>
    </header>

    <!-- Student Information -->
    <div class="row student-info-clean">
        <div class="col-md-4">
            <strong>Student Name</strong>
            <span><?php echo d_get('student_info.full_name'); ?></span>
        </div>
        <div class="col-md-4">
            <strong>Class</strong>
            <span><?php echo d_get('student_info.class'); ?></span>
        </div>
        <div class="col-md-4">
            <strong>Reporting Period</strong>
            <span><?php echo d_get('academic_period'); ?></span>
        </div>
    </div>

    <!-- Grades Table -->
    <table class="table clean-table">
        <thead>
            <tr>
                <th style="width: 40%;">Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center p-5">No academic data available.</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <?php
                            $grades = json_decode($grade_row['grades'], true);
                            $last_col_index = count($data['report_structure']['columns']) - 1;
                            foreach ($data['report_structure']['columns'] as $index => $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                                $is_final_grade = ($index == $last_col_index - 1); // Assuming second to last is the final grade
                        ?>
                            <td class="text-center <?php if ($is_final_grade) echo 'final-grade'; ?>">
                                <?php echo htmlspecialchars($grades[$key] ?? '-'); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <footer class="text-center text-muted mt-5">
        <p>Official School Document</p>
    </footer>
</div>