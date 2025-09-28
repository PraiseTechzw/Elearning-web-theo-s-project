# Campus IT Support System
**Chinhoyi University of Technology**

A modern, secure, and user-friendly web application for campus network and IT support services at Chinhoyi University of Technology (CUT).

## 🚀 Features

- **Secure Authentication**: Email-based login with token verification
- **Modern Dashboard**: Clean, responsive interface with quick actions
- **Session Management**: Secure user sessions with timeout protection
- **Mobile Responsive**: Works perfectly on all devices
- **Clean Architecture**: Organized folder structure and modular code
- **Security First**: SQL injection protection and input validation

## 📁 Project Structure

```
Elearning web/
├── assets/
│   ├── css/
│   │   ├── styles.css          # Main styles with CUT branding
│   │   ├── login.css           # Login page styles
│   │   └── dashboard.css       # Dashboard styles
│   ├── js/
│   │   ├── main.js             # Main JavaScript
│   │   ├── login.js            # Login functionality
│   │   ├── dashboard.js        # Dashboard functionality
│   │   └── contact.js          # Contact form functionality
│   └── images/                 # All images and assets
├── config/
│   ├── database.php            # Database configuration
│   └── campus_db_updated.sql   # Enhanced database schema
├── includes/
│   ├── Database.php            # Secure database connection class
│   ├── Auth.php                # Authentication system
│   ├── login.php               # Login handler
│   ├── verify.php              # Token verification
│   ├── session_check.php       # Session validation
│   └── logout.php              # Logout handler
├── pages/
│   ├── index.html              # Home page
│   ├── about.html              # About page
│   ├── login.html              # Login page
│   ├── dashboard.html          # User dashboard
│   ├── project-overview.html   # Project overview
│   ├── contact.html            # Contact & FAQ page
│   └── [other pages...]        # Additional pages
└── admin/                      # Admin panel
    ├── index.php               # Admin dashboard
    ├── login.php               # Admin login
    └── logout.php              # Admin logout
```

## 🛠️ Installation

### Local Development
1. **Clone the repository**
   ```bash
   git clone https://github.com/chinhoyi-university/cut-campus-it-support.git
   cd cut-campus-it-support
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Set up a web server** (Apache/Nginx with PHP support)
4. **Configure the database**:
   - Import `config/campus_db_updated.sql` into your MySQL database
   - Update `config/database.php` with your database credentials
5. **Configure email settings** in `config/database.php`
6. **Start development server**
   ```bash
   npm start
   ```

### Production Deployment

#### Option 1: GitHub Pages (Recommended)
1. **Push to GitHub**: Push your code to the `main` or `master` branch
2. **Automatic Deployment**: GitHub Actions will automatically build and deploy
3. **Access**: Your site will be available at `https://yourusername.github.io/cut-campus-it-support`

#### Option 2: Manual Deployment
1. **Build the project**
   ```bash
   npm run build
   ```
2. **Deploy**: Upload the `dist/` folder to your web server

#### Option 3: Direct Server Deployment
1. Upload all files to your web server
2. Configure your web server to serve the project
3. Set up database and configure `config/database.php`

## ⚙️ Configuration

### Database Setup
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'campus_db');
```

### Email Configuration
Update email settings in `config/database.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@campus.edu');
```

## 🔐 Security Features

- **Prepared Statements**: All database queries use prepared statements
- **Input Validation**: Server-side and client-side validation
- **Token-based Authentication**: Secure email verification system
- **Session Management**: Automatic timeout and secure session handling
- **XSS Protection**: Input sanitization and output escaping

## 🎨 Design Features

- **Modern UI**: Clean, professional design with smooth animations
- **Responsive Layout**: Works on desktop, tablet, and mobile
- **Accessibility**: Proper ARIA labels and keyboard navigation
- **Branding**: Praisetech signature throughout the application
- **User Experience**: Intuitive navigation and clear feedback

## 📱 Usage

1. **Home Page**: Visit `index.html` to see the landing page
2. **Login**: Click "Login" to access the secure login system
3. **Dashboard**: After email verification, access the user dashboard
4. **Navigation**: Use the responsive navigation menu to access different sections

## 🔧 Development

### Adding New Features
1. Create new PHP files in `includes/` for backend functionality
2. Add corresponding HTML pages in `pages/`
3. Update CSS in `assets/css/` for styling
4. Add JavaScript in `assets/js/` for interactivity

### Database Schema
The system uses a simple user table:
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(64) DEFAULT NULL,
  token_expires TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🚀 Future Enhancements

- [ ] Admin panel for user management
- [ ] Ticket system for support requests
- [ ] Real-time notifications
- [ ] File upload functionality
- [ ] Advanced reporting features
- [ ] API endpoints for mobile apps

## 📞 Support

For technical support or questions about this system, please contact:
- **Institution**: Chinhoyi University of Technology
- **Department**: ICT Department
- **Email**: ictsupport@cut.ac.zw
- **Phone**: +263 67 212 9451
- **Project**: Campus IT Support System

## 🚀 Deployment

### GitHub Pages Setup
1. Go to your repository settings
2. Navigate to "Pages" section
3. Select "GitHub Actions" as the source
4. The workflow will automatically deploy on every push to main/master

### Custom Domain (Optional)
1. Add your domain to the `CNAME` file in the repository root
2. Update the `cname` field in `.github/workflows/deploy.yml`
3. Configure DNS settings with your domain provider

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Built for Chinhoyi University of Technology with ❤️**
