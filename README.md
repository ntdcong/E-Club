# Club Management System 🎯

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

A comprehensive web-based system for managing clubs, events, and member activities. This platform streamlines club administration, event organization, and member engagement in an intuitive interface.

## 🌟 Key Features

### For Administrators
- Complete club management system
- User role management
- Event approval system
- Comprehensive statistics and analytics
- Club leader assignment

### For Club Leaders
- Member management
- Event creation and management
- Post creation and management
- Notification system
- Attendance tracking

### For Members
- Club discovery and joining
- Event participation
- Activity tracking
- Profile management
- Real-time notifications

## 🚀 Installation

1. Clone the repository to your XAMPP's htdocs directory:
```bash
git clone [repository-url] club_management
```

2. Import the database:
- Navigate to phpMyAdmin
- Create a new database named 'club_management'
- Import the SQL file from `DB/database.sql`

3. Configure the application:
- Update database credentials in `config.php`
- Configure email settings in `config/phpmailer_config.php`
- Set up Cloudinary credentials in `config/cloudinary.php`

4. Start your XAMPP server and access the application:
```
http://localhost/club_management
```

## 💻 Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap
- **Email Service:** PHPMailer
- **File Storage:** Cloudinary
- **Additional Libraries:**
  - GuzzleHTTP
  - PHPSpreadsheet
  - Monolog

## 📱 Features Overview

### Dashboard
- Real-time statistics
- Activity monitoring
- Quick access to key functions

### Club Management
- Create and manage clubs
- Member approval system
- Activity tracking
- Post management

### Event Management
- Event creation and scheduling
- Attendance tracking
- Event statistics
- Approval workflow

### User Management
- Role-based access control
- Profile management
- Activity history
- Email notifications

## 🔒 Security Features

- Prepared SQL statements
- Input sanitization
- Session management
- Role-based access control
- Secure file uploads

## 🌐 Localization

- Multi-language support
- Currently supports:
  - English (en)
  - Vietnamese (vi)

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For support, please contact the development team or raise an issue in the repository.

---

Made with ❤️ for better club management