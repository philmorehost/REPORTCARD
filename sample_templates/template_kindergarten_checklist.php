<?php
// Template 7: Kindergarten - Checklist Style
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Muli:wght@400;700&display=swap');
    .kg-check-container {
        font-family: 'Muli', sans-serif;
        border: 1px solid #ccc;
        padding: 2rem;
    }
    .kg-check-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .kg-check-header h3 {
        font-weight: 700;
        color: #333;
    }
    .kg-check-table {
        font-size: 0.95rem;
    }
    .kg-check-table th {
        background-color: #f2f2f2;
        text-align: center;
        vertical-align: middle;
    }
    .kg-check-table td {
        vertical-align: middle;
    }
    .skill-category {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .check-mark {
        font-size: 1.5rem;
        color: #198754;
    }
</style>

<div class="kg-check-container">
    <!-- Header -->
    <header class="kg-check-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" style="max-height: 70px; margin-bottom: 1rem;">
        <?php endif; ?>
        <h3 class="mb-1"><?php echo d_get('school_info.name'); ?></h3>
        <p class="mb-0 text-muted">Kindergarten Skills Checklist</p>
        <p><strong><?php echo d_get('academic_period'); ?></strong></p>
    </header>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-md-6"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
        <div class="col-md-6 text-md-end"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></div>
    </div>

    <!-- Skills Checklist Table -->
    <table class="table table-bordered kg-check-table">
        <thead class="text-center">
            <tr>
                <th style="width: 40%;">Developmental Area & Skill</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <!-- This template assumes a structure where the subject is the category and the grade is the skill -->
            <!-- For demonstration, we'll hardcode some skills. -->

            <tr class="skill-category"><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>"><strong>Social & Emotional Development</strong></td></tr>
            <tr>
                <td class="text-start ps-4">Shares toys and materials</td>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'consistent') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start ps-4">Expresses feelings appropriately</td>
                 <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'developing') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>

            <tr class="skill-category"><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>"><strong>Language & Literacy</strong></td></tr>
             <tr>
                <td class="text-start ps-4">Recognizes own name in print</td>
                 <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'consistent') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start ps-4">Speaks in complete sentences</td>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'consistent') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>

            <tr class="skill-category"><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>"><strong>Mathematics</strong></td></tr>
             <tr>
                <td class="text-start ps-4">Counts from 1 to 20</td>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'developing') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>
             <tr>
                <td class="text-start ps-4">Recognizes basic shapes</td>
                 <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <td><?php if(strtolower($column) == 'consistent') echo '<i class="bi bi-check-circle-fill check-mark"></i>'; ?></td>
                <?php endforeach; ?>
            </tr>

        </tbody>
    </table>

     <!-- Comments Section -->
    <div class="mt-4">
        <strong>Teacher's Comments:</strong>
        <p class="border p-2 mt-1">
            <?php echo d_get('student_info.full_name'); ?> has made wonderful progress in sharing with friends and is becoming more confident in their counting skills. A joy to have in class!
        </p>
    </div>
</div>