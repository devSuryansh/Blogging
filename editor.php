<?php
// editor.php - new and edit
require_once 'config.php';
$pdo = pdo();
$user = current_user();
if (!$user) {
  header('Location: ?page=login');
  exit;
}

$id = $_GET['id'] ?? null;
$editing = (bool)$id;
$errors = [];

if ($editing) {
  $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=:id AND author_id=:uid");
  $stmt->execute(['id' => $id, 'uid' => $user['id']]);
  $post = $stmt->fetch();
  if (!$post) {
    die("Not found or permission denied");
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!check_csrf($_POST['csrf'] ?? '')) {
    $errors[] = "Invalid CSRF";
  }
  $title = trim($_POST['title'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $tags_raw = trim($_POST['tags'] ?? '');
  $tags = array_values(array_filter(array_map('trim', explode(',', $tags_raw))));
  $content = $_POST['content'] ?? '';
  $excerpt = trim($_POST['excerpt'] ?? '');
  if ($title === '' || $content === '') $errors[] = "Title and content required";

  if (!$errors) {
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($title)) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    if ($editing) {
      $stmt = $pdo->prepare("UPDATE posts SET title=:title, slug=:slug, content=:content, excerpt=:excerpt, category=:cat, tags=:tags, updated_at=now() WHERE id=:id AND author_id=:uid");
      $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'excerpt' => $excerpt,
        'cat' => $category,
        'tags' => $tags,
        'id' => $id,
        'uid' => $user['id']
      ]);
      header('Location: ?page=view&id=' . urlencode($id));
      exit;
    } else {
      $stmt = $pdo->prepare("INSERT INTO posts (author_id, title, slug, content, excerpt, category, tags) VALUES (:uid,:title,:slug,:content,:excerpt,:cat,:tags) RETURNING id");
      $stmt->execute([
        'uid' => $user['id'],
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'excerpt' => $excerpt,
        'cat' => $category,
        'tags' => $tags
      ]);
      $new = $stmt->fetch();
      header('Location: ?page=view&id=' . urlencode($new['id']));
      exit;
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title><?= $editing ? 'Edit Post' : 'Create New Post' ?> - Beautiful Blog</title>
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
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Brand -->
        <div class="flex items-center space-x-4">
          <a href="index.php" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
              B
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Beautiful Blog</h1>
              <p class="text-sm text-gray-500">Write something beautiful</p>
            </div>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="flex items-center space-x-4">
          <a href="?page=dashboard" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
          <a href="logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Logout</a>
        </nav>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl shadow-elegant animate-fade-in">
      <!-- Header -->
      <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <?= $editing ? 'Edit Post' : 'Create New Post' ?>
          </h2>
          <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              Auto-save enabled
            </span>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="p-6">
        <?php if (!empty($errors)): ?>
          <div class="mb-6 space-y-2">
            <?php foreach ($errors as $e): ?>
              <div class="bg-red-50 border border-red-200 rounded-lg p-3 flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700 text-sm"><?= htmlspecialchars($e) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Post Title *</label>
            <input type="text"
              name="title"
              required
              value="<?= htmlspecialchars($post['title'] ?? '') ?>"
              placeholder="Enter an engaging title..."
              class="w-full px-4 py-3 text-lg border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
          </div>

          <!-- Meta Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <input type="text"
                name="category"
                value="<?= htmlspecialchars($post['category'] ?? '') ?>"
                placeholder="e.g., Technology, Lifestyle, Travel"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
              <input type="text"
                name="tags"
                value="<?= htmlspecialchars(is_array($post['tags']) ? join(',', $post['tags']) : '') ?>"
                placeholder="tag1, tag2, tag3"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
              <p class="mt-1 text-xs text-gray-500">Separate multiple tags with commas</p>
            </div>
          </div>

          <!-- Excerpt -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt (Preview)</label>
            <input type="text"
              name="excerpt"
              value="<?= htmlspecialchars($post['excerpt'] ?? '') ?>"
              placeholder="A brief summary of your post..."
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            <p class="mt-1 text-xs text-gray-500">This will be shown in post previews</p>
          </div>

          <!-- Content Editor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
              <!-- Editor Toolbar -->
              <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center space-x-2">
                <button type="button" onclick="formatText('bold')" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Bold">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h5a3 3 0 110 6H4v4a1 1 0 11-2 0V5zM4 8h5a1 1 0 100-2H4v2z" clip-rule="evenodd"></path>
                  </svg>
                </button>
                <button type="button" onclick="formatText('italic')" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Italic">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                  </svg>
                </button>
                <div class="w-px h-6 bg-gray-300"></div>
                <button type="button" onclick="formatText('formatBlock', 'h1')" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">H1</button>
                <button type="button" onclick="formatText('formatBlock', 'h2')" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">H2</button>
                <button type="button" onclick="formatText('formatBlock', 'p')" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">P</button>
              </div>

              <!-- Editor Content -->
              <div id="editor"
                contenteditable="true"
                class="min-h-[400px] p-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 prose prose-lg max-w-none"
                placeholder="Start writing your post..."><?= $editing ? $post['content'] : '<p><br></p>' ?></div>
            </div>
            <textarea name="content" id="content" class="hidden"></textarea>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-500">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Your post will be saved automatically as you type
            </div>

            <div class="flex items-center space-x-3">
              <a href="?page=dashboard"
                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                Cancel
              </a>
              <button type="submit"
                onclick="syncEditor()"
                class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                <?= $editing ? 'Update Post' : 'Publish Post' ?>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    // Editor functionality
    function syncEditor() {
      const ed = document.getElementById('editor');
      document.getElementById('content').value = ed.innerHTML;
    }

    function formatText(command, value = null) {
      document.execCommand(command, false, value);
      document.getElementById('editor').focus();
    }

    // Auto-save functionality
    let autoSaveTimer;
    document.getElementById('editor').addEventListener('input', function() {
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(syncEditor, 1000);
    });

    // Sync before page unload
    window.addEventListener('beforeunload', function() {
      syncEditor();
    });

    // Enhanced editor styling
    document.getElementById('editor').addEventListener('focus', function() {
      this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.2)';
    });

    document.getElementById('editor').addEventListener('blur', function() {
      this.style.boxShadow = 'none';
      syncEditor();
    });

    // Add placeholder behavior
    const editor = document.getElementById('editor');
    if (editor.innerHTML.trim() === '<p><br></p>' || editor.innerHTML.trim() === '') {
      editor.classList.add('empty');
    }

    editor.addEventListener('input', function() {
      if (this.innerHTML.trim() === '<p><br></p>' || this.innerHTML.trim() === '') {
        this.classList.add('empty');
      } else {
        this.classList.remove('empty');
      }
    });
  </script>

  <style>
    .prose h1 {
      font-size: 2rem;
      font-weight: 700;
      margin: 1.5rem 0 1rem;
    }

    .prose h2 {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 1.25rem 0 0.75rem;
    }

    .prose p {
      margin: 0.75rem 0;
      line-height: 1.7;
    }

    .prose strong {
      font-weight: 600;
    }

    .prose em {
      font-style: italic;
    }

    #editor.empty:before {
      content: "Start writing your post...";
      color: #9ca3af;
      pointer-events: none;
    }

    #editor:focus.empty:before {
      display: none;
    }
  </style>
</body>

</html>