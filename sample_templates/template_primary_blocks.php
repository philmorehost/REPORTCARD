<?php
// Template 8: Primary - Subject Blocks
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap');
    .primary-container {
        font-family: 'Quicksand', sans-serif;
        padding: 1.5rem;
        background-color: #f9f9f9;
    }
    .primary-header {
        text-align: center;
        margin-bottom: 2rem;
        border-bottom: 3px solid <?php echo d_get('school_info.brand_color', '#1a73e8'); ?>;
        padding-bottom: 1rem;
    }
    .primary-header h2 {
        font-weight: 700;
    }
    .student-box {
        background-color: #fff;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .subject-block {
        background-color: #fff;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .subject-header {
        padding: 1rem;
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .subject-grades {
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 1rem;
    }
    .grade-item {
        text-align: center;
    }
    .grade-item .value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .grade-item .label {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .subject-comment {
        padding: 0 1rem 1rem 1rem;
        font-style: italic;
        color: #555;
    }
    /* Define some colors for the subject blocks */
    .color-1 { background-color: #4285f4; }
    .color-2 { background-color: #34a853; }
    .color-3 { background-color: #fbbc05; }
    .color-4 { background-color: #ea4335; }
    .color-5 { background-color: #9c27b0; }
</style>

<div class="primary-container">
    <!-- Header -->
    <header class="primary-header">
        <h2 class="mb-1"><?php echo d_get('school_info.name'); ?></h2>
        <p class="mb-0">Progress Report for <?php echo d_get('academic_period'); ?></p>
    </header>

    <!-- Student Info -->
    <div class="student-box row mb-4 text-center">
        <div class="col"><strong>Student:</strong><br><?php echo d_get('student_info.full_name'); ?></div>
        <div class="col"><strong>Class:</strong><br><?php echo d_get('student_info.class'); ?></div>
    </div>

    <!-- Subject Blocks -->
    <?php if (empty($data['grades_data'])): ?>
        <div class="text-center p-5">No subjects or grades found for this period.</div>
    <?php else: ?>
        <?php
            $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5'];
            $color_index = 0;
            foreach ($data['grades_data'] as $grade_row):
                $color_class = $colors[$color_index % count($colors)];
                $color_index++;
        ?>
            <div class="subject-block">
                <div class="subject-header <?php echo $color_class; ?>">
                    <?php echo htmlspecialchars($grade_row['class_name']); ?>
                </div>
                <div class="subject-grades">
                    <?php
                        $grades = json_decode($grade_row['grades'], true);
                        foreach ($data['report_structure']['columns'] as $column):
                            $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                            if (strtolower($column) !== 'remark'): // Don't show 'remark' as a grade item
                    ?>
                        <div class="grade-item">
                            <div class="value"><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></div>
                            <div class="label"><?php echo htmlspecialchars($column); ?></div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
                <?php
                    $remark_key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', 'Remark'));
                    if (isset($grades[$remark_key]) && !empty($grades[$remark_key])):
                ?>
                    <div class="subject-comment">
                        <strong>Teacher's Comment:</strong> <?php echo htmlspecialchars($grades[$remark_key]); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>