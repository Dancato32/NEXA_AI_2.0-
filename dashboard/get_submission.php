<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    http_response_code(403);
    exit();
}

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

require_once '../includes/config.php';

$query = "SELECT s.*, sd.firstName, sd.lastName, sd.Class, a.title as assignment_title
          FROM assignment_submissions s
          JOIN student_details sd ON s.student_id = sd.id
          JOIN assignments a ON s.assignment_id = a.id
          WHERE s.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $submission_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$submission = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$submission) {
    echo '<p>Submission not found.</p>';
    exit();
}
?>

<div style="padding: 20px;">
    <div style="margin-bottom: 20px;">
        <h4>Student Information</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($submission['firstName'] . ' ' . $submission['lastName']); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($submission['Class']); ?></p>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h4>Submission Details</h4>
        <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($submission['submitted_at'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($submission['status']); ?></p>
        <?php if ($submission['score'] !== null): ?>
            <p><strong>Score:</strong> <?php echo $submission['score']; ?></p>
        <?php endif; ?>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h4>Submission Text</h4>
        <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;">
            <?php echo nl2br(htmlspecialchars($submission['submission_text'] ?? 'No text submitted')); ?>
        </div>
    </div>
    
    <?php if ($submission['attachment_url']): ?>
    <div style="margin-bottom: 20px;">
        <h4>Attachments</h4>
        <a href="../<?php echo htmlspecialchars($submission['attachment_url']); ?>" target="_blank" 
           style="display: inline-flex; align-items: center; gap: 8px; padding: 10px; background: #3b82f6; color: white; border-radius: 6px; text-decoration: none;">
            <i class="fas fa-download"></i> Download Attachment
        </a>
    </div>
    <?php endif; ?>
    
    <?php if ($submission['feedback']): ?>
    <div style="margin-bottom: 20px;">
        <h4>Teacher Feedback</h4>
        <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
        </div>
    </div>
    <?php endif; ?>
</div>