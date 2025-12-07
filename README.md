# 🦅 AQR QA Platform

> A modern, premium Question & Answer survey platform built with performance and aesthetics in mind.

![Project Status](https://img.shields.io/badge/status-active-success.svg)
![PHP Version](https://img.shields.io/badge/php-%5E8.2-777BB4.svg)
![Docker](https://img.shields.io/badge/docker-ready-2496ED.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## 📖 Overview

**AQR QA** is a lightweight yet powerful feedback and survey collection system. It features a stunning, responsive front-end design using **Glassmorphism** principles and a robust admin dashboard for managing content.

Built with native **PHP 8.2**, **TailwindCSS**, and **Alpine.js**, it delivers a seamless experience without the bloat of heavy frameworks.

## ✨ Key Features

### 🎨 User Interface

- **Premium Design**: Modern aesthetic with soft shadows, glassmorphism, and smooth transitions.
- **Responsive**: Fully optimized for mobile, tablet, and desktop devices.
- **Dynamic Interactions**: Powered by Alpine.js for instant feedback and smooth UI states.
- **Persian/RTL Support**: Native support for Right-to-Left languages with the beautiful Vazirmatn font.

### 🛠 Administrative Power

- **Dashboard**: Real-time statistics on categories, questions, and user responses.
- **Content Management**: Create, edit, and manage question categories and individual questions.
- **Question Types**: Support for multiple input types:
  - Text & Textarea
  - Boolean (Yes/No)
  - Select & Multi-select
- **Secure Access**: Protected admin panel with session-based authentication.

### 🏗 Architecture

- **Dockerized**: specific `docker-compose` setup for instant deployment.
- **Database**: efficient MySQL schema with foreign key constraints.
- **Zero-Build**: Utilizing CDN-based libraries for Tailwind and Alpine (dev-mode friendly).

## 🚀 Getting Started

### Prerequisites

- [Docker](https://www.docker.com/products/docker-desktop) and Docker Compose installed on your machine.

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/aqr-qa.git
   cd aqr-qa
   ```

2. **Start the application**
   Run the containers in detached mode:

   ```bash
   docker-compose up -d
   ```

3. **Initialize the Database**
   The database schema (`database.sql`) is automatically applied on the first run. To populate the database with sample data:
   ```bash
   # Enter the app container
   docker-compose exec app php seed.php
   ```

### Accessing the App

| Service                   | URL                           | Credentials (Default)             |
| ------------------------- | ----------------------------- | --------------------------------- |
| **Public Interface**      | `http://localhost:8088`       | N/A                               |
| **Admin Panel**           | `http://localhost:8088/admin` | User: `admin`<br>Pass: `admin123` |
| **Database (phpMyAdmin)** | `http://localhost:8081`       | User: `root`<br>Pass: `root123`   |

## 📂 Project Structure

```
aqr-qa/
├── admin/          # Admin panel pages (Dashboard, Login, Questions)
├── api/            # API endpoints for async operations
├── config/         # Database and app configuration
├── includes/       # Helper functions and shared logic
├── public/         # Public-facing survey pages
├── docker-compose.yml # Docker services definition
└── seed.php        # Database seeder script
```

## 🛠 Tech Stack

- **Backend**: PHP 8.2 (PDO MySQL)
- **Frontend**: HTML5, TailwindCSS 3, Alpine.js 3
- **Database**: MySQL 8.0
- **Server**: Apache (via Docker image)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.
