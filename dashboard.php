<?php
// dashboard.php
require_once 'config.php';
$user = current_user();
if (!$user) {
  header('Location: ?page=login');
  exit;
}
$pdo = pdo();
$stmt = $pdo->prepare("SELECT * FROM posts WHERE author_id = :uid ORDER BY created_at DESC");
$stmt->execute(['uid' => $user['id']]);
$posts = $stmt->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Dashboard - Beautiful Blog</title>
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
  <!-- Header -->
  <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Brand -->
        <div class="flex items-center space-x-4">
          <a href="index.php" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
              B
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
              <p class="text-sm text-gray-500"><?= htmlspecialchars($user['display_name'] ?? $user['email']) ?></p>
            </div>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="flex items-center space-x-4">
          <a href="?page=new" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Post
          </a>
          <a href="logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Logout</a>
        </nav>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Posts</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($posts) ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Published</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($posts) ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Status</p>
            <p class="text-2xl font-bold text-gray-900">Active</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Posts Management -->
    <div class="bg-white rounded-2xl shadow-elegant animate-fade-in">
      <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
            Your Posts
          </h3>
          <a href="?page=new" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create New
          </a>
        </div>
      </div>

      <div class="p-6">
        <?php if (count($posts) === 0): ?>
          <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h4>
            <p class="text-gray-500 mb-6">You haven't published any posts yet. Start sharing your ideas!</p>
            <a href="?page=new" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Write Your First Post
            </a>
          </div>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($posts as $p): ?>
              <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                      <a href="?page=view&id=<?= urlencode($p['id']) ?>" class="hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($p['title']) ?>
                      </a>
                    </h4>
                    <div class="flex items-center text-sm text-gray-500 mb-3">
                      <time class="mr-4"><?= date('M j, Y', strtotime($p['created_at'])) ?></time>
                      <?php if ($p['category']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                          <?= htmlspecialchars($p['category']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <?php if ($p['excerpt']): ?>
                      <p class="text-gray-600 text-sm line-clamp-2"><?= htmlspecialchars($p['excerpt']) ?></p>
                    <?php endif; ?>
                  </div>

                  <div class="flex items-center space-x-2 ml-4">
                    <a href="?page=edit&id=<?= urlencode($p['id']) ?>"
                      class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      Edit
                    </a>
                    <form method="post" action="delete.php" style="display:inline">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                      <button type="submit"
                        onclick="return confirm('Are you sure you want to delete this post?')"
                        class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>

</html>