<?php
$page_title = 'Register';
require_once 'config/database.php';
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, password, phone, address, role) 
                    VALUES (?, ?, ?, ?, ?, 'customer')
                ");
                $stmt->execute([$full_name, $email, $hashed_password, $phone, $address]);
                $success = 'Registration successful! Please login.';
            }
        } catch (PDOException $e) {
            $error = 'Registration error. Please try again.';
        }
    }
}
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12 flex items-center justify-center min-h-[60vh]">
    <div class="max-w-md w-full">
        <h1 class="font-display-lg text-4xl text-center mb-8">Register</h1>

        <?php if ($error): ?>
            <div class="bg-error/10 text-error p-4 mb-6 border border-error/20 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 text-green-700 p-4 mb-6 border border-green-200 text-center">
                <?php echo htmlspecialchars($success); ?>
                <br /><a href="login.php" class="underline font-bold">Login now</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Full Name *</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" name="full_name" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Email *</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="email" name="email" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Password *</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="password" name="password" required minlength="6" />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Confirm Password *</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="password" name="confirm_password" required />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Phone</label>
                <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" name="phone" />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Address</label>
                <textarea class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" name="address" rows="2"></textarea>
            </div>
            <button type="submit" class="w-full bg-primary text-white py-4 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                Register
            </button>
        </form>

        <p class="text-center text-sm text-on-surface-variant mt-6">
            Already have an account? <a href="login.php" class="text-primary border-b border-primary pb-1">Login</a>
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>