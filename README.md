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

Screenshot:
<img width="1214" height="750" alt="Screenshot 2025-07-28 150426" src="https://github.com/user-attachments/assets/55d67c75-ef57-4f51-a506-356338ad1cc7" />
<img width="1209" height="737" alt="Screenshot 2025-07-28 150439" src="https://github.com/user-attachments/assets/768eb6df-e308-496c-845b-8c1810bed7d0" />
<img width="1210" height="748" alt="Screenshot 2025-07-28 150448" src="https://github.com/user-attachments/assets/f30b988f-490c-4750-bf60-43114a141dba" />

(Dashboard)
<img width="1919" height="993" alt="Screenshot 2025-07-28 150326" src="https://github.com/user-attachments/assets/099522e1-5372-4706-80d6-73a37ef46f5b" />

(Profile)
<img width="1919" height="996" alt="Screenshot 2025-07-28 150340" src="https://github.com/user-attachments/assets/2cd186f7-0442-4959-8f8a-091432b56b9d" />

(Tasks)
<img width="1918" height="994" alt="Screenshot 2025-07-28 150350" src="https://github.com/user-attachments/assets/dd138139-2fbf-41fc-9e00-d744f5c9c8ef" />
<img width="1919" height="993" alt="Screenshot 2025-07-28 150357" src="https://github.com/user-attachments/assets/c84ec41f-56f0-4de9-9d1d-93f5dfc76966" />
<img width="1919" height="993" alt="Screenshot 2025-07-28 150405" src="https://github.com/user-attachments/assets/d4e9d8d7-2058-4b89-96e8-c79f1e34c452" />




