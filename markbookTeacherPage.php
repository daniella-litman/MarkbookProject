<?php
session_start();
include('markbookConnect.php');

// Session check for teacher
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: markbookLogin.php?error=unauthorized");
    exit();
}

$username = $_SESSION['username'];

// Fetch teacher info
$teacherQuery = "SELECT t.teacher_name, s.subject_name, s.subject_id 
                 FROM teacher t 
                 JOIN subject s ON t.teacher_id = s.teacher_id 
                 WHERE t.teacher_user = '$username'";
$teacherResult = mysqli_query($conn, $teacherQuery);
$teacher = mysqli_fetch_assoc($teacherResult);

$teacher_name = $teacher['teacher_name'];
$subject_name = $teacher['subject_name'];
$subject_id = $teacher['subject_id'];

function letterGrade($avg) {
    if ($avg >= 90) return 'A';
    if ($avg >= 80) return 'B';
    if ($avg >= 70) return 'C';
    if ($avg >= 60) return 'D';
    return 'F';
}

$classes = [1 => 'Class 1', 2 => 'Class 2', 3 => 'Class 3'];
$allClassAverages = [];  // For the combined chart
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Homepage</title>
    <?php include('markbookTeacherNavBar.html'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=Playfair+Display:wght@600;700&family=Libre+Baskerville:wght@400;700&display=swap');

        body {
            font-family: 'EB Garamond', serif;
            background-color: #f4f1ea;
            color: #2c2c2c; 
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 2px solid #2c2c2c;
            display: inline-block;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        p {
            font-family: 'Libre Baskerville', serif;
            font-size: 18px;
            margin: 8px 0 30px;
        }

        .columns-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .class-section {
            flex: 1;
            padding: 20px;
            border: 2px solid #2c2c2c;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .class-section h2 {
            font-size: 24px;
            text-transform: uppercase;
            border-bottom: 1px dashed #555;
            padding-bottom: 6px;
            margin-bottom: 15px;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #aaa;
            background-color: #f9f9f9;
        }

        .result {
            font-size: 18px;
            margin: 15px 0;
        }

        canvas {
            margin-top: 20px;
            max-width: 100%;
            height: auto;
        }

        #combinedChartContainer {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            border: 2px solid #2c2c2c;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<br><br><br>
<h1>Welcome, <?php echo htmlspecialchars($teacher_name); ?>!</h1>
<p>You are teaching: <strong><?php echo htmlspecialchars($subject_name); ?></strong></p>
<hr>

<div class="columns-container">
    <?php foreach ($classes as $class_id => $class_name) :
        // Fetch averages for each test
        $testAverages = [];
        for ($test = 1; $test <= 3; $test++) {
            $query = "SELECT AVG(mark) AS avg_mark 
                      FROM grade g
                      JOIN student s ON g.student_id = s.student_id
                      WHERE s.class_id = $class_id AND g.subject_id = $subject_id AND g.test_num = $test";
            $result = mysqli_query($conn, $query);
            $avg = mysqli_fetch_assoc($result)['avg_mark'];
            $testAverages[] = $avg ? round($avg, 2) : 0;
        }

        // Overall average (for all tests combined)
        $overallAvgQuery = "SELECT AVG(mark) AS avg_mark 
                            FROM grade g
                            JOIN student s ON g.student_id = s.student_id
                            WHERE s.class_id = $class_id AND g.subject_id = $subject_id";
        $overallResult = mysqli_query($conn, $overallAvgQuery);
        $overallAvg = mysqli_fetch_assoc($overallResult)['avg_mark'];
        $overallAvgDisplay = $overallAvg ? round($overallAvg, 2) : 'N/A';
        $letter = is_numeric($overallAvg) ? letterGrade($overallAvg) : '-';

        // Save for combined chart
        $allClassAverages[] = is_numeric($overallAvg) ? round($overallAvg, 2) : 0;
    ?>
        <div class="class-section">
            <h2><?php echo $class_name; ?></h2>
            <div class="result">
                <p>Overall Average: <?php echo $overallAvgDisplay; ?></p>
                <p>Letter Grade: <?php echo $letter; ?></p>
            </div>
            <canvas id="chart_<?php echo $class_id; ?>"></canvas>

            <script>
                new Chart(document.getElementById('chart_<?php echo $class_id; ?>'), {
                    type: 'bar',
                    data: {
                        labels: ['Test 1', 'Test 2', 'Test 3'],
                        datasets: [{
                            label: 'Average Grade',
                            data: <?php echo json_encode($testAverages); ?>,
                            backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e'],
                            borderColor: '#2c2c2c',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true, max: 100 }
                        }
                    }
                });
            </script>
        </div>
    <?php endforeach; ?>
</div>

<div id="combinedChartContainer">
    <h2>Class Comparison</h2>
    <canvas id="combinedChart"></canvas>
    </div>

    <script>
        new Chart(document.getElementById('combinedChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_values($classes)); ?>,
                datasets: [{
                label: 'Overall Class Average',
                data: <?php echo json_encode($allClassAverages); ?>,
                backgroundColor: ['#36b9cc', '#f6c23e', '#4e73df'],
                borderColor: '#2c2c2c',
                borderWidth: 1
            }]
        },
                  options: {
                  responsive: true,
                  scales: {
                  y: { beginAtZero: true, max: 100 }
                  }
                  }
                  });
    </script>

    </body>
</html>
