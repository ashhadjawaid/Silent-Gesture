# Silent Gesture Recognition Emergency Safety Web Application

A web-based emergency safety system designed for users in distress who cannot speak or make calls. By scanning webcam feeds, the application detects custom hand gestures and silently triggers emergency alerts (notifications, email alerts, and local logs) after validating a 3-second hold.

---

## 🚀 Key Features

*   **Silent Gesture Detection:** Powered by MediaPipe Hands to track and validate user hand gestures.
*   **3-Second Hold Validation:** Prevents accidental hand movements from triggering false alarms.
*   **Remembered Session Support:** Secure login system with a tokenized "Remember Me" checkbox.
*   **Dual Database Support:** Native support for MySQL with an automatic fallback to a local SQLite database for portable and offline execution.
*   **Offline Mode:** Emergency logs and local operations function perfectly even without an active internet connection.

---

## 🛠️ Technologies Used

### Frontend
*   HTML5 & CSS3
*   Vanilla JavaScript
*   Bootstrap 5 (UI Framework)
*   MediaPipe Hands (Gesture tracking API)

### Backend & Database
*   PHP (Session management, routing, form verification)
*   MySQL / SQLite (Database engines)
*   PDO (PHP Data Objects for database queries)

---

## 📂 Project Structure

```text
├── config.php          # Session security & Database configurations (PDO setup)
├── database.sql        # MySQL table schema
├── index.php           # Project introduction and home landing page
├── register.php        # Encrypted registration form
├── login.php           # User login with secure session cookie setup
├── gesture_setup.php   # Interactive selector cards for triggering gestures
├── dashboard.php       # Live status monitor with mock webcam input
├── settings.php        # Option panels to manage gestures & camera toggles
├── logout.php          # Session termination & cookie revocation
├── style.css           # Custom theme colors (Professional Dark Blue & Red)
└── README.md           # Deployment documentation
```

---

## 💻 How to Run the Project

### Option A: Using the Portable PHP Server (Recommended & Pre-configured)

This repository includes a portable, pre-configured PHP environment. You can spin up the project instantly without installing any external web server.

1.  Open your command line / Terminal in the project root folder.
2.  Run the following command to start the built-in PHP web server:
    ```bash
    .\php_bin\php.exe -S localhost:8000
    ```
3.  Open your browser and navigate to:
    ```text
    http://localhost:8000/index.php
    ```
4.  *Note: The system will automatically detect if MySQL is offline and save your data directly inside a portable `database.sqlite` file in the project folder. No database setup is needed!*

### Option B: Using XAMPP

1.  Copy the project folder into your XAMPP web root directory (usually `C:\xampp\htdocs\`).
2.  Rename the folder to `Silent-Gesture` or similar.
3.  Open the **XAMPP Control Panel** and click **Start** next to **Apache**.
4.  Open your browser and navigate to:
    ```text
    http://localhost/Silent-Gesture/index.php
    ```
5.  *(Optional)* To use MySQL, start **MySQL** in XAMPP, open `http://localhost/phpmyadmin/`, create a database named `silent_emergency`, and import the structure from `database.sql`.

---

## 🔒 Security Specifications

*   **Password Protection:** Passwords are fully hashed using `password_hash($password, PASSWORD_BCRYPT)` and checked using `password_verify()`.
*   **SQL Injection Prevention:** All SQL interactions are handled using **PDO Prepared Statements**.
*   **Remember Me Mechanism:** Employs a cryptographically secure 256-bit token (`random_bytes`) stored in browser cookies under the `httpOnly` secure flag.
