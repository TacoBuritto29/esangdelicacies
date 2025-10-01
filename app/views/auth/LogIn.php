<?php
require_once("session.php");
require_once __DIR__ . '/../_bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_role = $_POST['login_role'] ?? 'customer';
    $login_input = trim($_POST['login_input'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login_input !== '' && $password !== '') {
        // Hardcoded admin login for demonstration
        if ($login_role === 'admin' && $login_input === 'capstoneesang@gmail.com' && $password === 'october29_mary') {
            $_SESSION['role'] = 'ADMIN';
            $_SESSION['adminId'] = 1;
            header('Location: ../../views/admin/admin_dashboard.php');
            exit;
        }

        $mysqli = db();
        $roles = [
            'admin' => ['table' => 'ADMIN', 'id' => 'empId', 'field' => 'name', 'redirect' => '../../views/admin/admin_dashboard.php', 'sessionKey' => 'adminId'],
            'cashier' => ['table' => 'CASHIER', 'id' => 'empId', 'field' => 'name', 'redirect' => '../../views/cashier/cashier_walk_in.php', 'sessionKey' => 'cashierId'],
            'rider' => ['table' => 'RIDER', 'id' => 'empId', 'field' => 'email', 'redirect' => '../../views/rider/order_assignments.php', 'sessionKey' => 'riderId'],
            'order_manager' => ['table' => 'ORDER_MANAGER', 'id' => 'empId', 'field' => 'name', 'redirect' => '../../views/order_manager/order_management.php', 'sessionKey' => 'orderManagerId'],
            'customer' => ['table' => 'CUSTOMER', 'id' => 'customerId', 'field' => 'email', 'redirect' => '../../views/customer/customer_dashboard.php', 'sessionKey' => 'customerId'],
        ];
        // $roles = [
        //     'admin' => [
        //         'table' => 'ADMIN',
        //         'id' => 'empId',
        //         'field' => 'name',
        //         'redirect' => '../../views/admin/admin_dashboard.php',
        //         'sessionKey' => 'adminId'
        //     ],
        //     'cashier' => [
        //         'table' => 'CASHIER',
        //         'id' => 'empId',
        //         'field' => 'name',
        //         'redirect' => '/esang_delicacies/app/views/auth/2FA.php',
        //         'sessionKey' => 'cashierId'
        //     ],
        //     'rider' => [
        //         'table' => 'RIDER',
        //         'id' => 'empId',
        //         'field' => 'email',
        //         'redirect' => '/esang_delicacies/app/views/auth/2FA.php',
        //         'sessionKey' => 'riderId'
        //     ],
        //     'order_manager' => [
        //         'table' => 'ORDER_MANAGER',
        //         'id' => 'empId',
        //         'field' => 'name',
        //         'redirect' => '../../views/order_manager/order_management.php',
        //         'sessionKey' => 'orderManagerId'
        //     ],
        //     'customer' => [
        //         'table' => 'CUSTOMER',
        //         'id' => 'customerId',
        //         'field' => 'email',
        //         'redirect' => '/esang_delicacies/app/views/auth/2FA.php',
        //         'sessionKey' => 'customerId'
        //     ],
        // ];

        $role = $roles[$login_role] ?? $roles['customer'];
        $stmt = $mysqli->prepare("SELECT {$role['id']}, email, verified FROM {$role['table']} WHERE {$role['field']} = ? AND password = ? LIMIT 1");
        $stmt->bind_param('ss', $login_input, $password);
        if ($stmt->execute()) {
            $stmt->bind_result($foundId, $email, $verified);
            if ($stmt->fetch()) {
                $_SESSION['role'] = strtoupper($login_role);
                $_SESSION[$role['sessionKey']] = (int)$foundId;
                $_SESSION['email'] = $email;
                $stmt->close();
                if ($verified == 1) {
                    header('Location: ' . $role['redirect']);
                } else {
                    $_SESSION["success"] = 2;
                    $_SESSION["message"] = "Account verification failed";
                    header("Refresh:0");
                }
                exit;
            }
        }
        $stmt->close();
        $error = 'Invalid credentials for selected role.';
    } else {
        $error = 'Please enter all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #fffdf9; }
    .btn-warning { background-color: #ffcc00; border: none; }
    .btn-warning:hover { background-color: #e6b800; }
    .text-danger { color: #d32f2f !important; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row min-vh-100">

    <!-- Left: Login Form -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-4">
      <div class="w-100" style="max-width: 400px;">
        <h1 class="text-center mb-4 fw-bold text-danger">Log In</h1>

        <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>


        <?php if ($_SESSION["success"]==1): ?>
          <div class="alert alert-success text-center"><?php echo $_SESSION["message"] ?></div>
          <?php $_SESSION["success"] = ""; ?>
        <?php elseif($_SESSION["success"]==2): ?>
          <div class="alert alert-danger text-center"><?php echo $_SESSION["message"] ?></div>
          <?php $_SESSION["success"]=""; ?>
        <?php endif; ?>
        <form method="POST" action="" class="row g-3">
          <div class="col-12">
            <select name="login_role" id="login_role" class="form-select" required>
              <option value="customer">Customer (Email)</option>
              <option value="rider">Rider (Email)</option>
              <option value="cashier">Cashier (Name)</option>
              <option value="admin">Admin (Name)</option>
              <option value="order_manager">Order Manager (Name)</option>
            </select>
          </div>
          <div class="col-12">
            <input type="text" name="login_input" id="login_input" class="form-control" placeholder="Email or Name" required>
          </div>
          <div class="col-12">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
          </div>
          <div class="col-12 d-grid">
            <button type="submit" class="btn btn-warning fw-bold py-2">Log In</button>
          </div>
        </form>

        <p class="text-center mt-3">
          No account? <a href="../auth/SignUp.php" class="text-danger fw-bold">Sign Up</a>
        </p>
      </div>
    </div>

    <!-- Right: Image -->
    <div class="col-lg-6 d-none d-lg-block p-0">
        <img src="../VImages/Food Poster.png" 
            alt="Esang Delicacies" 
            class="w-100 p-1" 
            style="height: 100vh; object-fit: center; object-position: center;">
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Dynamic placeholder update
document.getElementById('login_role').addEventListener('change', function() {
  var input = document.getElementById('login_input');
  if (this.value === 'customer' || this.value === 'rider') {
    input.placeholder = 'Email';
  } else {
    input.placeholder = 'Name';
  }
});
</script>
</body>
</html>
