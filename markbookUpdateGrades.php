<?php
session_start();
include('markbookConnect.php');

// Check if user is a teacher
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: markbookLogin.php?error=unauthorized");
    exit();
}

$username = $_SESSION['username'];

// Get teacher and subject
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT teacher_id FROM teacher WHERE teacher_user = '$username'"));
if (!$teacher) die("Teacher not found.");

$subject = mysqli_fetch_assoc(mysqli_query($conn, "SELECT subject_id FROM subject WHERE teacher_id = {$teacher['teacher_id']}"));
$subject_id = $subject['subject_id'] ?? null;

if (isset($_GET['update_grade'], $_GET['student_id'], $_GET['test_num'], $_GET['mark'])) {
    $student_id = (int)$_GET['student_id'];
    $test_num = (int)$_GET['test_num'];
    $mark = max(0, min(100, (int)$_GET['mark']));

    // Check if grade exists
    $checkQuery = "SELECT grade_id FROM grade WHERE student_id = $student_id AND subject_id = $subject_id AND test_num = $test_num";
    $checkResult = mysqli_query($conn, $checkQuery);
    $grade = mysqli_fetch_assoc($checkResult);

    if ($grade) {
        // Update existing grade
        $update = mysqli_query($conn, "UPDATE grade SET mark = $mark WHERE grade_id = {$grade['grade_id']}");
        $status = $update ? "success" : "error";
    } else {
        // Insert new grade
        $insert = mysqli_query($conn, "INSERT INTO grade (student_id, subject_id, test_num, mark) VALUES ($student_id, $subject_id, $test_num, $mark)");
        $status = $insert ? "success" : "error";
    }

    echo json_encode(["status" => $status]);
    exit();
}

// Get selected class
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Fetch students & grades
$students = $class_id ? mysqli_query($conn, "SELECT * FROM student WHERE class_id = $class_id ORDER BY student_name") : null;
$grades = [];
if ($class_id) {
    $gradeQuery = mysqli_query($conn, "SELECT grade_id, student_id, test_num, mark FROM grade WHERE subject_id = $subject_id");
    while ($row = mysqli_fetch_assoc($gradeQuery)) {
        $grades[$row['test_num']][$row['student_id']] = $row['mark'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Grades</title>
    <?php include('markbookTeacherNavBar.html'); ?>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> <!-- For AJAX -->
    <style>
        body { font-family: 'EB Garamond', serif; text-align: center; background: #f4f1ea; padding: 20px; }
        h1 { font-size: 30px; border-bottom: 2px solid #000; display: inline-block; margin-bottom: 20px; }
        .selector { margin: 20px 0; }
        select { padding: 8px; font-size: 16px; border-radius: 4px; }
        .tests-container { display: flex; gap: 20px; justify-content: space-around; flex-wrap: wrap; margin-top: 30px; }
        .test-column { width: 100%; max-width: 300px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .test-column h2 { font-size: 22px; border-bottom: 1px dashed #aaa; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; border: 1px solid #aaa; text-align: center; }
        th { background: #ddd; }
        input[type="number"] { width: 60px; padding: 5px; border-radius: 4px; text-align: center; }
        .success-msg { color: green; font-weight: bold; margin-top: 5px; font-size: 14px; display: none; }
    </style>
</head>
<body>

<h1>Manage Grades</h1>

<div class="selector">
    <form method="GET">
        <label for="class_id">Select Class:</label>
        <select name="class_id" id="class_id" onchange="this.form.submit()">
            <option value="" disabled <?php if (!$class_id) echo 'selected'; ?>>Choose a class</option>
            <option value="1" <?php if ($class_id === 1) echo 'selected'; ?>>Class 1</option>
            <option value="2" <?php if ($class_id === 2) echo 'selected'; ?>>Class 2</option>
            <option value="3" <?php if ($class_id === 3) echo 'selected'; ?>>Class 3</option>
        </select>
    </form>
</div>

<?php if ($class_id && $students): ?>
    <div class="tests-container">
        <?php for ($test_num = 1; $test_num <= 3; $test_num++): ?>
            <div class="test-column">
                <h2>Test <?php echo $test_num; ?></h2>
                <table>
                    <tr><th>Student</th><th>Grade</th></tr>
                    <?php
                    mysqli_data_seek($students, 0); // Reset pointer
                    while ($student = mysqli_fetch_assoc($students)):
                        $student_id = $student['student_id'];
                        $mark = $grades[$test_num][$student_id] ?? '';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                        <td>
                            <input type="number" min="0" max="100" value="<?php echo $mark; ?>"
                                onchange="updateGrade(<?php echo $student_id; ?>, <?php echo $test_num; ?>, this)">
                            <span class="success-msg" id="success-<?php echo $student_id; ?>-<?php echo $test_num; ?>">Saved!</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endfor; ?>
    </div>
<?php elseif ($class_id): ?>
    <p>No students found in this class.</p>
<?php endif; ?>

<script>
function updateGrade(studentId, testNum, input) {
    const mark = input.value;
    if (mark === '') return;

    axios.get('', {
        params: { update_grade: 1, student_id: studentId, test_num: testNum, mark: mark }
    })
        .then(response => {
        if (response.data.status === 'success') {
            const msg = document.getElementById(`success-${studentId}-${testNum}`);
            msg.style.display = 'inline';
            setTimeout(() => msg.style.display = 'none', 1500);
        } else {
            alert('Error saving grade.');
        }
    })
        .catch(() => alert('Request failed.'));
}
    </script>

    </body>
</html>
