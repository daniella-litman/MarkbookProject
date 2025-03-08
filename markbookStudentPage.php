<?php
session_start();
include('markbookConnect.php');

if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: markbookLogin.php?error=unauthorized");
    exit();
}

$username = $_SESSION['username'];
$studentResult = mysqli_query($conn, "SELECT student_id, student_name FROM student WHERE student_user = '$username'");
$student = mysqli_fetch_assoc($studentResult);
$student_id = $student['student_id'];
$student_name = $student['student_name'];

if (isset($_GET['ajax']) && isset($_GET['subject_id'])) { //im only keeping this if statement because my AJax isn't in a sep, fopr some reason doesnt work w/o
    $subject_id = (int)$_GET['subject_id'];
    $test_num = isset($_GET['test_num']) ? (int)$_GET['test_num'] : 0;
    $testFilter = $test_num ? "AND test_num = $test_num" : "";

    $avgQuery = "SELECT AVG(mark) AS avg_mark FROM grade WHERE student_id = $student_id AND subject_id = $subject_id $testFilter";
    $avgResult = mysqli_query($conn, $avgQuery);
    $avg = mysqli_fetch_assoc($avgResult)['avg_mark'];
    $avg_display = $avg ? round($avg, 2) : 'N/A';

    function letterGrade($avg) {
        return match (true) {
            $avg >= 90 => 'A',
            $avg >= 80 => 'B',
            $avg >= 70 => 'C',
            $avg >= 60 => 'D',
            default => 'F',
        };
    }

    $letter = is_numeric($avg) ? letterGrade($avg) : '-';

    echo json_encode(['average' => $avg_display, 'letter' => $letter]);
    exit();
}

$subjectsResult = mysqli_query($conn, "
    SELECT s.subject_id, s.subject_name 
    FROM subject s
    JOIN grade g ON s.subject_id = g.subject_id
    WHERE g.student_id = $student_id
    GROUP BY s.subject_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Homepage</title>
    <?php include('markbookStudentNavBar.html'); ?>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 80px; background-color: #f7f5f0; color: #333; }
        h1 { font-size: 28px; margin-bottom: 30px; border-bottom: 2px solid #444; display: inline-block; padding-bottom: 8px; }
        .subject-section { width: 80%; max-width: 600px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: left; }
        .subject-section h2 { font-size: 22px; margin-bottom: 15px; border-bottom: 1px dashed #888; padding-bottom: 5px; }
        label { font-weight: bold; }
        select { width: 100%; padding: 10px; font-size: 16px; margin-top: 10px; border-radius: 6px; background-color: #f9f9f9; border: 1px solid #ccc; }
        .result { margin-top: 20px; font-size: 18px; background-color: #f0f0f0; padding: 15px; border-radius: 8px; text-align: center; }
        .result p { margin: 8px 0; font-weight: bold; }
    </style>
</head>
<body>

<h1>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>

<?php while ($subject = mysqli_fetch_assoc($subjectsResult)): ?>
    <div class="subject-section">
        <h2><?php echo htmlspecialchars($subject['subject_name']); ?></h2>

        <label for="test_num_<?php echo $subject['subject_id']; ?>">Select Test:</label>
        <select id="test_num_<?php echo $subject['subject_id']; ?>" onchange="fetchGrades(<?php echo $subject['subject_id']; ?>, this.value)">
            <option value="0" selected>All Tests</option>
            <option value="1">Test 1</option>
            <option value="2">Test 2</option>
            <option value="3">Test 3</option>
        </select>

        <div class="result" id="result_<?php echo $subject['subject_id']; ?>">
            <p>Average Mark: Loading...</p>
            <p>Letter Grade: -</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchGrades(<?php echo $subject['subject_id']; ?>, 0);
    });
    </script>
<?php endwhile; ?>

<script>
function fetchGrades(subjectId, testNum) {
    const resultDiv = document.getElementById(`result_${subjectId}`);
    resultDiv.innerHTML = '<p>Loading...</p>';

    fetch(`?ajax=1&subject_id=${subjectId}&test_num=${testNum}`)
        .then(response => response.ok ? response.json() : Promise.reject('Fetch error'))
        .then(data => {
            resultDiv.innerHTML = `
                <p>Average Mark: ${data.average}</p>
                <p>Letter Grade: ${data.letter}</p>
            `;
        })
        .catch(() => {
        resultDiv.innerHTML = '<p style="color: red;">Error fetching grades.</p>';
    });
}
    </script>

    </body>
</html>
