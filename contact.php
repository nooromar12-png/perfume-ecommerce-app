<?php
$page_title = 'Contact';
require_once 'config/database.php';
include 'includes/header.php';

$success = $_SESSION['contact_success'] ?? '';
$error = $_SESSION['contact_error'] ?? '';
unset($_SESSION['contact_success'], $_SESSION['contact_error']);
?>

<main class="max-w-[1280px] mx-auto px-6 md:px-16 py-12">
    <h1 class="font-display-lg text-4xl md:text-6xl text-center mb-4">Contact Us</h1>
    <p class="text-center text-on-surface-variant text-lg mb-12">
        We'd love to hear from you. Reach out with any questions or feedback.
    </p>

    <?php if ($success): ?>
        <div class="max-w-3xl mx-auto bg-green-50 text-green-700 p-4 mb-6 border border-green-200 text-center">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="max-w-3xl mx-auto bg-error/10 text-error p-4 mb-6 border border-error/20 text-center">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
        <div>
            <form method="POST" action="scripts/contact_process.php" class="space-y-6">
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Full Name *</label>
                    <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="text" name="name" required />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Email Address *</label>
                    <input class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" type="email" name="email" required />
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Subject</label>
                    <select class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" name="subject">
                        <option value="Inquiry">Inquiry</option>
                        <option value="Complaint">Complaint</option>
                        <option value="Suggestion">Suggestion</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-widest text-on-surface-variant block mb-1">Message *</label>
                    <textarea class="w-full border border-outline-variant bg-transparent px-4 py-3 input-field" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-4 uppercase text-sm tracking-widest hover:opacity-90 transition-all">
                    Send Message
                </button>
            </form>
        </div>

        <div>
            <h2 class="font-display-lg text-2xl mb-6 border-b border-outline-variant pb-4">Get in Touch</h2>
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-3xl text-primary">location_on</span>
                    <div>
                        <p class="font-bold">Address</p>
                        <p class="text-on-surface-variant">123 Luxury Avenue<br />gaza, dair albalah</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-3xl text-primary">email</span>
                    <div>
                        <p class="font-bold">Email</p>
                        <p class="text-on-surface-variant">nooralbuhaisi@lessence.com</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-3xl text-primary">phone</span>
                    <div>
                        <p class="font-bold">Phone</p>
                        <p class="text-on-surface-variant">+972 59123456</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-3xl text-primary">schedule</span>
                    <div>
                        <p class="font-bold">Working Hours</p>
                        <p class="text-on-surface-variant">Mon-Fri: 9:00 AM - 9:00 PM</p>
                        <p class="text-on-surface-variant">Sat-Sun: 10:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>