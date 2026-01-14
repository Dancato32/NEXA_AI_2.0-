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

// Verify teacher owns this assignment
$assignment_query = "SELECT a.*, t.firstName, t.lastName 
                    FROM assignments a
                    JOIN teachers_details t ON a.teacher_id = t.id
                    WHERE a.id = ? AND a.teacher_id = ?";
$stmt = mysqli_prepare($conn, $assignment_query);
mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assignment) {
    die("Assignment not found or you don't have permission to view it.");
}

// Get submissions for this assignment
$submissions_query = "SELECT 
    s.*,
    sd.firstName,
    sd.lastName,
    sd.UserName,
    sd.Class,
    CASE 
        WHEN s.status = 'graded' THEN 'graded'
        WHEN s.status = 'submitted' OR s.submitted_at IS NOT NULL THEN 'submitted'
        WHEN a.due_date < NOW() AND s.status = 'pending' THEN 'late'
        ELSE 'pending'
    END as display_status
FROM assignment_submissions s
JOIN student_details sd ON s.student_id = sd.id
JOIN assignments a ON s.assignment_id = a.id
WHERE s.assignment_id = ?
ORDER BY s.submitted_at DESC, sd.lastName, sd.firstName";

$stmt = mysqli_prepare($conn, $submissions_query);
mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$submissions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $submissions[] = $row;
}
mysqli_stmt_close($stmt);

// Get submission statistics
$stats = [
    'total' => count($submissions),
    'submitted' => 0,
    'graded' => 0,
    'pending' => 0,
    'late' => 0
];

foreach ($submissions as $submission) {
    if ($submission['display_status'] === 'graded') $stats['graded']++;
    elseif ($submission['display_status'] === 'submitted') $stats['submitted']++;
    elseif ($submission['display_status'] === 'late') $stats['late']++;
    else $stats['pending']++;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --secondary: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --black: #111827;
            --dark-gray: #374151;
            --medium-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
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
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: var(--white);
            padding: 30px;
        }
        
        .header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .assignment-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            padding: 25px;
            border-bottom: 1px solid var(--border);
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: var(--light-gray);
            border: 1px solid var(--border);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .submissions-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .submissions-table th {
            background: var(--light-gray);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark-gray);
            border-bottom: 2px solid var(--border);
        }
        
        .submissions-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .submissions-table tr:hover {
            background: var(--light-gray);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-submitted { background: #dbeafe; color: #1d4ed8; }
        .status-graded { background: #d1fae5; color: #047857; }
        .status-pending { background: #f3f4f6; color: #6b7280; }
        .status-late { background: #fee2e2; color: #dc2626; }
        
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-success { background: var(--secondary); color: var(--white); }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-secondary { background: var(--light-gray); color: var(--dark-gray); border: 1px solid var(--border); }
        
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: var(--white);
            border-radius: 16px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--medium-gray);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Assignment Submissions</h1>
            <p><?php echo htmlspecialchars($assignment['title']); ?></p>
            <div class="assignment-info">
                <p><strong>Subject:</strong> <?php echo htmlspecialchars($assignment['subject']); ?> • 
                   <strong>Class:</strong> <?php echo htmlspecialchars($assignment['class']); ?> • 
                   <strong>Due:</strong> <?php echo date('M d, Y h:i A', strtotime($assignment['due_date'])); ?></p>
            </div>
            <a href="teacher_assignment.php" class="btn btn-secondary" style="margin-top: 15px;">
                <i class="fas fa-arrow-left"></i> Back to Assignments
            </a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div>Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['submitted']; ?></div>
                <div>Submitted</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['graded']; ?></div>
                <div>Graded</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending'] + $stats['late']; ?></div>
                <div>Pending/Late</div>
            </div>
        </div>
        
        <div style="padding: 25px;">
            <?php if (empty($submissions)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <h3>No Submissions Yet</h3>
                    <p>Students haven't submitted their work for this assignment yet.</p>
                </div>
            <?php else: ?>
                <table class="submissions-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($submission['firstName'] . ' ' . $submission['lastName']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($submission['UserName']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($submission['Class']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $submission['display_status']; ?>">
                                        <?php echo ucfirst($submission['display_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($submission['submitted_at']): ?>
                                        <?php echo date('M d, Y h:i A', strtotime($submission['submitted_at'])); ?>
                                    <?php else: ?>
                                        <em>Not submitted</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($submission['score'] !== null): ?>
                                        <strong><?php echo $submission['score']; ?>/<?php echo $assignment['total_points']; ?></strong>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <?php if ($submission['submitted_at']): ?>
                                            <button class="btn btn-primary view-submission-btn" 
                                                    data-student-name="<?php echo htmlspecialchars($submission['firstName'] . ' ' . $submission['lastName']); ?>"
                                                    data-submission-id="<?php echo $submission['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if ($submission['display_status'] !== 'graded'): ?>
                                                <a href="grade_assignments.php?id=<?php echo $assignment_id; ?>&student=<?php echo $submission['student_id']; ?>" 
                                                   class="btn btn-success">
                                                    <i class="fas fa-check-circle"></i> Grade
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Submission View Modal -->
    <div class="modal" id="submissionModal">
        <div class="modal-content">
            <div class="modal-header" style="padding: 25px; border-bottom: 1px solid var(--border);">
                <h3 id="modalTitle">Submission Details</h3>
                <button class="close-modal" onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 25px;" id="submissionContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
        function closeModal() {
            document.getElementById('submissionModal').classList.remove('active');
        }
        
        document.querySelectorAll('.view-submission-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-submission-id');
                const studentName = this.getAttribute('data-student-name');
                
                document.getElementById('modalTitle').textContent = `Submission: ${studentName}`;
                
                // Load submission content via AJAX
                fetch(`get_submission.php?id=${submissionId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('submissionContent').innerHTML = data;
                        document.getElementById('submissionModal').classList.add('active');
                    })
                    .catch(error => {
                        document.getElementById('submissionContent').innerHTML = '<p>Error loading submission details.</p>';
                        document.getElementById('submissionModal').classList.add('active');
                    });
            });
        });
        
        // Close modal when clicking outside
        document.getElementById('submissionModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>