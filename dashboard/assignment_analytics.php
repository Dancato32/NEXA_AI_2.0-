<?php
session_start();

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../Frontend/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Database connection
require_once '../includes/config.php';

// Get assignment details
$assignment_query = "SELECT a.*, COUNT(s.id) as total_submissions 
                    FROM assignments a
                    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id
                    WHERE a.id = ? AND a.teacher_id = ?
                    GROUP BY a.id";
$stmt = mysqli_prepare($conn, $assignment_query);
mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assignment) {
    die("Assignment not found or you don't have permission to view it.");
}

// Get submission statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN s.status = 'graded' THEN 1 END) as graded,
    COUNT(CASE WHEN s.status = 'submitted' THEN 1 END) as submitted,
    COUNT(CASE WHEN s.status = 'pending' AND a.due_date < NOW() THEN 1 END) as late,
    AVG(s.score) as avg_score,
    MIN(s.score) as min_score,
    MAX(s.score) as max_score,
    STDDEV(s.score) as std_dev
FROM assignment_submissions s
JOIN assignments a ON s.assignment_id = a.id
WHERE s.assignment_id = ? AND s.submitted_at IS NOT NULL";

$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Get grade distribution
$distribution_query = "SELECT 
    CASE 
        WHEN score >= 90 THEN 'A (90-100)'
        WHEN score >= 80 THEN 'B (80-89)'
        WHEN score >= 70 THEN 'C (70-79)'
        WHEN score >= 60 THEN 'D (60-69)'
        ELSE 'F (Below 60)'
    END as grade_range,
    COUNT(*) as count
FROM assignment_submissions 
WHERE assignment_id = ? AND score IS NOT NULL
GROUP BY 
    CASE 
        WHEN score >= 90 THEN 'A (90-100)'
        WHEN score >= 80 THEN 'B (80-89)'
        WHEN score >= 70 THEN 'C (70-79)'
        WHEN score >= 60 THEN 'D (60-69)'
        ELSE 'F (Below 60)'
    END
ORDER BY 
    CASE 
        WHEN score >= 90 THEN 1
        WHEN score >= 80 THEN 2
        WHEN score >= 70 THEN 3
        WHEN score >= 60 THEN 4
        ELSE 5
    END";

$stmt = mysqli_prepare($conn, $distribution_query);
mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$distribution = [];
$max_distribution = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $distribution[] = $row;
    $max_distribution = max($max_distribution, $row['count']);
}
mysqli_stmt_close($stmt);

// Get submission timeline
$timeline_query = "SELECT 
    DATE(s.submitted_at) as submission_date,
    COUNT(*) as count
FROM assignment_submissions s
WHERE s.assignment_id = ? AND s.submitted_at IS NOT NULL
GROUP BY DATE(s.submitted_at)
ORDER BY submission_date";

$stmt = mysqli_prepare($conn, $timeline_query);
mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$timeline = [];
while ($row = mysqli_fetch_assoc($result)) {
    $timeline[] = $row;
}
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Analytics • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --black: #111827;
            --dark-gray: #374151;
            --medium-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFF;
            color: var(--black);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: var(--white);
            padding: 30px;
        }
        
        .content { padding: 30px; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }
        
        .chart-container {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark-gray);
        }
        
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            height: 200px;
            padding: 20px 0;
        }
        
        .bar {
            flex: 1;
            background: linear-gradient(to top, var(--primary), #6366f1);
            border-radius: 4px 4px 0 0;
            position: relative;
            min-height: 10px;
        }
        
        .bar-label {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8rem;
            color: var(--medium-gray);
        }
        
        .timeline-chart {
            display: flex;
            gap: 10px;
            height: 150px;
            align-items: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Assignment Analytics</h1>
            <p><?php echo htmlspecialchars($assignment['title']); ?></p>
            <a href="view_submissions.php?id=<?php echo $assignment_id; ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Submissions
            </a>
        </div>
        
        <div class="content">
            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['graded'] ?? 0; ?></div>
                    <div class="stat-label">Graded</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['submitted'] ?? 0; ?></div>
                    <div class="stat-label">Submitted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['late'] ?? 0; ?></div>
                    <div class="stat-label">Late</div>
                </div>
            </div>
            
            <!-- Score Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['avg_score'] ?? 0, 1); ?></div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['max_score'] ?? '-'; ?></div>
                    <div class="stat-label">Highest Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['min_score'] ?? '-'; ?></div>
                    <div class="stat-label">Lowest Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['std_dev'] ?? 0, 1); ?></div>
                    <div class="stat-label">Score Deviation</div>
                </div>
            </div>
            
            <!-- Grade Distribution Chart -->
            <div class="chart-container">
                <div class="chart-title">Grade Distribution</div>
                <div class="bar-chart">
                    <?php foreach ($distribution as $item): ?>
                        <?php 
                        $height = $max_distribution > 0 ? ($item['count'] / $max_distribution * 100) : 0;
                        $color = match(true) {
                            str_contains($item['grade_range'], 'A') => '#10b981',
                            str_contains($item['grade_range'], 'B') => '#3b82f6',
                            str_contains($item['grade_range'], 'C') => '#f59e0b',
                            str_contains($item['grade_range'], 'D') => '#f97316',
                            default => '#ef4444'
                        };
                        ?>
                        <div class="bar" style="height: <?php echo $height; ?>%; background: <?php echo $color; ?>;">
                            <div class="bar-label">
                                <?php echo $item['grade_range']; ?><br>
                                <?php echo $item['count']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Submission Timeline -->
            <?php if (!empty($timeline)): ?>
            <div class="chart-container">
                <div class="chart-title">Submission Timeline</div>
                <div class="timeline-chart">
                    <?php foreach ($timeline as $item): ?>
                        <?php 
                        $max_timeline = max(array_column($timeline, 'count'));
                        $height = $max_timeline > 0 ? ($item['count'] / $max_timeline * 100) : 0;
                        ?>
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 30px; height: <?php echo $height; ?>%; background: linear-gradient(to top, var(--primary), #6366f1); border-radius: 4px 4px 0 0;"></div>
                            <div style="font-size: 0.8rem; color: var(--medium-gray); margin-top: 5px;">
                                <?php echo date('m/d', strtotime($item['submission_date'])); ?><br>
                                <strong><?php echo $item['count']; ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Additional Information -->
            <div class="chart-container">
                <div class="chart-title">Assignment Details</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($assignment['subject']); ?></p>
                        <p><strong>Class:</strong> <?php echo htmlspecialchars($assignment['class']); ?></p>
                        <p><strong>Total Points:</strong> <?php echo $assignment['total_points']; ?></p>
                    </div>
                    <div>
                        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?></p>
                        <p><strong>Due Date:</strong> <?php echo date('M d, Y h:i A', strtotime($assignment['due_date'])); ?></p>
                        <p><strong>Status:</strong> 
                            <?php if (strtotime($assignment['due_date']) < time()): ?>
                                <span style="color: var(--danger);">Completed</span>
                            <?php else: ?>
                                <span style="color: var(--secondary);">Active</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add hover effects to charts
        document.querySelectorAll('.bar').forEach(bar => {
            bar.addEventListener('mouseover', function() {
                this.style.opacity = '0.8';
            });
            bar.addEventListener('mouseout', function() {
                this.style.opacity = '1';
            });
        });
    </script>
</body>
</html>