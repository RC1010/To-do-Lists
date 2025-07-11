# To-do-Lists
# 📝 To-Do List App

A simple and user-friendly To-Do List web application to help users manage their tasks efficiently. Built with PHP, MySQL, Bootstrap, and AJAX.

## 🚀 Features

- ✅ User Authentication (Login/Register)
- 📂 User-specific Categories
- 🗂️ Task Management (Create, Edit, Delete)
- 🎯 Task Prioritization (Low, Medium, High)
- 📊 Task Status Tracking (Not Started, In Progress, Completed)
- 🔍 Category Filtering and Pagination
- ⚙️ AJAX-based Pagination for smooth experience
- 🖼️ User Profile with Image Upload
- 📅 Sort by Due Date and Priority

🔍 What This App Implements
This To-Do List App implements the classic CRUD operations and follows common web application architecture patterns:

✅ 1. CRUD Functionality
Your app uses CRUD for both Tasks and Categories:

- Create (C)	Add new tasks and categories
- Read (R)	View task lists, categories, and user-specific dashboards
- Update (U)	Edit tasks (titles, categories, status, priority) and categories
- Delete (D)	Remove tasks and categories

✅ 2. User Authentication
- Login & Registration system
- Session management using PHP sessions
- Only authenticated users can access and manage their tasks and categories
- Each user can only see their own data (user-specific categories & tasks)

✅ 3. Relational Database Design (MySQL)
- Users Table: Stores user information (name, email, password, etc.)
- Tasks Table: Contains task data with foreign keys linking to users and categories
- Categories Table: Allows users to group their tasks

✅ 4. Modern Web Technologies
- Bootstrap: Responsive and consistent UI design
- AJAX: Pagination for categories without reloading the entire page
- PHP & MySQL: Backend logic and database management
- Prepared Statements: Secure queries to prevent SQL injection

✅ 5. Optional Enhancements
- File upload for profile images
- Sorted task lists by priority and due date
- Pagination and word truncation for better category management

Architecture Summary:
- Frontend: HTML, CSS, Bootstrap, JavaScript (AJAX)
- Backend: PHP 8.x
- Database: MySQL (XAMPP or other stack)
- Architecture: CRUD, MVC-like separation, Secure login system
