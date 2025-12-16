# Rental Platform

A complete rental marketplace platform built with PHP, MySQL, HTML, CSS, and JavaScript. This platform supports both Docker and XAMPP environments, controlled by a single configuration variable.

## Features

- **User Authentication**: Registration, login, password recovery
- **Role-Based Access Control**: Admin and user roles with proper authorization
- **Listing Management**: Create, edit, delete rental listings
- **Image Upload**: Multiple image support for listings
- **Admin Approval System**: Listings require admin approval before going live
- **Search & Filter**: Advanced search with keyword, price range, location, and property type filters
- **Responsive Design**: Modern UI that works on all devices
- **Session-Based Security**: Secure authentication and authorization

## Project Structure

```
/config
  env.php              # Environment configuration (Docker/XAMPP switch)
  database.php         # Database connection logic

/docker
  docker-compose.yml   # Docker configuration

/public
  index.php           # Home page
  login.php           # Login page
  register.php        # Registration page
  forgot-password.php # Password recovery
  logout.php          # Logout handler
  listings.php        # Browse listings with filters
  listing-details.php # Individual listing view
  create-listing.php  # Create new listing
  edit-listing.php    # Edit existing listing
  profile.php         # User profile management

/admin
  dashboard.php       # Admin dashboard
  approve-listings.php # Review pending listings
  users.php          # User management

/includes
  auth.php           # Authentication functions
  middleware.php    # Access control middleware
  header.php        # Site header
  footer.php        # Site footer

/assets
  /css
    main.css        # Main stylesheet
  /js
    main.js         # JavaScript functionality
  /uploads
    /listings       # Uploaded listing images

/sql
  schema.sql        # Database schema
```

## Prerequisites

### For Docker:
- Docker Desktop installed
- Docker Compose installed

### For XAMPP:
- XAMPP installed (PHP 7.4+ and MySQL)
- Apache web server running
- MySQL service running

## Setup Instructions

### Option 1: Docker Setup (Recommended)

1. **Configure Environment**
   - Open `/config/env.php`
   - Ensure `USE_DOCKER` is set to `true`:
     ```php
     define('USE_DOCKER', true);
     ```

2. **Start Docker Containers**
   ```bash
   cd docker
   docker-compose up -d
   ```

3. **Access the Application**
   - Open your browser and navigate to: `http://localhost:8080`
   - The database will be automatically initialized with the schema

4. **Stop Docker Containers**
   ```bash
   cd docker
   docker-compose down
   ```

### Option 2: XAMPP Setup

1. **Configure Environment**
   - Open `/config/env.php`
   - Set `USE_DOCKER` to `false`:
     ```php
     define('USE_DOCKER', false);
     ```

2. **Copy Project Files**
   - Copy the entire project folder to `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
   - Or create a virtual host pointing to the project directory

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `rental_db`
   - Import the schema from `/sql/schema.sql`

4. **Configure Database** (if needed)
   - Edit `/config/database.php` if your MySQL credentials differ from defaults
   - Default XAMPP credentials:
     - Host: `localhost`
     - User: `root`
     - Password: `` (empty)

5. **Set Permissions**
   - Ensure the `/assets/uploads/listings` directory is writable
   - On Linux/Mac: `chmod -R 755 assets/uploads`

6. **Access the Application**
   - Navigate to: `http://localhost/479-rental/public/` (or your configured path)

## Default Admin Credentials

After setup, you can login with the default admin account:

- **Email**: `admin@rental.com`
- **Password**: `admin123`

**Important**: Change the admin password immediately after first login!

## Environment Switching

The platform uses a single configuration variable to switch between Docker and XAMPP:

**File**: `/config/env.php`

```php
// For Docker
define('USE_DOCKER', true);

// For XAMPP
define('USE_DOCKER', false);
```

No other code changes are required. The database connection automatically adapts based on this setting.

## Database Schema

The database includes three main tables:

1. **users**: User accounts with role-based access
2. **listings**: Rental property listings
3. **listing_images**: Images associated with listings

See `/sql/schema.sql` for the complete schema definition.

## User Roles

### Admin
- Access admin dashboard
- Approve/reject listings
- View all users
- View all listings (including pending)

### User
- Register and login
- Create rental listings
- Edit/delete own listings
- View approved listings
- Search and filter listings
- Manage profile

## Key Features

### Listing Workflow
1. User creates a listing (status: `pending`)
2. Admin reviews the listing
3. Admin approves or rejects
4. Approved listings become visible to all users

### Image Upload
- Multiple images per listing
- First image is set as primary
- Images stored in `/assets/uploads/listings/`
- File type validation (JPEG, PNG, GIF, WebP)
- Maximum file size: 5MB

### Search & Filter
- Keyword search (title, description, address)
- Price range filter
- Location filter
- Property type filter
- Filters persist on page reload

## Security Features

- Password hashing using PHP `password_hash()`
- Session-based authentication
- Input sanitization
- SQL injection prevention (PDO prepared statements)
- File type validation for uploads
- Role-based access control middleware
- XSS protection (htmlspecialchars)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Troubleshooting

### Docker Issues

**Port already in use:**
- Change ports in `docker-compose.yml` (8080:80 for PHP, 3307:3306 for MySQL)

**Database connection failed:**
- Ensure MySQL container is running: `docker ps`
- Check if `USE_DOCKER` is set to `true`

### XAMPP Issues

**Database connection failed:**
- Ensure MySQL service is running in XAMPP Control Panel
- Verify database credentials in `/config/database.php`
- Check if database `rental_db` exists

**Images not uploading:**
- Check directory permissions: `chmod -R 755 assets/uploads`
- Verify `upload_max_filesize` in `php.ini`

**Page not found errors:**
- Ensure you're accessing the correct path (e.g., `/public/index.php`)
- Check Apache rewrite module is enabled
- Verify `.htaccess` file exists (if using URL rewriting)

## Development Notes

- All code uses plain PHP (no frameworks)
- PDO for database operations
- Separation of concerns (includes, config, public, admin)
- Code is commented and beginner-friendly
- Follows PHP best practices

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check:
1. Configuration files (`/config/env.php`, `/config/database.php`)
2. Database connection settings
3. File permissions
4. Server logs

---

**Built with**: PHP, MySQL, HTML, CSS, JavaScript  
**Architecture**: Plain PHP (No Framework)  
**Database**: MySQL 8.0  
**Server**: Apache (Docker/XAMPP)

