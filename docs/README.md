# FreelanceHub - Freelance Marketplace Platform

A complete freelance marketplace platform where freelancers can upload gigs, clients can place orders, and admins can manage the entire platform.

## 🚀 Project Overview

**Name**: FreelanceHub  
**Goal**: Create a comprehensive freelance marketplace connecting talented freelancers with clients  
**Tech Stack**: PHP, MySQL, HTML, CSS, JavaScript, Bootstrap  
**Version**: 1.0.0  
**Status**: ✅ Active Development

## ✨ Main Features

### 1. **User Roles**
- **Admin**: Platform management, user approval, account suspension
- **Freelancer**: Profile management, gig creation, order handling
- **Client**: Browse gigs, place orders, review freelancers

### 2. **Authentication System** ✅
- User registration (Freelancer/Client)
- Secure login with password hashing
- Session-based authentication
- Account approval workflow

### 3. **Freelancer Features** 🔄
- Complete profile management
- Upload gigs with details
- Manage orders
- Portfolio showcase
- Rating and review system

### 4. **Client Features** 🔄
- Browse gigs by category
- View freelancer profiles
- Place orders
- Order history
- Review freelancers

### 5. **Admin Panel** ✅
- Dashboard with statistics
- User management (approve/reject/suspend)
- Gig management
- Order monitoring
- Category management

### 6. **Design Features** ✅
- Modern, clean interface
- Dark mode toggle
- Fully responsive
- Bootstrap-based UI
- Professional color scheme

## 📊 Database Architecture

### Main Tables:
1. **users** - User accounts (admin, freelancer, client)
2. **freelancer_profiles** - Freelancer details, bio, skills, rating
3. **gigs** - Service listings by freelancers
4. **orders** - Client orders and their status
5. **bids** - Client bids on gigs (optional)
6. **reviews** - Freelancer ratings and reviews
7. **messages** - Basic messaging between users
8. **categories** - Service categories

### Data Flow:
1. User registers → Admin approves → User becomes active
2. Freelancer creates gig → Client views → Client orders → Freelancer accepts/rejects
3. Order completed → Client reviews → Freelancer rating updated

## 🎨 UI/UX Features

- **Theme**: Professional neutral theme with dark mode
- **Colors**: Primary (Indigo), Secondary (Purple), Success, Warning, Danger
- **Typography**: Inter font family
- **Components**: Cards, buttons, forms, tables, badges, alerts
- **Responsive**: Mobile-first design
- **Icons**: Font Awesome 6.4.0

## 📁 Project Structure

```
webapp/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Admin dashboard
│   ├── users.php          # User management
│   ├── gigs.php           # Gig management
│   ├── orders.php         # Order management
│   └── user-action.php    # User action handler
├── freelancer/            # Freelancer dashboard files
│   ├── dashboard.php      # Freelancer dashboard
│   ├── profile.php        # Profile management
│   ├── gigs.php           # Manage gigs
│   └── orders.php         # View orders
├── client/                # Client dashboard files
│   ├── dashboard.php      # Client dashboard
│   └── orders.php         # Order history
├── includes/              # Shared includes
│   ├── header.php         # Header with navbar
│   ├── footer.php         # Footer
│   └── functions.php      # Helper functions
├── config/                # Configuration files
│   ├── database.php       # Database connection
│   └── database.example.php
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # Main JavaScript
│   └── images/
├── uploads/               # User uploads
│   ├── profiles/          # Profile pictures
│   ├── gigs/              # Gig images
│   └── portfolio/         # Portfolio images
├── database.sql           # Database schema
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── logout.php             # Logout handler
├── browse-gigs.php        # Browse all gigs
├── gig-details.php        # Gig details page
└── README.md              # This file
```

## 🔧 Installation Instructions

### Prerequisites:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional)

### Steps:

1. **Clone or Download Project**
   ```bash
   # Download the project to your web server directory
   # For XAMPP: C:/xampp/htdocs/freelancehub/
   # For WAMP: C:/wamp64/www/freelancehub/
   ```

2. **Create Database**
   ```bash
   # Open phpMyAdmin
   # Import database.sql file
   # Or run SQL commands from database.sql
   ```

3. **Configure Database Connection**
   ```bash
   # Copy config/database.example.php to config/database.php
   # Update database credentials:
   ```
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'freelance_marketplace');
   ```

4. **Set Permissions**
   ```bash
   # Make uploads directory writable
   chmod -R 777 uploads/
   ```

5. **Access Application**
   ```
   http://localhost/freelancehub/
   ```

### Default Admin Credentials:
- **Username**: admin
- **Password**: admin123

## 📝 Currently Completed Features

### ✅ Phase 1 (Completed)
- [x] Project structure setup
- [x] Database schema with 8 tables
- [x] User authentication system
- [x] Registration with role selection
- [x] Login with session management
- [x] Password hashing and security
- [x] Admin dashboard with statistics
- [x] User approval/rejection system
- [x] Account suspension (7 days/permanent)
- [x] Dark mode toggle
- [x] Responsive design
- [x] Flash message system

### 🔄 Phase 2 (In Progress)
- [ ] Freelancer profile management (pending)
- [ ] Gig upload and edit (pending)
- [ ] Portfolio management (pending)
- [ ] Profile completeness calculation (pending)

### ⏳ Phase 3 (Pending)
- [ ] Browse gigs with filters
- [ ] Category-based search
- [ ] Gig details page
- [ ] Order placement system
- [ ] Order status updates

### ⏳ Phase 4 (Pending)
- [ ] Freelancer ranking algorithm
- [ ] Rating and review system
- [ ] Messaging system
- [ ] Order management
- [ ] Admin gig management

### ⏳ Phase 5 (Pending)
- [ ] Advanced search and filters
- [ ] Email notifications
- [ ] Payment integration (optional)
- [ ] Final testing and bug fixes

## 🎯 Functional Entry URIs

### Public Pages
- `GET /` - Homepage
- `GET /login.php` - Login page
- `POST /login.php` - Login action
- `GET /register.php` - Registration page
- `POST /register.php` - Registration action
- `GET /logout.php` - Logout
- `GET /browse-gigs.php` - Browse all gigs
- `GET /gig-details.php?id={id}` - View gig details
- `GET /freelancer-profile.php?id={id}` - View freelancer profile

### Admin Panel (Requires: admin role)
- `GET /admin/dashboard.php` - Admin dashboard
- `GET /admin/users.php` - Manage users
- `GET /admin/user-action.php?action={action}&id={id}` - User actions
  - Actions: approve, reject, suspend_7days, suspend_permanent, activate
- `GET /admin/gigs.php` - Manage gigs (pending)
- `GET /admin/orders.php` - Manage orders (pending)
- `GET /admin/categories.php` - Manage categories (pending)

### Freelancer Panel (Requires: freelancer role)
- `GET /freelancer/dashboard.php` - Freelancer dashboard (pending)
- `GET /freelancer/profile.php` - Edit profile (pending)
- `GET /freelancer/gigs.php` - Manage gigs (pending)
- `GET /freelancer/orders.php` - View orders (pending)

### Client Panel (Requires: client role)
- `GET /client/dashboard.php` - Client dashboard (pending)
- `GET /client/orders.php` - Order history (pending)

## 🔒 Security Features

- Password hashing with bcrypt
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- CSRF token implementation (ready)
- Session security
- File upload validation
- Role-based access control

## 📱 Responsive Breakpoints

- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

## 🎨 Color Scheme

```css
--primary-color: #6366f1 (Indigo)
--secondary-color: #8b5cf6 (Purple)
--success-color: #10b981 (Green)
--warning-color: #f59e0b (Orange)
--danger-color: #ef4444 (Red)
--info-color: #3b82f6 (Blue)
```

## 🚀 Recommended Next Steps

1. **Complete Freelancer Module**
   - Profile edit functionality
   - Gig upload with image
   - Portfolio management
   - Profile completeness calculator

2. **Build Client Module**
   - Browse gigs with filters
   - Order placement
   - Order tracking

3. **Implement Order System**
   - Order workflow
   - Status updates
   - Notifications

4. **Add Rating System**
   - Review submission
   - Rating calculation
   - Display on profiles

5. **Enhance Search**
   - Advanced filters
   - Sorting options
   - Ranking algorithm

## 📞 Support & Contact

For issues, questions, or contributions:
- Email: support@freelancehub.com
- Documentation: [Coming Soon]

## 📄 License

This project is developed as a custom freelance marketplace platform.

---

**Last Updated**: January 24, 2026  
**Version**: 1.0.0 (Development)  
**Developer**: Your Development Team
