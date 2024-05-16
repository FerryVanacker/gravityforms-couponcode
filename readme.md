# Gravity Forms Couponcodes Generator

## Description
The **GP Couponcodes Generator** plugin for WordPress allows you to easily generate coupon codes within Gravity Forms. With an intuitive settings tab, you can quickly configure the plugin and link it to the desired form fields.

## Installation
1. **Download the plugin:** Download the plugin files
2. **Upload the plugin:** Go to your WordPress dashboard, navigate to "Plugins" > "Add New" and upload the zip file.
3. **Activate the plugin:** Activate the plugin from the WordPress dashboard.
4. **Create table:** Upon activation, a new database table is automatically created to store the coupon codes.

## Requirements
- WordPress 5.0 or higher
- Gravity Forms 1.9 or higher

## Features
- **Automatic coupon code generation:** Generates unique coupon codes upon form submission.
- **Link to form fields:** Choose the fields in your form where the coupon code and email address are stored.
- **Used code verification:** Checks if a coupon code has already been used before storing it.
- **Settings:** Configure the length of the coupon code, the discount amount, and the discount type (flat rate or percentage).

## Usage Instructions
1. **Configure plugin settings:**
   - Go to "Forms" > "Settings" > "Coupon Codes Settings".
   - Select the form you want to link to the coupon codes.
   - Specify the IDs of the fields where the coupon code and email address will be stored.
   - Set the length of the coupon code, the discount amount, and the discount type.

2. **Using the coupon codes:**
   - Upon each submission of the linked form, a unique coupon code is generated and stored in the database.
   - This coupon code can then be used within your own discount structure or shop.

## Hooks and Filters
- **`gform_field_value_uuid`:** Used to generate a unique coupon code.
- **`gform_after_submission`:** Action that takes place after form submission, where the coupon code is generated and stored.

## Database Table
Upon plugin activation, a new table is created:
- **Table Name:** `wp_coupon_codes`
- **Columns:**
  - `id` (mediumint, auto_increment)
  - `code` (varchar, unique key)
  - `email` (varchar)
  - `used` (tinyint, default 0)

## Support
For support, visit the [Conversie Partners support site](https://support.conversiepartners.nl/).

## Author
- **Name:** Conversie Partners
- **Developer:** Ferry Vanacker
- **Website:** [www.conversiepartners.nl](https://www.conversiepartners.nl/)
- **Version:** 2.0