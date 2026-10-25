<?php
$page_title = 'Login';
require_once 'config/database.php';
include 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/admin_dashboard.php');
                } else {
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                    header('Location: ' . $redirect);
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login error. Please try again.';
        }
    }
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12 flex items-center justify-center min-h-[60vh]">
    <div class="max-w-md w-full">
        <h1 class="font-display-lg text-4xl text-center mb-8">Login</h1>

        <?php if ($error): ?>
            <div class="bg-error/10 text-error p-4 mb-6 border border-error/20 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Email</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="email" name="email" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Password</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="password" name="password" required />
            </div>
            <button type="submit" class="w-full bg-primary text-white py-4 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                Login
            </button>
        </form>

        <p class="text-center text-sm text-on-surface-variant mt-6">
            Don't have an account? <a href="register.php" class="text-primary border-b border-primary pb-1">Register</a>
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>