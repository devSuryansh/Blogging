# 🌐 Blogging Platform

A **beautiful and responsive blogging application** built with **PHP, HTML, CSS, JS, and PostgreSQL**.  
It allows users to share their thoughts, interact with others, and explore blog posts in an intuitive interface.

---

## ✨ Features

- 🔓 **Public Access**

  - Browse and read blogs without logging in.
  - Search and filter blog posts by keywords, categories, or tags.

- 🔑 **User Authentication**

  - Sign up with email and password (securely stored with hashing).
  - Log in to create, edit, or delete your blog posts.
  - Session-based authentication for secure access.

- 📝 **Blog Management**

  - Rich editor to write and publish blogs.
  - Add **categories and tags** to organize posts.
  - Edit or delete blogs you own.

- 💬 **User Interactions**

  - Like and comment on posts.
  - View comments from other readers.
  - Engagement data stored for consistency.

- 🔍 **Search & Filter**

  - Quickly find blogs by keywords, categories, or tags.
  - Structured retrieval from PostgreSQL for efficiency.

- 🛡️ **Security**
  - Passwords stored as hashes.
  - SQL Injection protection using prepared statements.
  - Session management for authenticated operations.

---

## 🛠️ Tech Stack

- **Frontend:**

  - HTML5, CSS3, JavaScript (Vanilla JS)
  - Responsive design for mobile and desktop

- **Backend:**

  - PHP (Server-side rendering + authentication + blog logic)

- **Database:**
  - PostgreSQL for structured storage of:
    - Users (emails, hashed passwords, profile data)
    - Blogs (title, content, tags, categories, timestamps)
    - Comments
    - Likes/Interactions

---

## 📂 Project Structure

```md
/Blogging
│── index.php # Homepage displaying blogs
│── login.php # User login page
│── signup.php # User signup page
│── dashboard.php # User dashboard for managing blogs
│── create_post.php # Editor to create a new blog
│── edit_post.php # Edit existing blogs
│── view_post.php # Display full blog with comments
│── like_post.php # Handle likes
│── comment_post.php # Handle comments
│
├── assets/
│ ├── css/ # Stylesheets
│ ├── js/ # JavaScript files
│ └── images/ # App images
│
├── includes/
│ ├── db.php # Database connection (PostgreSQL)
│ ├── auth.php # Authentication functions
│ └── helpers.php # Utility functions
│
└── README.md # Project documentation
```

---

## ⚙️ Installation & Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/devSuryansh/Blogging.git
   cd Blogging
   ```

2. **Setup PostgreSQL Database**

   ```sql
   CREATE DATABASE blog_app;
   \c blog_app;

   -- Users Table
   CREATE TABLE users (
     id SERIAL PRIMARY KEY,
     email VARCHAR(255) UNIQUE NOT NULL,
     password VARCHAR(255) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   -- Blog Posts Table
   CREATE TABLE posts (
     id SERIAL PRIMARY KEY,
     user_id INT REFERENCES users(id) ON DELETE CASCADE,
     title VARCHAR(255) NOT NULL,
     content TEXT NOT NULL,
     category VARCHAR(100),
     tags TEXT[],
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   -- Comments Table
   CREATE TABLE comments (
     id SERIAL PRIMARY KEY,
     post_id INT REFERENCES posts(id) ON DELETE CASCADE,
     user_id INT REFERENCES users(id) ON DELETE CASCADE,
     comment TEXT NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   -- Likes Table
   CREATE TABLE likes (
     id SERIAL PRIMARY KEY,
     post_id INT REFERENCES posts(id) ON DELETE CASCADE,
     user_id INT REFERENCES users(id) ON DELETE CASCADE,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. **Configure Database Connection**
   Update `includes/db.php` with your PostgreSQL credentials:

   ```php
   <?php
   $host = "localhost";
   $port = "5432";
   $dbname = "blog_app";
   $user = "your_username";
   $password = "your_password";

   try {
       $db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
       $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
       echo "Error: " . $e->getMessage();
   }
   ?>
   ```

4. **Run the Application**

   - Place the project inside your local server directory (`htdocs` for XAMPP or `www` for WAMP).
   - Start Apache & PostgreSQL services.
   - Visit `http://localhost/blog-app`.

---

## 🚀 Future Improvements

- 🔒 JWT-based authentication (API-ready).
- 🖼️ Support for image uploads in blogs.
- 👤 User profile pages with bio and avatar.
- 📊 Admin dashboard for moderation.
- 🌍 Multi-language support.

---

## 🤝 Contributing

Contributions are welcome!
Please fork the repository, create a feature branch, and submit a pull request.

---

## 📜 License

This project is licensed under the **MIT License** – feel free to use and modify.
