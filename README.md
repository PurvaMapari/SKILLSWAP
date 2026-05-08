# SkillSwap

SkillSwap is a skill exchange platform where users can teach what they know and learn what they want — without paying money. Users can create skill offers, explore other users’ skills, send swap requests, and connect through collaborative learning.

---

# Features

* User Authentication (Login & Signup)
* Create Skill Offers & Learning Interests
* Explore Skills from Other Users
* Send & Receive Swap Requests
* User Profiles with Skills & Bio
* Dashboard for Managing Requests
* Messaging Interface
* Reviews & Ratings System
* Responsive Modern UI

---

# Tech Stack

* Frontend: HTML5, CSS3, JavaScript
* Backend: PHP
* Database: MySQL
* Version Control: Git & GitHub

---

# Project Structure

```bash
SKILLSWAP/
│
├── api/
│   ├── config.php
│   ├── login.php
│   ├── signup.php
│   ├── profile.php
│   ├── skills.php
│   ├── swap_request.php
│   ├── createoffer.php
│   ├── messages.php
│   ├── reviews.php
│   ├── contact.php
│   ├── getuserskill.php
│   ├── getteacher.php
│   └── skillswap_db_full.sql
│
├── css/
│   ├── main.css
│   └── components.css
│
├── js/
│
├── images/
│
├── pages/
│   ├── home.html
│   ├── explore.html
│   ├── dashboard.html
│   ├── profile.html
│   ├── other-profile.html
│   ├── skill-detail.html
│   ├── swap-detail.html
│   ├── swap-request.html
│   ├── messages.html
│   ├── about.html
│   └── contact.html
│
├── index.html
├── login.html
├── signup.html
├── header.html
└── README.md
```

---

# Database

The project uses MySQL database.

Database file:

```bash
api/skillswap_db_full.sql
```

Main tables:

* users
* skills
* swap_requests
* messages
* reviews
* contact_messages

---

# Setup Instructions

## 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/SKILLSWAP.git
```

---

## 2. Open Project

Open the project folder in:

* VS Code
* Cursor
* Any code editor

---

## 3. Setup Database

1. Open phpMyAdmin or MySQL
2. Create database:

```sql
CREATE DATABASE skillswap_db;
```

3. Import:

```bash
api/skillswap_db_full.sql
```

---

## 4. Configure Database Connection

Open:

```bash
api/config.php
```

Update credentials:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "skillswap_db";
```

---

## 5. Run Project

Start:

* Apache
* MySQL

using:

* XAMPP
* MAMP
* WAMP

Then open:

```bash
http://localhost/SKILLSWAP/
```

---

# Core Flow

1. User creates skills they can offer or want to learn
2. Skills appear on Explore page
3. Another user sends swap request
4. Request is stored in database
5. Receiver sees request in Dashboard
6. Users can communicate and collaborate

---

# Pages

| Page         | Purpose                    |
| ------------ | -------------------------- |
| Landing Page | Platform introduction      |
| Explore      | Browse skills & users      |
| Profile      | User profile & skills      |
| Dashboard    | Manage swap requests       |
| Messages     | Chat interface             |
| Swap Request | Create exchange request    |
| Skill Detail | Detailed skill information |

---

# Future Improvements

* Real-time chat
* Notifications
* Skill matching algorithm
* Video call integration
* Wishlist system
* Admin dashboard
* Firebase/Auth integration
* AI-powered recommendations

---

# Developed By

Purva Mapari
Saket Kapileshwari
