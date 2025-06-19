# nux-frontend-login

## Description
The **nux-frontend-login** plugin provides a customizable front-end login system for WordPress. It includes features such as custom login forms, password reset functionality, and email templates for user interactions.

## Features
- Customizable login form.
- Password reset functionality.
- Email templates for password reset and confirmation.
- Modular structure for easy maintenance and extension.

## File Structure
```
nux-front-end-login/
    email-filters.php
    nux-front-end-login.php
    template-functions.php
    assets/
        js/
            custom-login.js
    classes/
        class-custom-login.php
        class-members.php
    templates/
        email-password-reset.php
        email-reset-confirm.php
        footer-default.php
        header-default.php
        login-form.php
```

## Installation
1. Clone or download the repository into your WordPress `wp-content/plugins` directory.
2. Activate the plugin through the WordPress admin dashboard.

## Usage
- Add the shortcode `[nux_login_form]` to any page or post to display the custom login form.
- Optionally use the `nux_login_form()` function in your theme template files.
- Customize the email templates located in the `templates/` directory as needed.

## Development
### Prerequisites
- WordPress installation.
- PHP 7.4 or higher.
- Basic knowledge of WordPress plugin development.

### Customization
- **Email Filters**: Modify email behavior in `email-filters.php`.
- **Template Functions**: Add or override template-related functions in `template-functions.php`.
- **Classes**: Extend or modify functionality in `classes/class-custom-login.php` and `classes/class-members.php`.

## Support
For issues or feature requests, please contact the development team or open an issue in the repository.

## License
This plugin is licensed under the [MIT License](https://opensource.org/licenses/MIT).
