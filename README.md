# Sports Event Calendar

This is a PHP-based web app designed to display and manage sports events. The app allows the user, to add, view, update, and delete sports events, and displays them in a weekly format, that can be filtered by sport.

## Overview

This project allows administrators to add, update, and delete sports events through a dedicated admin interface. The application is developed using vanilla PHP, ensuring ease of use and minimal dependencies.

Key features:
- Event management system (CRUD: Create, Read, Update, Delete)
- Admin panel for event management
- Simple and clean interface for event display
- No frameworks, just plain PHP

---

## Project Structure
sports_event_calendar/ 
  ├── .git/ # Git version control files 
  ├── calendar/ │ 
    ├── components/ # PHP components used across the app │ 
    ├── public/ # Public-facing interface for users to view events

### Key Directories
- **components/**: Reusable PHP components for shared functionality, like database connection or event formatting.
- **public/**: Contains the public interface and assets, such as the event display, details, create, and update pages.

---

## Installation

1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd sports_event_calendar

2. **Set Up the Environment**:

  - Ensure PHP 7.4+ is installed on your machine.
  - Configure your web server (Apache, Nginx, etc.) to point to the calendar/public folder as       the document root.

3. **Database Setup**:

  - Set up a MySQL database.
  - Import "sports_event_calendar_sportradar.sql" file to your MySQL Database to create the   	    necessary tables.
  - Double-check that the database credentials in the db_connect.php file fit with your MySQL       database.

4. **Running the Application**:
  - Open your browser and navigate to http://localhost:3000/calendar/public/index.php to view       the application.

---

## Assumptions & Decisions
- No Frameworks: This project is built using plain PHP with no frameworks to keep it lightweight and easy to deploy on any server with PHP support.
- Database: A MySQL database is used to store event data.
- No Frontend Libraries: The frontend is built with minimal HTML/CSS. JavaScript is not heavily used, keeping the application simple and focused on core functionality.
- Security: Basic security measures (e.g., form validation, input sanitization) are implemented, but additional steps (like CSRF protection) should be considered for production use.
- Process: Started with a convoluted solution, that was not getting the desired results. Had to minimize the logic and feature so it does not consume time, especially while having family duress. That proved to be way better and neater to do than the original solution.
- View: The database has 20 events. The events are dated 3 months into the future. When scrolling through the weeks, you might see that some weeks are empty, that is due to the long Christmas break that the leagues are taking :P. Just make sure to keep skipping to the following weeks, there are some awesome games happening in the new year!







