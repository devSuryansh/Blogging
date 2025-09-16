<?php
// register.php
require_once 'config.php';
$pdo = pdo();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!check_csrf($_POST['csrf'] ?? '')) {
    $errors[] = "Invalid CSRF token";
  }
  $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
  $display = trim($_POST['display'] ?? '');
  $pass = $_POST['password'] ?? '';
  if (!$email) $errors[] = "Valid email required";
  if (strlen($pass) < 6) $errors[] = "Password must be at least 6 chars";
  if (!$errors) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    try {
      $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, display_name) VALUES (:email, :hash, :display) RETURNING id");
      $stmt->execute(['email' => $email, 'hash' => $hash, 'display' => $display]);
      $row = $stmt->fetch();
      $_SESSION['user_id'] = $row['id'];
      session_regenerate_id(true);
      header('Location: index.php');
      exit;
    } catch (PDOException $e) {
      if (strpos($e->getMessage(), 'duplicate') !== false) $errors[] = "Email already in use";
      else $errors[] = "DB error";
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Create Account - Beautiful Blog</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            'inter': ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 min-h-screen font-inter">
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center animate-fade-in">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg">
          B
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Create your account</h2>
        <p class="text-gray-600">Join our community and start sharing your ideas</p>
      </div>

      <!-- Registration Form -->
      <div class="bg-white rounded-2xl shadow-elegant p-8 animate-fade-in">
        <?php if (!empty($errors)): ?>
          <div class="mb-6 space-y-2">
            <?php foreach ($errors as $err): ?>
              <div class="bg-red-50 border border-red-200 rounded-lg p-3 flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700 text-sm"><?= htmlspecialchars($err) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
            <input type="email"
              name="email"
              required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
              placeholder="your@email.com">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
            <input type="text"
              name="display"
              value="<?= htmlspecialchars($_POST['display'] ?? '') ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
              placeholder="How others will see you">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
            <input type="password"
              name="password"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
              placeholder="At least 6 characters">
            <p class="mt-1 text-xs text-gray-500">Must be at least 6 characters long</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button type="submit"
              class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              Create Account
            </button>
            <a href="index.php"
              class="flex-shrink-0 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition-colors text-center">
              Cancel
            </a>
          </div>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Already have an account?
            <a href="?page=login" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Sign in</a>
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center text-sm text-gray-500 animate-fade-in">
        <p>By creating an account, you agree to our Terms of Service and Privacy Policy</p>
      </div>
    </div>
  </div>
</body>

</html>