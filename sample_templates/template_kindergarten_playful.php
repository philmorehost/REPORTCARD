<?php
// Template 4: Kindergarten Fun
// A colorful and engaging report card template designed for kindergarten students.
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Fredoka+One&family=Poppins:wght@400;600&display=swap');

    .kiddy-container {
        font-family: 'Poppins', sans-serif;
        border: 2px solid #007bff;
        border-radius: 15px;
        padding: 2rem;
        background-color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Decorative elements - Sun/Cloud */
    .kiddy-container::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: #FFD700; /* Sun color */
        border-radius: 50%;
        opacity: 0.8;
        z-index: 0;
    }
    /* Subtle Kids ABC background image */
    .kiddy-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../../uploads/kindergarten.png');
        background-repeat: no-repeat;
        background-position: 50% 50%;
        background-size: 80%;
        opacity: 0.08;
        z-index: 0;
    }

    .kiddy-header {
        text-align: center;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }
    .kiddy-header img.school-logo {
        max-height: 80px;
        margin-bottom: 0.5rem;
    }
    .kiddy-header h1 {
        font-family: 'Fredoka One', cursive;
        color: #007bff;
        font-size: 2.5rem;
        margin-bottom: 0;
    }
    .kiddy-header h2 {
        font-family: 'Fredoka One', cursive;
        color: #28a745;
        font-size: 2rem;
        margin-top: 0.5rem;
    }

    .student-info-kiddy {
        background-color: #e9f5ff;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }

    .kiddy-grades-table {
        margin-top: 1rem;
        border: 1px solid #dee2e6;
        position: relative;
        z-index: 2;
    }
    .kiddy-grades-table thead {
        background-color: #007bff;
        color: white;
        font-family: 'Fredoka One', cursive;
        font-weight: normal;
        letter-spacing: 1px;
    }

    .kiddy-grades-table th, .kiddy-grades-table td {
        vertical-align: middle;
    }
    .kiddy-grades-table tbody tr:nth-child(odd) {
        background-color: #f8f9fa;
    }

    .kiddy-comments-box {
        background-color: #fffde7;
        border: 1px dashed #FFC107;
        border-radius: 10px;
        padding: 1rem;
        height: 100%;
        position: relative;
        z-index: 2;
    }
    .kiddy-comments-box h5 {
        font-family: 'Fredoka One', cursive;
        color: #ff9800;
    }

    .corner-kids-image {
        position: absolute;
        bottom: -20px;
        left: -30px;
        max-width: 250px;
        height: auto;
        opacity: 0.2;
        z-index: 1;
        transform: rotate(-5deg);
        filter: drop-shadow(3px 3px 5px rgba(0,0,0,0.1));
    }

    .kiddy-signatures {
        margin-top: 3rem;
        display: flex;
        justify-content: center; /* Changed to center the single signature block */
        align-items: flex-end;
        position: relative;
        z-index: 2;
    }
    .kiddy-signature-block {
        text-align: center;
        width: 50%; /* Increased width slightly */
    }
    .kiddy-signature-line {
        border-top: 2px dotted #000;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    /* UPDATED Style for Text Signature */
    .kiddy-signature-text {
        font-family: 'Great Vibes', cursive; /* New signature font */
        font-size: 2.5rem; /* Increased size for better look */
        color: #000033; /* Dark blue ink color */
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: -0.5rem;
    }


    .top-left-numbers {
        position: absolute;
        top: 20px;
        left: 20px;
        font-family: 'Fredoka One', cursive;
        font-size: 3rem;
        opacity: 0.6;
        transform: rotate(-10deg);
        z-index: 1;
    }
    .top-left-numbers .num-1 { color: #28a745; }
    .top-left-numbers .num-2 { color: #dc3545; }
    .top-left-numbers .num-3 { color: #FFD700; text-shadow: 0 0 2px #fff; }

    .top-right-letters {
        position: absolute;
        top: 20px;
        right: 20px;
        font-family: 'Fredoka One', cursive;
        font-size: 3rem;
        opacity: 0.6;
        transform: rotate(10deg);
        z-index: 1;
    }
    .top-right-letters .letter-a { color: #007bff; }
    .top-right-letters .letter-b { color: #fd7e14; }
    .top-right-letters .letter-c { color: #6f42c1; }

    .top-balloons {
        position: absolute;
        top: -20px;
        width: 100%;
        height: 100px;
        left: 0;
        z-index: 1;
        pointer-events: none;
    }

    .balloon {
        position: absolute;
        width: 50px;
        height: 60px;
        border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
        opacity: 0.7;
        filter: drop-shadow(2px 2px 3px rgba(0,0,0,0.2));
    }
    .balloon::after {
        content: '';
        position: absolute;
        width: 1px;
        height: 30px;
        background: #888;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
    }

    .balloon.blue { background-color: #007bff; left: 10%; top: 10px; transform: rotate(5deg); }
    .balloon.green { background-color: #28a745; left: 25%; top: 5px; transform: rotate(-8deg); width: 45px; height: 55px; }
    .balloon.red { background-color: #dc3545; left: 40%; top: 15px; transform: rotate(10deg); }
    .balloon.yellow { background-color: #FFD700; left: 55%; top: 0px; transform: rotate(-3deg); width: 55px; height: 65px; }
    .balloon.purple { background-color: #6f42c1; left: 70%; top: 20px; transform: rotate(7deg); }
    .balloon.orange { background-color: #fd7e14; left: 85%; top: 12px; transform: rotate(-6deg); width: 48px; height: 58px; }

</style>

<div class="kiddy-container">
    <div class="top-left-numbers">
        <span class="num-1">1</span><span class="num-2">2</span><span class="num-3">3</span>
    </div>

    <div class="top-right-letters">
        <span class="letter-a">A</span><span class="letter-b">B</span><span class="letter-c">C</span>
    </div>

    <div class="top-balloons">
        <div class="balloon blue"></div>
        <div class="balloon green"></div>
        <div class="balloon red"></div>
        <div class="balloon yellow"></div>
        <div class="balloon purple"></div>
        <div class="balloon orange"></div>
    </div>

    <header class="kiddy-header">
        <?php if (d_get('school_info.logo_url')): ?>
            <img src="/<?php echo d_get('school_info.logo_url'); ?>" alt="School Logo" class="school-logo">
        <?php endif; ?>
        <p class="lead"><?php echo d_get('school_info.address'); ?></p>
        <h1><?php echo d_get('school_info.name'); ?></h1>
        <h2>KINDERGARTEN REPORT CARD</h2>
    </header>

    <div class="student-info-kiddy">
        <div class="row">
            <div class="col-6">
                <p class="mb-1"><strong>Student:</strong> <?php echo d_get('student_info.full_name'); ?></p>
                <p class="mb-1"><strong>Class:</strong> <?php echo d_get('student_info.class'); ?></p>
            </div>
            <div class="col-6">
                <p class="mb-1"><strong>Student ID:</strong> <?php echo d_get('student_info.student_id_number', 'N/A'); ?></p>
                <p class="mb-1"><strong>Period:</strong> <?php echo d_get('academic_period'); ?></p>
            </div>
        </div>
    </div>

    <h4 class="text-center" style="font-family: 'Fredoka One', cursive; color: #dc3545;">Skills Assessment</h4>
    <table class="table table-bordered kiddy-grades-table">
        <thead>
            <tr>
                <th>Learning Area</th>
                <?php foreach ($data['report_structure']['columns'] as $column): ?>
                    <th class="text-center"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['grades_data'])): ?>
                <tr><td colspan="<?php echo count($data['report_structure']['columns']) + 1; ?>" class="text-center py-4">No assessment data available.</td></tr>
            <?php else: ?>
                <?php foreach ($data['grades_data'] as $grade_row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade_row['class_name']); ?></td>
                        <?php
                            $grades = json_decode($grade_row['grades'], true);
                            foreach ($data['report_structure']['columns'] as $column):
                                $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $column));
                        ?>
                            <td class="text-center"><?php echo htmlspecialchars($grades[$key] ?? '—'); ?></td>
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
    <div class="row mt-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <?php if (!empty($teacher_comment)): ?>
            <div class="kiddy-comments-box">
                <h5>Teacher's Comment</h5>
                <p class="mb-0"><?php echo htmlspecialchars($teacher_comment); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php if (!empty($principal_comment)): ?>
            <div class="kiddy-comments-box">
                <h5>Principal's Comment</h5>
                <p class="mb-0"><?php echo htmlspecialchars($principal_comment); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <img src="../../uploads/kindergarten.png" alt="Kids learning ABCs" class="corner-kids-image">

    <div class="kiddy-signatures">
        <div class="kiddy-signature-block">
            <?php
                // ROBUST SIGNATURE LOGIC
                $principal_sig = d_get('principal_signature', null, false);
                $signature_content = '<div class="kiddy-signature-text">N/A</div>'; // Default content

                if (is_array($principal_sig) && !empty($principal_sig['signature_data'])) {
                    if ($principal_sig['signature_type'] === 'image') {
                        $signature_content = '<img src="/' . htmlspecialchars($principal_sig['signature_data']) . '" alt="Principal Signature" style="max-height: 40px;">';
                    } else { // Assumes text-based signature
                        $signature_content = '<div class="kiddy-signature-text">' . htmlspecialchars($principal_sig['signature_data']) . '</div>';
                    }
                }
                echo $signature_content;
            ?>
            <div class="kiddy-signature-line"></div>
            <p><strong>Proprietress's Signature</strong></p>
        </div>
    </div>
</div>