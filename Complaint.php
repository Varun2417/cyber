
<?php
   
session_start();

// Check if the user is logged in via session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
} elseif (isset($_COOKIE['email_cookie'])) {
    // If not, check if a "Remember Me" cookie exists
    $user = $_COOKIE['email_cookie'];
    $_SESSION['email'] = $email; // Re-establish the session
} else {
    // If neither exists, redirect to the login page
    header('Location: log.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $uname     = $_POST['uname'] ?? '';
    $email     = $_POST['email'] ?? '';
    $category  = $_POST['category'] ?? '';
    $complaint = $_POST['complaint'] ?? '';
    
    // Generate unique issue ID (W-TID)
    $datePart = date('Ymd');
    $randomPart = strtoupper(bin2hex(random_bytes(3))); // 6-character random string
    $issueId = "W-TID-" . $datePart . "-" . $randomPart;

    // === 1. Save to Database ===
$servername = "sql105.infinityfree.com";
$username   = "if0_40100119";
$password   = "varungajjala08";
$database   = "if0_40100119_login";

    $con = new mysqli($servername, $username, $password , $database);

    if ($con->connect_error) {
        die("❌ DB Connection failed: " . $con->connect_error);
    }

    // Use prepared statement (safer)
    $stmt = $con->prepare("INSERT INTO complaint(issue_id, uname, email, category, complaint) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss",$issueId, $uname, $email, $category, $complaint);

    if (!$stmt->execute()) {
        die("❌ Database insert failed: " . $stmt->error);
    }

    $stmt->close();
    $con->close();

    // === 2. Send Confirmation Email ===
    $mail = new PHPMailer(true);

    try {
        // SMTP (Brevo)
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '966b3c001@smtp-brevo.com';
        $mail->Password   = 'mXwvV3cqNtLBI8Sy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & recipients
        $mail->setFrom('cubetillu@gmail.com', 'Cyber Security Agent');
        $mail->addAddress($email);              // confirmation to user
        $mail->addAddress('demo01476@gmail.com'); // copy to you

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Complaint Confirmation - Issue ID: ' . $issueId;
        $mail->Body    = "
            <h2>Complaint Received</h2>
            <p>Dear " . htmlspecialchars($uname) . ",</p>
            <p>We have received your complaint and assigned it the following tracking ID:</p>
            <h3 style='color: #3498db;'>" . $issueId . "</h3>
            <p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>
            <p><strong>Complaint Details:</strong></p>
            <blockquote>" . nl2br(htmlspecialchars($complaint)) . "</blockquote>
            <p>Our team will investigate this matter and provide updates via email.</p>
            <p>You can use your Issue ID to track the status of your complaint on our website.</p>
            <p>Thank you for bringing this to our attention,</p>
            <p><strong>Cyber Security Team</strong></p>
        ";

        $mail->send();

        // === 3. Redirect back with success ===
        echo "<script>
                alert('✅ Complaint received! Your Issue ID is: " . $issueId . ". A confirmation email has been sent to $email.');
                window.location.href='Complaint.php';
              </script>";
    } catch (Exception $e) {
        echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    // If accessed directly
    header("Location: Complaint.php");
    exit();
}
?> <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaint Form</title>
    <style>
        /* Your existing styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0c0c14 0%, #1a1a2e 100%);
            color: #e6e6e6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .form-container {
            background: rgba(30, 30, 46, 0.7);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 30px rgba(0, 255, 200, 0.1);
            border: 1px solid rgba(0, 255, 200, 0.2);
            backdrop-filter: blur(12px);
            position: relative;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 5px;
            width: 100%;
            background: linear-gradient(90deg, #00ffc8, #00b3ff);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .form-container:hover::before {
            transform: scaleX(1);
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            background: linear-gradient(90deg, #00ffc8, #00b3ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientText 3s linear infinite;
        }

        @keyframes gradientText {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #b8b8cc;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #e6e6e6;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #00ffc8;
            box-shadow: 0 0 8px rgba(0, 255, 200, 0.4);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00ffc8, #00b3ff);
            color: #0c0c14;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #00b3ff, #00ffc8);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: -1;
        }

        button:hover::before {
            opacity: 1;
        }

        button:hover {
            box-shadow: 0 0 20px rgba(0, 255, 200, 0.5);
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }

        /* Popup Modal */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: #1a1a2e;
            color: #e6e6e6;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 255, 200, 0.5);
            z-index: 999;
            transition: transform 0.3s ease;
            text-align: center;
            width: 90%;
            max-width: 400px;
        }

        .popup.show {
            transform: translate(-50%, -50%) scale(1);
        }

        .popup h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #00ffc8;
        }

        .popup p {
            margin-bottom: 20px;
        }

        .popup button {
            background: #00ffc8;
            color: #0c0c14;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .popup button:hover {
            background: #00b3ff;
        }

        /* New back button styles */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            color: #00ffc8;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .back-button:hover {
            color: #00b3ff;
            text-shadow: 0 0 8px rgba(0, 255, 200, 0.5);
        }

        .back-button::before {
            content: '←';
            margin-right: 8px;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <a href="main.php" class="back-button">Back to Home</a>
        
        <h2>Submit a Complaint</h2>
        <form action="Complaint.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="uname" placeholder="Enter your username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="service">Service</option>
                <option value="billing">Billing</option>
                <option value="technical">Technical</option>
                <option value="other">Other</option>
            </select>

            <label for="description">Description</label>
            <textarea id="description" name="complaint" placeholder="Describe your issue in detail..." required></textarea>

            <button type="submit">Submit Complaint</button>
        </form>
    </div>

    <!-- Popup Message -->
    <div class="popup" id="popupBox">
        <h3>Complaint Submitted</h3>
        <p>Thank you for submitting your complaint. We have received it and will look into the matter.</p>
        <button onclick="closePopup()">Back</button>
    </div>

    <script>
        // Show popup if URL has ?success=1
        window.onload = function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('success') === '1') {
                document.getElementById('popupBox').classList.add('show');
            }
        }

        function closePopup() {
            window.location.href = window.location.pathname; // Reload without query
        }
    </script>

</body>
</html>