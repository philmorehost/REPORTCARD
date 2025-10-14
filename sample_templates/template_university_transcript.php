<?php
// Template 14: University - Official Transcript
?>
<style>
    .uni-container {
        font-family: 'Georgia', serif;
        font-size: 10pt;
        color: #000;
        background-color: #fff;
        padding: 2rem;
    }
    .uni-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 1rem;
        border-bottom: 3px solid #000;
    }
    .uni-header .school-info h1 {
        font-size: 18pt;
        font-weight: bold;
        margin: 0;
    }
    .uni-header .school-info p {
        margin: 0;
        font-size: 9pt;
    }
    .uni-logo {
        max-height: 90px;
    }
    .transcript-title {
        text-align: center;
        margin: 1.5rem 0;
        font-size: 14pt;
        font-weight: bold;
        text-transform: uppercase;
    }
    .student-details-uni {
        border: 1px solid #000;
        padding: 0.5rem;
        margin-bottom: 2rem;
    }
    .uni-table {
        width: 100%;
        border-collapse: collapse;
    }
    .uni-table th, .uni-table td {
        border: 1px solid #000;
        padding: 0.5rem;
        text-align: left;
    }
    .uni-table th {
        font-weight: bold;
    }
    .uni-summary {
        margin-top: 1rem;
        text-align: right;
    }
    .seal-area {
        position: relative;
        margin-top: 2rem;
        text-align: center;
    }
    .seal-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 2px solid #ccc;
        color: #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 10pt;
    }
</style>

<div class="uni-container">
    <!-- Header -->
    <header class="uni-header">
        <div class="school-info">
            <h1><?php echo d_get('school_info.name'); ?></h1>
            <p>123 University Drive, Knowledge City, State 12345</p>
        </div>
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="University Logo" class="uni-logo">
        <?php endif; ?>
    </header>

    <h2 class="transcript-title">Official Academic Transcript</h2>

    <!-- Student Details -->
    <div class="student-details-uni">
        <div class="row">
            <div class="col-6"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></div>
            <div class="col-6"><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number'); ?></div>
            <div class="col-6"><strong>Program:</strong> <?php echo d_get('student_info.class'); ?></div>
            <div class="col-6"><strong>Date Issued:</strong> <?php echo date('F j, Y'); ?></div>
        </div>
    </div>

    <!-- Grades Table -->
    <table class="uni-table">
        <thead>
            <tr>
                <th>Course Code</th>
                <th style="width: 40%;">Course Title</th>
                <th class="text-center">Credits</th>
                <th class="text-center">Grade</th>
                <th class="text-center">Grade Points</th>
            </tr>
        </thead>
        <tbody>
            <!-- This template assumes a specific column structure. -->
            <!-- The school admin would need to create columns like: 'Course Code', 'Credits', 'Grade', 'Grade Points' -->
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="5" class="text-center p-4">No courses recorded for this period.</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <?php $grades = json_decode($grade_row['grades'], true); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grades['course_code'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($grades['credits'] ?? '-'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($grades['grade'] ?? '-'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($grades['grade_points'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div class="row uni-summary">
        <div class="col-6 offset-6">
            <table class="table table-sm table-borderless">
                <tr><td><strong>Semester GPA:</strong></td><td>3.80</td></tr>
                <tr><td><strong>Cumulative GPA:</strong></td><td>3.75</td></tr>
                <tr><td><strong>Credits Earned:</strong></td><td>18</td></tr>
            </table>
        </div>
    </div>

    <!-- Seal and Signature -->
    <div class="row" style="margin-top: 3rem;">
        <div class="col-8">
            <div class="seal-area">
                <div class="seal-placeholder">Official<br>University<br>Seal</div>
            </div>
        </div>
        <div class="col-4 text-center">
            <div style="border-bottom: 1px solid #000; margin-top: 5rem; margin-bottom: 0.5rem;"></div>
            <p style="margin:0; font-size: 10pt;">University Registrar</p>
        </div>
    </div>
</div>