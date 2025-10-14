<?php
// Template 10: Primary - Illustrated Theme
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Open+Sans&display=swap');
    .illustrated-container {
        font-family: 'Open Sans', sans-serif;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 2rem;
        /* Placeholder for a background image. A real implementation would use a nice, subtle pattern. */
        background-color: #fff;
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text x="0" y="20" font-size="14" fill="%23e9ecef" transform="rotate(45)">ABC</text><text x="50" y="70" font-size="14" fill="%23e9ecef" transform="rotate(45)">123</text></svg>');
    }
    .illustrated-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .illustrated-header h2 {
        font-family: 'Fredoka One', cursive;
        color: <?php echo d_get('school_info.brand_color', '#ff6f61'); ?>;
        font-size: 2.2rem;
    }
    .student-highlight {
        background-color: <?php echo d_get('school_info.brand_color', '#ff6f61'); ?>;
        color: white;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    .illustrated-table {
        background-color: rgba(255,255,255,0.9);
    }
    .illustrated-table th {
        font-family: 'Fredoka One', cursive;
        background-color: transparent !important;
        color: <?php echo d_get('school_info.brand_color', '#ff6f61'); ?>;
        border-bottom: 2px solid <?php echo d_get('school_info.brand_color', '#ff6f61'); ?>;
    }
    .comment-box-illustrated {
        margin-top: 2rem;
        text-align: center;
    }
    .comment-box-illustrated .icon {
        font-size: 3rem;
        color: #ffc107;
    }
</style>

<div class="illustrated-container">
    <!-- Header -->
    <header class="illustrated-header">
        <h2 class="mb-1">Awesome Work!</h2>
        <p class="lead text-muted"><?php echo d_get('school_info.name'); ?> - <?php echo d_get('academic_period'); ?></p>
    </header>

    <!-- Student Highlight -->
    <div class="student-highlight text-center">
        <h4 class="mb-0 text-white">This report is for the amazing...</h4>
        <h2 class="display-5 text-white" style="font-family: 'Fredoka One', cursive;"><?php echo d_get('student_info.full_name'); ?></h2>
    </div>

    <!-- Grades Table -->
    <table class="table table-striped illustrated-table">
        <thead class="text-center">
            <tr>
                <th class="text-start" style="width: 30%;">Subject</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="p-4">Ready to see some amazing grades!</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td class="text-start"><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
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

    <!-- Teacher's Note -->
    <div class="comment-box-illustrated">
        <div class="icon"><i class="bi bi-chat-quote-fill"></i></div>
        <h5 class="mt-2">A Special Note From Your Teacher</h5>
        <p class="lead fst-italic">
            "<?php echo d_get('student_info.full_name'); ?> has been a superstar this term! Their curiosity and kindness make our classroom a better place. Keep up the wonderful work!"
        </p>
    </div>
</div>