# LEARNING MANAGEMENT SYSTEM FOR ViKl
### Table of Contents
- [**SETUP**](#setup)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
    - [1. Installing dependencies](#1-installing-dependencies)
    - [2. Configure the `.env` file](#2-configure-the-env-file)
    - [3. Setup database](#3-setup-database)
  - [Start the application](#start-the-application)
- [**DOCUMENTATION**](#documentation)
  - [System Architecture](#system-architecture)
  - [Backend of the learning platform](#backend-of-the-learning-platform)
    - [Service Provider and Router](#service-provider-and-router)
    - [Middleware](#middleware)
    - [Controller](#controller)
    - [Models](#models)
    - [Database](#database)
  - [Frontend of the learning platform](#frontend-of-the-learning-platform)
    - [Views](#views)
    - [JavaScript Elements](#javascript-elements)
    - [Styling](#styling)
- [**OPEN TODOs**](#open-todos)
  - [Views depending on the user roles](#views-depending-on-the-user-roles)
  - [Feedback](#feedback)
  - [Prevent interruption of chat session](#prevent-interruption-of-chat-session)
  - [Other ToDos](#other-todos)


# SETUP

## Prerequisites
| Dependency    | Version used for implementation      |
| --------------| ------------------------------------ |
| PHP           | 8.2.10                               |
| Composer      | 2.6.0                                |
| Node.js       | 20.5.1                               |
| NPM           | 10.1.0                               |
| MariaDB       | 11.1.2                               |

## Installation
### 1. Installing dependencies
1. Clone the repository and navigate to project folder
2. Install Composer dependencies: `composer install`
3. Install NPM packages: `npm install`
### 2. Configure the `.env` file:  
1. Copy the .env.example file to .env  
2. Configure the login information for the admin user in the .env file:
    ```
    APP_ADMIN=admin-vikl@th-nuernberg.de
    APP_ADMIN_PASSWORD=SuperSecretPassword123
    ```
3. Configure the database connection in the .env file:  
    ```
    DB_DATABASE=yourDBName    
    DB_USERNAME=yourDBUsername  
    DB_PASSWORD=yourDBPassword
    ```
4. Add an OpenAI-Key if you want to use ChatGPT: `OPENAI_KEY`
5. Generate the Laravel app key: `php artisan key:generate`
### 3. Setup database
First, make sure the database server is running.
1. Run the database migrations: `php artisan migrate`
2. Add some inital data using seeders: `php artisan db:seed`  
This adds the following data to the database:
    - Counselling Fields and Personae
    - List of possible User Roles
    - List of possible Statuses
    - Admin User
3. Optionally add test data to the database, run: `php artisan db:seed --class=TestDataSeeder`  
This adds the following data to the database:
    - Teacher: teacher-vikl@th-nuernberg.de, password same as admin-password from .env
    - Student: student-vikl@th-nuernberg.de, password same as admin-password from .env
    - One Course, where the created teacher and student are members

## Start the application
1. Start the development server: `php artisan serve`
2. Start the Vite development server: `npm run dev`

After that, the application should be accessible at http://localhost:8000

# DOCUMENTATION
## System Architecture
The architecture consists of two main parts: the learning platform (the project in this repository) and a chatbot (ViKl). These systems communicate via a REST interface. The separation of the Learning Platform and the ViKl, each with its own backend and database, ensures flexibility and independence. 
![System-Architecture](./documentation/Diagramme/Anwendungsarchitektur.png)


## Backend of the learning platform
The backend architecture consists of five main components: Service Provider, Middleware, Controller, Models, and the Database. Resource folders (inside the `app` directory) organize essential elements.
![Backend-Architecture](./documentation/Diagramme/Backend-Architektur.png)

### Service Provider and Router
- Service Providers configure routes for incoming requests directed to controllers

### Middleware
- Ensures actions are performed by authorized users
- Authentication is universal; Admin and EditingTeacher middlewares secure specific routes

### Controller
- Resource Controllers manage models based on user actions
- They provide CRUD operations (Create, Read, Udate, Delete)

### Models
- Controllers interact with models based on policies
- Policies controll user access to specific actions on models based on roles and defined criteria

### Database
- tables created through migrations; seeders insert initial data (see [Setup database](#3-setup-database))
- Database access exclusively through models

## Frontend of the learning platform
All files relevant to the frontend are located in the `resources` directory:
![Frontend](./documentation/Diagramme/Frontend.png)
### Views
- All Blade-created views
- `Layouts` folder contains all layouts for the platform
- Views are grouped based on usage
- `auth` folder contains Laravel-generated views for authentication process
### JavaScript Elements
- The `js` folder predominantly contains Vue.js components, categorized into subdirectories based on user roles
- Shared components among multiple user groups are stored in the `general` folder
- The `helpers` folder includes JavaScript files with frontend utility functions used by multiple components
### Styling
- Global styling definitions are stored in the `css` folder
- The `components` subfolder contains additional SCSS files defining global classes for commonly used elements
- `general.scss` defines commonly used classes
- `variables.scss`containds overrides of Bootstrap variables
#### Responsive Design
- Implemented for the entire application
- Implemented using Bootstrap layouts and breakpoints for responsiveness

# OPEN TODOs
## Views depending on the user roles

| Main Role           | Own Courses + Creation of New Courses   | Courses with Teacher Role              | Courses with Student Role               |
| --------------------| ----------------------------------------| ---------------------------------------| ----------------------------------------|
| **Admin**           | TODO: sees all courses with full rights | TODO: sees all courses with full rights| TODO: sees all courses with full rights |
| **EditingTeacher**  | done (pending: Course contents)         | TODO                                   | TODO                                    |
| **Teacher**         | not allowed                             | TODO                                   | TODO                                    |
| **Student**         | not allowed                             | not allowed                            | done                                    |

## Feedback
The inclusion of feedback mechanisms, such as trainer feedback, peer review, chatbot feedback and chatbot assessment, is currently being analysed in a separate research project. Based on the results, these points need to be integrated.

## Prevent interruption of chat session
In the current system, users can resume a chat at any point. This sould not be possible.  
Proposed solution: When opening an exercise or task where the chat status is still "in progress", the system should check the creation date of the counseling. If it has been more than x hours:
- A notification will be displayed, alerting the user that the chat has expired.
- Users will be prompted to choose between marking the exercise or task as "Done" or deleting it.
- Additionally, for tasks with a due date that has passed:
  - A notification will inform the user that the task is no longer editable.
  - Users can acknowledge this message with an "OK" button, and the chat remains in progress.
 
## Other ToDos
- Technical difficulties
- Optimisation of the user experience and the usability: functions such as sorting and filtering of table contents, according to the mockups
- Templates for the trainers (for courses and counselling setups)
- Technical support centre