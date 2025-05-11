<?php
session_start();

// Mock database using session
if (!isset($_SESSION['users'])) $_SESSION['users'] = [];
if (!isset($_SESSION['loans'])) $_SESSION['loans'] = [];
if (!isset($_SESSION['images'])) $_SESSION['images'] = [];

// --- Helper functions ---
function render_header($title) {
    echo "<!DOCTYPE html><html><head><title>$title</title><style>
        body { font-family: Arial, sans-serif; background: #eef5f0; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; padding: 20px;
                     border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; color: #006400; }
        form { margin-top: 20px; }
        input, select, button {
            width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px;
        }
        button { background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .message { background: #e0ffe0; padding: 10px; color: #006400; border-radius: 5px; text-align: center; }
        .error { background: #ffe0e0; color: #990000; }
        .linkbar { margin-bottom: 20px; text-align: center; }
        .linkbar a { margin: 0 10px; color: #006400; text-decoration: none; font-weight: bold; }
        .linkbar a:hover { text-decoration: underline; }
        footer { text-align: center; font-size: 0.9em; color: #777; margin-top: 20px; }
    </style></head><body><div class='container'>";
    echo "<h1>AgriCred: AI-driven Microfinance for Farmers</h1><hr>";
}

function render_footer() {
    echo "<hr><footer>&copy; 2025 AgriCred</footer></div></body></html>";
}

function crop_analysis_mock($image_path) {
    $types = ['Wheat', 'Rice', 'Maize'];
    $health = ['Good', 'Moderate', 'Poor'];
    return [
        'type' => $types[array_rand($types)],
        'health' => $health[array_rand($health)]
    ];
}

function calculate_credit_score($health) {
    return match($health) {
        'Good' => rand(750, 850),
        'Moderate' => rand(600, 749),
        'Poor' => rand(400, 599),
        default => 0
    };
}

// --- Routes ---
$action = $_GET['action'] ?? 'home';

if ($action == 'register') {
    render_header("Register");
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = ['id' => uniqid(), 'name' => $_POST['name'], 'email' => $_POST['email'], 'pass' => $_POST['pass']];
        $_SESSION['users'][] = $user;
        echo "<div class='message'>Registration successful. <a href='?action=login'>Login now</a></div>";
    } else {
        echo "<form method='post'>
            <input name='name' placeholder='Full Name' required><br>
            <input name='email' type='email' placeholder='Email' required><br>
            <input name='pass' type='password' placeholder='Password' required><br>
            <button>Register</button>
        </form>";
    }
    render_footer();

} elseif ($action == 'login') {
    render_header("Login");
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($_SESSION['users'] as $user) {
            if ($user['email'] == $_POST['email'] && $user['pass'] == $_POST['pass']) {
                $_SESSION['user'] = $user;
                header("Location: ?action=dashboard");
                exit;
            }
        }
        echo "<div class='error'>Invalid credentials.</div>";
    }
    echo "<form method='post'>
        <input name='email' type='email' placeholder='Email' required><br>
        <input name='pass' type='password' placeholder='Password' required><br>
        <button>Login</button>
    </form>";
    render_footer();

} elseif ($action == 'dashboard' && isset($_SESSION['user'])) {
    render_header("Dashboard");
    $user = $_SESSION['user'];
    echo "<div class='linkbar'>
        Welcome, <strong>{$user['name']}</strong> |
        <a href='?action=upload'>Upload Crop Image</a> |
        <a href='?action=loans'>View Loan Status</a> |
        <a href='?action=logout'>Logout</a>
    </div>";
    render_footer();

} elseif ($action == 'upload' && isset($_SESSION['user'])) {
    render_header("Upload Crop Image");
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
        $upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$path = $upload_dir . basename($_FILES['image']['name']);
if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
    $analysis = crop_analysis_mock($path);
    $score = calculate_credit_score($analysis['health']);

    $loan = [
        'user_id' => $_SESSION['user']['id'],
        'image' => $path,
        'crop' => $analysis['type'],
        'health' => $analysis['health'],
        'score' => $score,
        'status' => $score > 700 ? "Approved" : "Pending",
        'amount' => $score > 700 ? 50000 : 20000
    ];
    $_SESSION['loans'][] = $loan;

    echo "<div class='message'>
        Image analyzed successfully.<br>
        Crop: <strong>{$analysis['type']}</strong><br>
        Health: <strong>{$analysis['health']}</strong><br>
        Credit Score: <strong>$score</strong><br>
        Loan Status: <strong>{$loan['status']}</strong><br>
        Amount: ₹{$loan['amount']}<br>
        <a href='?action=dashboard'>Back to Dashboard</a>
    </div>";
} else {
    echo "<div class='error'>Failed to upload image. Please check file permissions.</div>";
}

        $analysis = crop_analysis_mock($path);
        $score = calculate_credit_score($analysis['health']);

        $loan = [
            'user_id' => $_SESSION['user']['id'],
            'image' => $path,
            'crop' => $analysis['type'],
            'health' => $analysis['health'],
            'score' => $score,
            'status' => $score > 700 ? "Approved" : "Pending",
            'amount' => $score > 700 ? 50000 : 20000
        ];
        $_SESSION['loans'][] = $loan;

        echo "<div class='message'>
            Image analyzed successfully.<br>
            Crop: <strong>{$analysis['type']}</strong><br>
            Health: <strong>{$analysis['health']}</strong><br>
            Credit Score: <strong>$score</strong><br>
            Loan Status: <strong>{$loan['status']}</strong><br>
            Amount: ₹{$loan['amount']}<br>
            <a href='?action=dashboard'>Back to Dashboard</a>
        </div>";
    } else {
        echo "<form method='post' enctype='multipart/form-data'>
            <input type='file' name='image' required><br>
            <button>Submit for Analysis</button>
        </form>";
    }
    render_footer();

} elseif ($action == 'loans' && isset($_SESSION['user'])) {
    render_header("Loan Status");
    foreach ($_SESSION['loans'] as $loan) {
        if ($loan['user_id'] == $_SESSION['user']['id']) {
            echo "<div class='message'>
                <b>Crop:</b> {$loan['crop']} |
                <b>Health:</b> {$loan['health']} |
                <b>Score:</b> {$loan['score']} |
                <b>Status:</b> {$loan['status']} |
                <b>Amount:</b> ₹{$loan['amount']}
            </div>";
        }
    }
    echo "<div class='linkbar'><a href='?action=dashboard'>Back to Dashboard</a></div>";
    render_footer();

} elseif ($action == 'logout') {
    session_destroy();
    header("Location: ?action=login");
    exit;

} else {
    render_header("Home");
    echo "<div class='linkbar'>
        <a href='?action=register'>Register</a> |
        <a href='?action=login'>Login</a>
    </div>";
    render_footer();
}
?>