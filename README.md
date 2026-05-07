# SkillSwap

A web platform where users exchange skills with one another instead of paying money. Teach what you know, learn what you want.

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend (coming soon):** PHP
- **Database (coming soon):** MySQL

## Frontend Setup

No server needed! Just open the files directly in your browser.

### Folder Structure
```
skillswap/
├── css/
│   ├── main.css
│   └── components.css
├── js/
│   └── main.js
├── pages/
│   ├── explore.html
│   ├── skill-detail.html
│   ├── profile.html
│   ├── other-profile.html
│   ├── swap-request.html
│   ├── dashboard.html
│   └── messages.html
├── index.html
├── login.html
└── signup.html
```

### Run Locally

Simply open `index.html` in your browser — or use VS Code Live Server:

1. Install the **Live Server** extension in VS Code
2. Right-click `index.html`
3. Select **Open with Live Server**

## Pages

- **Landing** (`index.html`) - Platform intro, featured skills, search
- **Explore** (`pages/explore.html`) - Browse skills with filters
- **Skill Detail** (`pages/skill-detail.html`) - View skill and teacher
- **Profile** (`pages/profile.html`) - Own profile with bio and skills
- **Other Profile** (`pages/other-profile.html`) - View another user's profile and leave a review
- **Swap Request** (`pages/swap-request.html`) - Send swap request with message
- **Dashboard** (`pages/dashboard.html`) - Incoming/outgoing requests, active swaps
- **Messages** (`pages/messages.html`) - Conversation with swap partner
- **Auth** (`login.html`, `signup.html`) - Login and Sign Up

## Frontend Features

- Responsive layout with mobile hamburger menu
- Skill cards grid with category badges
- Search and filter UI on explore page
- Auth forms (login & signup)
- Profile page with skills offered/wanted and reviews
- Dashboard with request status badges (pending/accepted/rejected)
- Message chat UI (sent/received bubbles)
- Star ratings on review cards