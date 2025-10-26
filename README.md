# 🎓 SCCI Workshops Registration System

A simple and secure web-based system for managing workshop registrations for **SCCI Student Activity**.  
Participants can register for their preferred workshops, and each workshop has its own admin dashboard to view and export participant data securely.

---

## 🚀 Features

### 👥 Participant Side
- Register with **name, email, phone number**.
- Choose **first, second, and third workshop preferences** from available workshops:
  - Devology
  - Investnuer
  - Marketive
  - Techsolve
- Optionally add **technical skills**.
- All fields are validated before submission.
- Secure form with **server-side sanitization** and validation.

### 🛠 Admin Side
- Separate **login for each workshop admin**.
- Admins can:
  - View only their workshop’s participants.
  - See stats (total, first/second/third preferences, today’s registrations, participants with skills).
  - Search, filter, and export participant data as **CSV**.
- Secure **session management** and **CSRF protection**.
- Each login session expires after a timeout period for security.

---

## 🔐 Security Features
- **Hashed passwords** with `password_hash()` and `password_verify()`.
- **CSRF Token** to prevent form hijacking.
- **Session regeneration** on login to avoid session fixation.
- **Session timeout** (auto logout after inactivity).
- **Error handling** (PDO errors logged, not displayed to users).
- **Input sanitization** to prevent SQL injection and XSS.

---

## 🧩 Tech Stack

| Layer | Technology |
|-------|-------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP (PDO for database interaction) |
| Database | MySQL |

---

## ⚙️ Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/mahmoud530/SCCI-Workshops-Registration-System.git
