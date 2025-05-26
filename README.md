# tbtKU-Suhrrid
Project Name: Suhrrid (সুহৃদ)  
Suhrrid (সুহৃদ) or tbtKU Suhrrid is a Student Service Platform for the students of Khulna University which will be a well-wisher or benefactor for searching rooms/messes and buying &amp; selling used items.


# Course Details
Course: 0714 02 CSE 2206  
Course Name: Database Systems Project/Fieldwork  
Course Teacher: Aminul Islam (Assistant Professor, CSE Discipline, KU)


# Proposed By:
Kazi Rifat Morshed (Student ID: 230220)  
Md Mahfuzur Rahman (Student ID: 230228)  
Md Rimon Islam (Student ID: 230236)  


# Video Demonstration
[Video Link](https://kazirifatmorshed.github.io/tbtku-suhrrid.html)

# Objectives:
Developing a platform for automating manual processes of:  
- advertising room/hostel/apartment rent  
- buying and selling old/used stuff   

# Project Type:
Non-profit, Social Service, Automation.

# Features
- The registration system requires student ID for the students of Khulna University and addresses for outsiders. 
- Email-based login system.
- Information on room/hostel/apartment, including how many rooms are available, how many people will be needed, on which floor, location, photo of building and room, photo of the washroom, and total rent cost (excluding bills and charges). electricity-gas-water-security-internet bills, room size, garage availability, balcony size, etc. 
- Removal of advertisement after the transaction of approval.
- Ads that are 15 days old will automatically become inactive and will not be shown in the feed but can be accessed by using the ad ID or from the user dashboard page (where all ads posted by an individual are listed).
- Dashboard for the maintainer/admin where he can inspect warned users, reported users, reported ads, and pending ads waiting for approval.
- Banned users have their profile highlighted in red.
- Moderators can see warning and ban buttons on user profiles (except their own).
- Banned users are shown a notice when attempting to log in. 
- Restriction to prevent admins/moderators from warning or blocking themselves.
- The admin can add and remove moderators.
- Users can create ads for room/mess rentals and selling used items. 
- Ad renewal feature: In the profile’s ad list, users can renew ads and see renewal status. 
- Favorites/marking system for ads. 
- Any user can comment on any post.
- Reporting system: Reported ads are highlighted (font color set to red).
- Announcements are fetched from a text file and displayed.
- Ad details include room count, student count, available from, agreement policy, bathroom details, roommate details, location link (map), religion preference, security, - furniture, entry time, owner’s name/contact, distance from KU, facing side, kitchen/fridge/drinking water/balcony/room size, nearby landmarks, etc. Utility bills and - facilities are detailed per ad (food, cctv, geyser, IPS, wifi, electricity, garbage, fridge, security, assistant, etc.).
- Prevent invalid user accounts from being accessed/manipulated via direct URL.
- Feedback/error handling in form submissions.
- Highlighting/restricting access as per user roles and statuses (banned, moderator, admin).
- Clear user feedback via UI highlights (red color for banned/reported).


# How to host ?

## **Set up your web server**
- If you're using WAMP in Windows machine:
	- Make sure WAMP is installed
	- Place the project files in the `www` directory. The location of `www` is `C:/wamp64/www/`

- If you're using XAMPP in Linux/Windows machine:
	- Make sure XAMPP is installed
	- Place the project files in the `htdocs` directory. The location of `www` is `C:/xampp/htdocs/` in windows, and, `/opt/lampp/htdocs/` in Linux. You may have to gain write access using `chmod` in linux.

## **Database Setup**

   You can set up the database using phpMyAdmin (GUI Method) or MySQL command line.

   1. Open phpMyAdmin in your browser:
      - If using WAMP: Click on the WAMP icon in the system tray → phpMyAdmin
      - Or visit: `http://localhost/phpmyadmin`
   
   2. Login to phpMyAdmin (default username is 'root' with no password for XAMPP/WAMP)
   
   3. Create a new database:
      - Click "New" in the left sidebar
      - Enter "tbtKU_Suhrrid_db" as the database name
      - Set `utf8_unicode_ci` for character-set collation
      - Click "Create"
   
   4. Import the database schema:
      - Select the "tbtKU_Suhrrid_db" database from the left sidebar
      - Click the "Import" tab at the top
      - Click "Choose File" and select `tbtKU-Suhrrid/Project Documents/Database Implementation/tbtKU_Suhrrid_db.sql`
      - Click "Import"


## Running the Application

1. Start your WAMP/XAMPP server
2. Open your web browser and navigate to:
   ```
    http://localhost/tbtKU-Suhrrid/tbtKU-Suhrrid/
   ```

