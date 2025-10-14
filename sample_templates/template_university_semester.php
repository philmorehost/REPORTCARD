<?php
// Template 15: University - Redesigned
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap');
    
    .uni-container {
        font-family: 'Lato', sans-serif;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden; /* Ensures border-radius is respected */
    }

    .uni-header {
        padding: 1.5rem 2rem;
        border-top: 5px solid <?php echo d_get('school_info.brand_color', '#00447C'); ?>;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8f9fa;
    }
    .uni-logo {
        max-height: 70px;
        width: auto;
    }
    .uni-header .header-text h2 {
        font-weight: 900;
        margin: 0;
        color: #212529;
    }
    .uni-header .header-text p {
        margin: 0;
        font-size: 1.1rem;
        color: #495057;
    }

    .uni-body {
        padding: 2rem;
    }
    
    .uni-student-info strong {
        color: #343a40;
    }

    .uni-gpa-summary {
        display: flex;
        justify-content: space-between;
        text-align: center;
        gap: 1.5rem; /* Space between cards */
        margin-top: 2rem;
    }
    .uni-gpa-card {
        flex: 1;
        background-color: #f8f9fa;
        padding: 1.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .uni-gpa-card .value {
        font-size: 2.5rem;
        font-weight: 900;
        color: <?php echo d_get('school_info.brand_color', '#00447C'); ?>;
        line-height: 1;
    }
    .uni-gpa-card .label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .uni-table {
        margin-top: 2rem;
    }
    .uni-table th {
        border-bottom: 2px solid #343a40;
        color: #343a40;
    }
    .uni-table td {
        vertical-align: middle;
    }
    .uni-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .uni-footer-note {
        margin-top: 2.5rem;
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-left: 4px solid <?php echo d_get('school_info.brand_color', '#00447C'); ?>;
        border-radius: 0 8px 8px 0;
    }
</style>

<div class="uni-container">
    <header class="uni-header">
        <div>
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="University Logo" class="uni-logo">
            <?php endif; ?>
        </div>
        <div class="header-text text-end">
            <h2 class="mb-0"><?php echo d_get('school_info.name'); ?></h2>
            <p class="mb-1">Semester Performance Review</p>
            <strong><?php echo d_get('academic_period'); ?></strong>
        </div>
    </header>

    <div class="uni-body">
        <div class="row uni-student-info">
            <div class="col-md-7"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
            <div class="col-md-5"><strong>Program:</strong> <?php echo d_get('student_info.class'); ?></div>
        </div>
        <hr class="my-4">

        <div class="uni-gpa-summary">
            <div class="uni-gpa-card">
                <div class="value">
                    <?php echo d_get('gpa_summary.semester_gpa', 'N/A'); ?>
                </div>
                <div class="label">Semester GPA</div>
            </div>
            <div class="uni-gpa-card">
                <div class="value">
                    <?php echo d_get('gpa_summary.cumulative_gpa', 'N/A'); ?>
                </div>
                <div class="label">Cumulative GPA</div>
            </div>
            <div class="uni-gpa-card">
                <div class="value">
                    <?php echo d_get('gpa_summary.credits_earned', 'N/A'); ?>
                </div>
                <div class="label">Credits Earned</div>
            </div>
        </div>

        <h5 class="mt-5">Course Details</h5>
        <table class="table uni-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Course</th>
                    <th style="width: 50%;">Title</th>
                    <th class="text-center">Credits</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                 <?php if (empty($data['grades_data'])): ?>
                    <tr><td colspan="4" class="text-center p-4">No course data for this semester.</td></tr>
                <?php else: ?>
                      <?php foreach ($data['grades_data'] as $grade_row): ?>
                         <?php $grades = json_decode($grade_row['grades'], true); ?>
                         <tr>
                             <td><?php echo htmlspecialchars($grades['course_code'] ?? 'N/A'); ?></td>
                             <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                             <td class="text-center"><?php echo htmlspecialchars($grades['credits'] ?? '-'); ?></td>
                             <td class="text-center fw-bold"><?php echo htmlspecialchars($grades['grade'] ?? '-'); ?></td>
                         </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="uni-footer-note">
            <strong>Note from the Dean's Office:</strong>
            <p class="fst-italic mb-0 mt-1">
                An excellent semester performance. You have been named to the Dean's List for <?php echo d_get('academic_period'); ?>. Congratulations on your hard work and academic achievement.
            </p>
        </div>
    </div>
</div>