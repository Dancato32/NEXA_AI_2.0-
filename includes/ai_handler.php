<?php
// ai_handler.php - Place this in your includes folder

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors in JSON response

// Health check endpoint
if (isset($_GET['action']) && $_GET['action'] === 'health') {
    echo json_encode([
        'success' => true,
        'healthy' => true,
        'timestamp' => time()
    ]);
    exit;
}

// Main AI request handler
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action !== 'ask_ai') {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$question = trim($_POST['question'] ?? '');
$conversationHistory = json_decode($_POST['history'] ?? '[]', true);

if (!$question) {
    echo json_encode(['success' => false, 'error' => 'No question provided']);
    exit;
}

// Prepare API payload
$payload = json_encode([
    "prompt" => $question,
    "conversation_history" => $conversationHistory,
    "max_new_tokens" => 150,  // Changed from max_length
    "temperature" => 0.7
]);

// Your ngrok URL
$apiUrl = "https://rickety-superarduously-dawson.ngrok-free.dev/generate";

// Initialize cURL
$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Accept: application/json"
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($curlError) {
    error_log("cURL Error: " . $curlError);
    echo json_encode([
        'success' => false, 
        'error' => 'Connection error. Please check if the AI server is running.'
    ]);
    exit;
}

// Check HTTP status
if ($httpCode !== 200) {
    error_log("HTTP Error: Status " . $httpCode . " - Response: " . $response);
    echo json_encode([
        'success' => false, 
        'error' => 'AI server returned error (Status: ' . $httpCode . ')'
    ]);
    exit;
}

// Parse JSON response
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Parse Error: " . json_last_error_msg() . " - Response: " . $response);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid response from AI server'
    ]);
    exit;
}

// Check if response exists in data
if (!isset($data['response'])) {
    error_log("Missing response field in API data: " . print_r($data, true));
    echo json_encode([
        'success' => false, 
        'error' => 'AI response format error'
    ]);
    exit;
}

// Extract subject from question (simple detection)
$subject = detectSubject($question);

// Build enhanced response with educational features
$aiResponse = $data['response'];
$learningTip = null;
$followUpQuestion = null;

// Add educational enhancements based on subject
if (strpos(strtolower($question), 'how') !== false || 
    strpos(strtolower($question), 'why') !== false) {
    $followUpQuestion = "Would you like me to explain this in more detail or give you a practice problem?";
}

// Return successful response
echo json_encode([
    "success" => true,
    "response" => [
        "answer" => $aiResponse,
        "subject" => $subject,
        "learning_tip" => $learningTip,
        "follow_up_question" => $followUpQuestion
    ]
]);

/**
 * Simple subject detection from question
 */
function detectSubject($question) {
    $lower = strtolower($question);
    
    $subjects = [
        'math' => ['math', 'algebra', 'geometry', 'calculate', 'equation', 'solve', 'number'],
        'science' => ['science', 'chemistry', 'physics', 'biology', 'experiment', 'atom', 'cell'],
        'english' => ['english', 'grammar', 'writing', 'essay', 'sentence', 'paragraph', 'spell'],
        'history' => ['history', 'historical', 'war', 'civilization', 'ancient', 'century'],
        'geography' => ['geography', 'country', 'continent', 'map', 'capital', 'ocean']
    ];
    
    foreach ($subjects as $subject => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($lower, $keyword) !== false) {
                return ucfirst($subject);
            }
        }
    }
    
    return 'General';
}
?>