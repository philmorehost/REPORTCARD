<?php
// Template 3: Vibrant & Friendly
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    .vibrant-container {
        font-family: 'Poppins', sans-serif;
        border: 2px solid <?php echo d_get('school_info.brand_color', '#0dcaf0'); ?>;
        border-radius: 15px;
        padding: 1.5rem;
        background: #fdfdfd;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .vibrant-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .vibrant-header h2 {
        font-weight: 600;
        color: <?php echo d_get('school_info.brand_color', '#0dcaf0'); ?>;
    }
    .school-logo-vibrant {
        max-height: 80px;
        margin-bottom: 1rem;
    }
    .student-info-box {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 2rem;
    }
    .vibrant-table th {
        background-color: <?php echo d_get('school_info.brand_color', '#0dcaf0'); ?> !important;
        color: white;
        border: none !important;
        font-weight: 600;
        padding: 1rem;
    }
    .vibrant-table td {
        padding: 1rem;
        vertical-align: middle;
    }
    .vibrant-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .comment-card {
        background-color: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
    .comment-card h5 {
        color: <?php echo d_get('school_info.brand_color', '#0dcaf0'); ?>;
    }
</style>

<div class="vibrant-container">
    <!-- Header -->
    <header class="vibrant-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo-vibrant">
        <?php endif; ?>
        <h2 class="mb-1"><?php echo d_get('school_info.name'); ?></h2>
        <p class="mb-0 text-muted">Our Student's Progress Report!</p>
    </header>

    <!-- Student Information -->
    <div class="student-info-box">
        <div class="row text-center">
            <div class="col-6">
                <h6 class="text-muted mb-0">Student</h6>
                <strong><?php echo d_get('student_info.full_name'); ?></strong>
            </div>
            <div class="col-6">
                <h6 class="text-muted mb-0">Class</h6>
                <strong><?php echo d_get('student_info.class'); ?></strong>
            </div>
        </div>
    </div>

    <!-- Grades Table -->
    <table class="table vibrant-table text-center">
        <thead>
            <tr>
                <th class="text-start" style="width: 30%; border-top-left-radius: 10px;">Subject</th>
                <?php
                $columns = $data['report_structure']['columns'];
                $last_col_index = count($columns) - 1;
                foreach ($columns as $index => $column):
                    $border_radius_style = ($index == $last_col_index) ? 'border-top-right-radius: 10px;' : '';
                ?>
                    <th style="<?php echo $border_radius_style; ?>"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($columns) + 1; ?>" class="text-center py-5">Let's get some grades in here!</td></tr>
            <?php else: ?>
                 <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td class="text-start"><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <?php
                            $grades = json_decode($grade_row['grades'], true);
                            foreach ($columns as $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                        ?>
                            <td><?php echo htmlspecialchars($grades[$key] ?? '-'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Comments Section -->
    <div class="comment-card">
        <h5 class="mb-3"><i class="bi bi-chat-right-text-fill me-2"></i>A Note From The Teacher</h5>
        <p class="mb-0">John is a star! His creativity shines through in every project. We are so proud of his hard work this term.</p>
    </div>
</div>