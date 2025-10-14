<?php
// Template 9: Primary - Goals & Achievements
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap');
    .goals-container {
        font-family: 'Nunito', sans-serif;
        border: 1px solid #ddd;
        padding: 2rem;
    }
    .goals-header {
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }
    .goals-header .school-name {
        font-weight: 700;
        font-size: 1.8rem;
        color: <?php echo d_get('school_info.brand_color', '#0056b3'); ?>;
    }
    .goals-table th {
        background-color: #f8f9fa;
    }
    .section-title {
        font-weight: 700;
        font-size: 1.3rem;
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: <?php echo d_get('school_info.brand_color', '#0056b3'); ?>;
    }
    .goal-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
    }
    .goal-box .icon {
        font-size: 2rem;
        color: #ffc107;
        float: left;
        margin-right: 1rem;
    }
</style>

<div class="goals-container">
    <!-- Header -->
    <header class="goals-header">
        <div class="d-flex justify-content-between align-items-center">
            <span class="school-name"><?php echo d_get('school_info.name'); ?></span>
            <?php if (d_get('school_info.logo_url')): ?>
                <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" style="max-height: 60px;">
            <?php endif; ?>
        </div>
        <p class="mb-0 text-muted">Student Progress & Goals Report</p>
    </header>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-md-6"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
        <div class="col-md-6 text-md-end"><strong>Period:</strong> <?php echo d_get('academic_period'); ?></div>
    </div>

    <!-- Academic Performance -->
    <h3 class="section-title">Academic Performance</h3>
    <table class="table table-bordered goals-table">
        <thead class="text-center">
            <tr>
                <th class="text-start" style="width: 30%;">Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center p-4">No academic records for this period.</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td class="text-start"><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
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

    <!-- Goals Section -->
    <h3 class="section-title">Personal & Academic Goals</h3>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="goal-box h-100">
                <i class="bi bi-bullseye icon"></i>
                <h5>Student's Goal</h5>
                <p>My goal for this term was to read one new chapter book every week.</p>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="goal-box h-100">
                <i class="bi bi-award-fill icon"></i>
                <h5>Teacher's Feedback on Goal</h5>
                <p><?php echo d_get('student_info.full_name'); ?> did a fantastic job and exceeded this goal, often reading two books a week! Wonderful dedication.</p>
            </div>
        </div>
    </div>

    <!-- General Comments -->
     <div class="mt-3">
        <h5>General Teacher's Comment:</h5>
        <p class="border p-2">
            A fantastic term for <?php echo d_get('student_info.full_name'); ?>. Their commitment to their reading goal was inspiring to see.
        </p>
    </div>
</div>