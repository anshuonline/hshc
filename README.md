# Dewachen Retreat Gangtok - Retreat Website

A complete retreat website with admin management features built using PHP and Tailwind CSS.

## Features

### Frontend
- Fully responsive design using Tailwind CSS
- Mobile-friendly navigation with hamburger menu
- Home page with hero section and animations
- Single retreat page with detailed information
- About page with retreat story and facilities
- Contact page with form
- Gallery with image hover effects
- Smooth scrolling and transitions

### Admin Panel
- Secure login system
- Dashboard with statistics
- Retreat information management
- Image management (upload and display)
- Admin user management

## Installation

1. Clone or download this repository to your web server directory
2. Create a MySQL database named `dewachen_retreat`
3. Update the database configuration in `config/db.php` if needed
4. Run the initialization script by visiting `http://your-domain/init.php`
5. Access the website at `http://your-domain/`
6. Access the admin panel at `http://your-domain/admin/login.php`

## Admin Login

- Username: `admin`
- Password: `admin123`

## File Structure

```
├── config/
│   └── db.php              # Database configuration
├── includes/
│   ├── header.php           # Site header with responsive navigation
│   └── footer.php           # Site footer
├── admin/
│   ├── login.php            # Admin login page
│   ├── logout.php           # Admin logout
│   ├── dashboard.php        # Admin dashboard
│   ├── hotels.php           # Retreat information management
│   ├── images.php           # Image management
│   └── admins.php           # Admin user management
├── uploads/                 # Uploaded images (created automatically)
├── index.php                # Home page
├── hotels.php               # Retreat details page
├── hotel-details.php        # Retreat details (alternative)
├── about.php                # About page
├── contact.php              # Contact page
├── init.php                 # Database initialization
└── README.md                # This file
```

## Customization

1. Update the database connection details in `config/db.php`
2. Modify the site content in the respective PHP files
3. Replace placeholder images with actual retreat images
4. Customize the Tailwind CSS styling as needed

## Security Notes

- Change the default admin password after first login
- Ensure the `uploads/` directory has proper permissions
- Use HTTPS in production environments
- Regularly update dependencies and PHP version

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, etc.)

## License

This project is open source and available under the MIT License.