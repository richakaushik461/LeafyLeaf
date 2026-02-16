# LeafyLeaf
LeafyLife - Online Plant Shop
 
LeafyLife is a college project — an online plant shop built using HTML, CSS, JavaScript, PHP, and Razorpay Test API. It runs locally on XAMPP.
 
Features
- Browse plant products with images and descriptions 
- Add to cart and checkout 
- Razorpay test payment integration 
- Order confirmation page 
- User registration/login and profile 
- Blog and inquiries system 
- Admin panel to manage products and orders 
 
Tech Stack
- Frontend: HTML, CSS, JavaScript 
- Backend: PHP 
- Database: MySQL (via phpMyAdmin) 
- Payments: Razorpay Test API 
- Local Server: XAMPP 
 
Setup Instructions
 
1. Install XAMPP
 Download from: https://www.apachefriends.org/
 
2. Setup Project Folder
 Place the entire `leafylife` folder inside the `htdocs` directory: 
 `C:/xampp/htdocs/leafylife`
 
3. Database Setup
 - Open phpMyAdmin 
 - Create a database named `leafylife` 
 - Import `leafylife.sql` file
 
4. Configure Razorpay
 - Create a Razorpay account in test mode 
 - Add your test API keys in `checkout.php`
 
5. Run the Website
 - Start Apache and MySQL from the XAMPP control panel 
 - Open your browser and go to: 
 `http://localhost/leafylife`
 
Folder Structure
leafylife/
├── images/
├── about.php
├── add_blog.php
├── add_product.php
├── address.php
├── admin.php
├── admin_nav.php
├── admin_panel.php
├── app.js
├── auto-register-sw.js
├── blog.php
├── blogs.php
├── blogs_mgmt.php
├── cancel_order.php
├── cancel_request.php
├── cart.php
├── change_password.php
├── checkout.php
├── contact.php
├── counter_functions.php
├── db_connect.php
├── desktop.ini
├── edit_blog.php
├── edit_product.php
├── edit_user.php
├── index.php
├── inquiries.php
├── loginreg.php
├── logout.php
├── logout_process.php
├── order_success.php
├── orders.php
├── product.php
├── products_mgmt.php
├── profile.php
├── shop.php
├── ss.js
├── style.css
├── style1.css
├── style2.css
├── styles.css
├── submit_review.php
├── sw.js
├── track_order.php
├── try.php
├── user details.txt
├── users.php
├── view_inquiry.php
├── wishlist.php
 
 
Notes
- Use Razorpay test keys only — not suitable for live payments 
- Always sanitize user inputs in real-world deployments
- Unzip images folder and place as "images" folder in "leafylife" folder
 
Contact
Email: richakaushik461@gmail.com
