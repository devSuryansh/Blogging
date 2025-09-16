<?php
// index.php
require 'config.php';
$user = current_user();
$pdo = pdo();

$page = $_GET['page'] ?? 'home';

if ($page === 'home') {
  // search & filter
  $q = trim($_GET['q'] ?? '');
  $tag = trim($_GET['tag'] ?? '');
  $where = [];
  $params = [];
  if ($q !== '') {
    $where[] = "tsv @@ plainto_tsquery('english', :q)";
    $params['q'] = $q;
  }
  if ($tag !== '') {
    $where[] = ":tag = ANY(tags)";
    $params['tag'] = $tag;
  }
  $sql = "SELECT p.*, u.display_name, u.email FROM posts p JOIN users u ON p.author_id=u.id";
  if ($where) $sql .= " WHERE " . implode(" AND ", $where);
  $sql .= " ORDER BY p.created_at DESC LIMIT 50";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $posts = $stmt->fetchAll();
  // fetch all tags for sidebar
  $tags = $pdo->query("SELECT unnest(array_agg(DISTINCT tag)) as tag FROM (SELECT unnest(coalesce(tags, ARRAY[]::text[])) as tag FROM posts) t")->fetchAll(PDO::FETCH_ASSOC);
?>
  <!doctype html>
  <html>

  <head>
    <meta charset="utf-8">
    <title>Beautiful Blog - Read & Share Ideas</title>
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
          <div class="flex items-center space-x-4 animate-slide-in">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
              B
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Beautiful Blog</h1>
              <p class="text-sm text-gray-500">Read & share ideas</p>
            </div>
          </div>

          <!-- Navigation -->
          <nav class="flex items-center space-x-6">
            <?php if ($user): ?>
              <span class="text-sm text-gray-600">
                Hello, <span class="font-medium text-gray-900"><?= htmlspecialchars($user['display_name'] ?? $user['email']) ?></span>
              </span>
              <a href="?page=dashboard" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
              <a href="logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Logout</a>
            <?php else: ?>
              <a href="?page=login" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Login</a>
              <a href="?page=register" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">Sign up</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Posts Section -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Search Bar -->
          <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
            <form method="get" class="flex flex-col sm:flex-row gap-4">
              <input type="hidden" name="page" value="home">
              <div class="flex-1">
                <input
                  type="text"
                  name="q"
                  placeholder="Search posts..."
                  value="<?= htmlspecialchars($q) ?>"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
              </div>
              <button class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                Search
              </button>
            </form>
          </div>

          <!-- Posts List -->
          <div class="space-y-6">
            <?php foreach ($posts as $p): ?>
              <article class="bg-white rounded-2xl shadow-elegant p-6 hover-scale hover:shadow-glow transition-all duration-300 animate-fade-in">
                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-medium">
                      <?= strtoupper(substr($p['display_name'], 0, 1)) ?>
                    </div>
                    <span class="font-medium"><?= htmlspecialchars($p['display_name']) ?></span>
                  </div>
                  <time class="text-gray-400"><?= date('M j, Y', strtotime($p['created_at'])) ?></time>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight">
                  <a href="?page=view&id=<?= urlencode($p['id']) ?>" class="hover:text-indigo-600 transition-colors">
                    <?= htmlspecialchars($p['title']) ?>
                  </a>
                </h3>

                <p class="text-gray-600 mb-4 leading-relaxed">
                  <?= htmlspecialchars($p['excerpt'] ?? substr(strip_tags($p['content']), 0, 200)) ?>...
                </p>

                <?php if (!empty($p['tags'])): ?>
                  <div class="flex flex-wrap gap-2">
                    <?php foreach ($p['tags'] ?? [] as $t): ?>
                      <a href="?page=home&tag=<?= urlencode($t) ?>"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors">
                        #<?= htmlspecialchars($t) ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>

            <?php if (empty($posts)): ?>
              <div class="bg-white rounded-2xl shadow-elegant p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No posts found</h3>
                <p class="text-gray-500">Be the first to share your ideas!</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <!-- Sidebar -->
        <aside class="space-y-6">
          <!-- Tags Section -->
          <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              Popular Tags
            </h4>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($tags as $t): if (!$t['tag']) continue; ?>
                <a href="?page=home&tag=<?= urlencode($t['tag']) ?>"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 transition-colors">
                  #<?= htmlspecialchars($t['tag']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- About Section -->
          <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border border-indigo-100 animate-fade-in">
            <h4 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              About Beautiful Blog
            </h4>
            <p class="text-gray-600 text-sm leading-relaxed mb-4">
              A modern, lightweight blogging platform built with PHP & PostgreSQL. Share your thoughts, connect with readers, and build your community.
            </p>
            <div class="space-y-3">
              <?php if ($user): ?>
                <a href="?page=new" class="block w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-center px-4 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                  <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  Create New Post
                </a>
              <?php else: ?>
                <a href="?page=login" class="block w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-center px-4 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                  <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                  </svg>
                  Sign in to Publish
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Stats Card -->
          <div class="bg-white rounded-2xl shadow-elegant p-6 animate-fade-in">
            <h4 class="text-lg font-bold text-gray-900 mb-4">Community Stats</h4>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 text-sm">Total Posts</span>
                <span class="font-bold text-indigo-600"><?= count($posts) ?></span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 text-sm">Popular Tags</span>
                <span class="font-bold text-purple-600"><?= count($tags) ?></span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center">
          <p class="text-gray-600 text-sm">
            Built with ❤️ using PHP & PostgreSQL —
            <span class="font-medium">Secure by default</span>
            (prepared statements, hashed passwords)
          </p>
          <div class="mt-4 flex justify-center space-x-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              Secure
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
              </svg>
              Responsive
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
              Modern
            </span>
          </div>
        </div>
      </div>
    </footer>
  </body>

  </html>
<?php
  exit;
}

// Route: view single post
if ($page === 'view') {
  $id = $_GET['id'] ?? '';
  $stmt = $pdo->prepare("SELECT p.*, u.display_name FROM posts p JOIN users u ON p.author_id=u.id WHERE p.id = :id");
  $stmt->execute(['id' => $id]);
  $p = $stmt->fetch();
  if (!$p) {
    http_response_code(404);
    echo "Not found";
    exit;
  }

  // get comments & like count
  $cstmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = :id ORDER BY created_at ASC");
  $cstmt->execute(['id' => $id]);
  $comments = $cstmt->fetchAll();

  $lstmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = :id");
  $lstmt->execute(['id' => $id]);
  $likes = $lstmt->fetchColumn();

  $userLiked = false;
  if ($user) {
    $ul = $pdo->prepare("SELECT 1 FROM likes WHERE post_id=:id AND user_id=:uid");
    $ul->execute(['id' => $id, 'uid' => $user['id']]);
    $userLiked = (bool)$ul->fetch();
  }
?>
  <!doctype html>
  <html>

  <head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($p['title']) ?> - Beautiful Blog</title>
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
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Brand -->
          <div class="flex items-center space-x-4">
            <a href="index.php" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                B
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">Beautiful Blog</h1>
                <p class="text-sm text-gray-500">by <?= htmlspecialchars($p['display_name']) ?></p>
              </div>
            </a>
          </div>

          <!-- Navigation -->
          <nav class="flex items-center space-x-6">
            <?php if ($user): ?>
              <span class="text-sm text-gray-600">
                Hi <span class="font-medium text-gray-900"><?= htmlspecialchars($user['display_name'] ?? $user['email']) ?></span>
              </span>
              <a href="?page=dashboard" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
              <a href="logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Logout</a>
            <?php else: ?>
              <a href="?page=login" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">Login</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <article class="bg-white rounded-2xl shadow-elegant overflow-hidden animate-fade-in">
        <!-- Article Header -->
        <header class="p-8 pb-6 border-b border-gray-100">
          <div class="mb-6">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight mb-4">
              <?= htmlspecialchars($p['title']) ?>
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
              <div class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-medium">
                  <?= strtoupper(substr($p['display_name'], 0, 1)) ?>
                </div>
                <span class="font-medium"><?= htmlspecialchars($p['display_name']) ?></span>
              </div>
              <span class="text-gray-400">•</span>
              <time class="text-gray-500"><?= date('M j, Y', strtotime($p['created_at'])) ?></time>
              <?php if ($p['category']): ?>
                <span class="text-gray-400">•</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                  <?= htmlspecialchars($p['category']) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($p['tags'])): ?>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($p['tags'] ?? [] as $t): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                  #<?= htmlspecialchars($t) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </header>

        <!-- Article Content -->
        <div class="p-8">
          <div class="prose prose-lg max-w-none">
            <?= $p['content'] /* content is stored as HTML from editor */ ?>
          </div>
        </div>

        <!-- Actions -->
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-4">
            <form id="likeForm" method="post" action="like.php">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="post_id" value="<?= htmlspecialchars($p['id']) ?>">
              <?php if ($user): ?>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition-all <?= $userLiked ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                  <?= $userLiked ? "❤️ Liked" : "🤍 Like" ?>
                  <span class="ml-2 bg-white px-2 py-1 rounded-full text-xs font-bold"><?= intval($likes) ?></span>
                </button>
              <?php else: ?>
                <a href="?page=login" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                  🤍 Login to like
                  <span class="ml-2 bg-white px-2 py-1 rounded-full text-xs font-bold"><?= intval($likes) ?></span>
                </a>
              <?php endif; ?>
            </form>

            <?php if ($user && $user['id'] === $p['author_id']): ?>
              <div class="flex items-center space-x-3">
                <a href="?page=edit&id=<?= urlencode($p['id']) ?>"
                  class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium hover:bg-blue-200 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Edit
                </a>
                <form method="post" action="delete.php" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                  <button type="submit"
                    onclick="return confirm('Are you sure you want to delete this post?')"
                    class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium hover:bg-red-200 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <!-- Comments Section -->
      <section class="mt-8 bg-white rounded-2xl shadow-elegant p-8 animate-fade-in">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
          <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          Comments (<?= count($comments) ?>)
        </h3>

        <!-- Comments List -->
        <div class="space-y-6 mb-8">
          <?php foreach ($comments as $cm): ?>
            <div class="border-l-4 border-indigo-200 pl-6 py-4 bg-gray-50 rounded-r-xl">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center text-white text-sm font-medium">
                    <?= strtoupper(substr($cm['author_name'], 0, 1)) ?>
                  </div>
                  <span class="font-medium text-gray-900"><?= htmlspecialchars($cm['author_name']) ?></span>
                </div>
                <time class="text-sm text-gray-500"><?= date('M j, Y H:i', strtotime($cm['created_at'])) ?></time>
              </div>
              <div class="text-gray-700 leading-relaxed">
                <?= nl2br(htmlspecialchars($cm['content'])) ?>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (empty($comments)): ?>
            <div class="text-center py-8">
              <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
              </div>
              <h4 class="text-lg font-medium text-gray-900 mb-2">No comments yet</h4>
              <p class="text-gray-500">Be the first to share your thoughts!</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Add Comment Form -->
        <div class="border-t border-gray-200 pt-8">
          <h4 class="text-lg font-bold text-gray-900 mb-4">Add a comment</h4>
          <form method="post" action="comment.php" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="post_id" value="<?= htmlspecialchars($p['id']) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Name *</label>
                <input type="text"
                  name="author_name"
                  required
                  placeholder="Enter your name"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email (optional)</label>
                <input type="email"
                  name="author_email"
                  placeholder="your@email.com"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Comment *</label>
              <textarea name="content"
                required
                rows="4"
                placeholder="Share your thoughts..."
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>

            <div class="flex justify-end">
              <button type="submit"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Post Comment
              </button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </body>

  </html>
<?php
  exit;
}

// route: login/register/new/edit/dashboard handled below by includes
if ($page === 'login') {
  include 'login.php';
  exit;
}
if ($page === 'register') {
  include 'register.php';
  exit;
}
if ($page === 'dashboard') {
  include 'dashboard.php';
  exit;
}
if ($page === 'new') {
  include 'editor.php';
  exit;
}
if ($page === 'edit') {
  include 'editor.php';
  exit;
}

// fallback
http_response_code(404);
echo "Page not found";
