# Handbook - Social Media Application

![Handbook Logo](assets/images/handbook.png)

## Introduction

Handbook is a social media application that allows users to connect with each other, share posts, and interact with content. It features a user-friendly interface and an admin panel for managing the application.

## Features

- **User Authentication:** Sign up, log in, and log out.
- **Profile Management:** Edit your profile, including your name, username, and profile picture.
- **Social Interaction:** Follow and unfollow users, block and unblock users.
- **Content Sharing:** Create posts with text and images.
- **Engagement:** Like and unlike posts, comment on posts.
- **Notifications:** Receive notifications for likes, comments, and follows.
- **Admin Panel:** Manage users and posts.

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/handbook.git
    ```
2.  **Import the database:**
    - Create a new database named `handbook`.
    - Import the `handbook.sql` file into the `handbook` database.
3.  **Configure the database connection:**
    - Open `assets/php/config.php` and update the database credentials.
4.  **Configure the email credentials:**
    - Open `assets/php/smtp_config.php` and update the email credentials.
5.  **Run the application:**
    - Place the project in your web server's root directory (e.g., `htdocs` for XAMPP).
    - Open your web browser and navigate to `http://localhost/Tuongtac`.

## Usage

- **Sign up:** Create a new account.
- **Log in:** Access your account.
- **Wall:** View posts from users you follow.
- **Profile:** View your profile and posts.
- **Edit Profile:** Update your profile information.

## Admin Panel

- **Access:** `http://localhost/Tuongtac/admin`
- **Login:** Use your admin credentials to log in.
- **Dashboard:** View statistics about the application.
- **Manage Users:** Verify, block, and unblock users.
- **Manage Posts:** Delete posts.

## Database Schema

- **users:** Stores user information.
- **posts:** Stores post information.
- **likes:** Stores information about likes on posts.
- **comments:** Stores comments on posts.
- **follow_list:** Stores information about user follows.
- **block_list:** Stores information about blocked users.
- **notifications:** Stores user notifications.

## Technologies Used

- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap
- **Database:** MySQL
- **Email:** PHPMailer

