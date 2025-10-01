
require_once $_SERVER['DOCUMENT_ROOT'] . '/esang_delicacies/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$email = isset($_POST['email']) ? $_POST['email'] : (isset($_GET['email']) ? $_GET['email'] : null);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

$otp = rand(100000, 999999);
session_start();
$_SESSION['otp_' . $email] = $otp;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'YOUR_GMAIL@gmail.com'; // Your Gmail address
    $mail->Password   = 'YOUR_APP_PASSWORD';    // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('YOUR_GMAIL@gmail.com', 'Esang Delicacies');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body    = "Your OTP code is: <b>$otp</b>";
    $mail->AltBody = "Your OTP code is: $otp";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'OTP sent to email successfully.', 'otp' => $otp]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Mailer Error: ' . $mail->ErrorInfo,
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
