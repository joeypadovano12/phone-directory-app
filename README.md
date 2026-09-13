# Phone Directory Web Application

A full-stack web application for searching, viewing, and saving mobile device details. The app pulls live device specifications from an external phone API, stores them in a MySQL database, and allows users to build personalized phone lists for their profiles.

## Features
* **User Accounts & Roles:** Secure user registration and login system with separate permissions and pages for standard users and admins.
* **Live API Search:** Connects to the GSMArena API to search for mobile devices and import specs directly into the app database.
* **Saved Favorites:** Logged-in users can save devices to their personal profile and manage their saved list.
* **Admin Controls:** Admin users can manually create, edit, or delete phone entries and manage user roles.

## Built With
* **Backend:** PHP, MySQL
* **Frontend:** HTML5, CSS3, JavaScript
* **API:** GSMArena REST API (via RapidAPI)

## Database Structure
* `Users` - Stores account details and encrypted passwords.
* `Roles` & `UserRoles` - Controls whether an account is a standard user or an admin.
* `project_phones` - Holds phone specifications (brand, model, specs).
* `user_phones` - Connects users to the specific phones they have saved.
