<p align="center" style="margin-bottom: 0px !important;">
  <img width="200" src="https://static.darflen.com/img/favicons/apple-touch-icon-precomposed.png" alt="Darflen logo" align="center">
</p>
<h1 align="center">Darflen</h1>
 
<p align="center" style="margin-bottom: 0px !important;">
Darflen is a simplistic yet fully-featured social media website made with PHP where you can share things by posting in a community.
</p>

<!---
## Table of Contents
* [Features](#Features)
* [Installation](#Installation)
  * [Requirements](#Requirements)
  * [Installation Steps](#Installation-Steps)
  * [Post-installation Steps](#Post-Installation-Steps)
* [Demo](#Accounts)
-->

## Features

<!---
* [Accounts](#Accounts)
  * [Authentication](#Authentication)
  * [Profiles](#Profiles)
    * [Profile Page](#Profile-Page)
    * [Settings Page](#Settings-Page)
 * [Admin Panel](#Admin-Panel)
 * [Explore Page](#Explore-Page)
 -->

### Accounts

#### Authentication

Darflen supports a complete ``user registration and authentication`` system.
They can register an account by putting a unique email and a display name (username) or logging in with their email and password. Each user's passwords are hashed before storing in a database so that even admins do not have access to the original passwords as well. The user birthdate input is only used to deter underage people, and it's not saved in the user data.

There is also a password recovery system in case the users forget their password. The site generates temporary encrypted token links which when used by the user prompt to change the password.

The site verifies the user prompts and checks for any errors such as ``Empty Fields``, ``Invalid Email``, ``Badly Formatted Inputs``, ``Server Errors`` and much more.

![Profile page](https://static.darflen.com/img/d.png)

#### Profiles

Darflen supports a complete ``profile system``. Each user has a profile that is assigned a profile after registering. They can post and react to other users' posts. The user's bio, as well as profile image and banner, are optional, meaning that anyone can register without setting those. In that case, the user will be assigned a default user image and the bio and will be set with a default text that says that they can edit that.

##### Profile Page
The user profile can easily be accessed by clicking their profile on the navbar, which is on the top right corner of the site page, clicking a user card, or clicking a posting user that is on the top of the post. The profile page shows many users' information, such as their statistics, their bio, their profile achievements, and their latest posts.  If in case the user has not done anything or is new, the page shows a text with a 'such empty' caption to remind you that you need to be more active! An user can follow another user profile to keep in check their new posts in the user feed.

##### Settings Page
The user can control their profile through the settings page by clicking the gear icon on the top right of the page.  The settings page allows the user to change most of their information, such as their username and email. The password can also be changed, however, only by providing the current password to retain a more secure interface. Each user can also add, or change their profile icon and banner, create and track their invites links, log out from individual devices, change the page themes and delete their account from the site. They can also check if their email is verified and do an email verification.

![Profile page](https://static.darflen.com/img/c.png)

### Admin Panel
Darflen has a simple but effective ``admin panel`` for administrators. Admins have access to the admin panel, where they can manage user information, edit website content, handle reports, and perform other moderation tasks. The admin panel also simplify access to each ``Users``, ``Posts``, ``Comments`` and ``replies`` content for moderation purposes making easier for administrators to moderate the site.

![Profile page](https://static.darflen.com/img/e.png)

### Explore Page
Darflen has an explore page. It is the public square of the website. The explore page lets the users explore the site and discover posts, users, and new content. The page shows posts in four categories:

- Recent posts: Shows the latest public posts shared.
- Popular posts: Shows the most popular posts on the site.
- Loved posts: Shows the most-hearted posts on the site.
- Trending posts: Shows posts that are getting traction.

 The page also shows popular hashtags used on posts, the amount of current online logged users, and six randomly picked users from the site.
 
 ![Profile page](https://static.darflen.com/img/a.png)

## Installation

This installation presumes that you installed everything required to make it work and that you use Nginx.

### Requirements

- PHP 8+
- MariaDB
- phpMyAdmin
- FFMpeg
- Redis

> These requirements are completable at once by installing [Laragon](https://laragon.org/), a server stack used to make this social media website.

### Installation Steps

1. Import ``database.sql`` file into phpMyAdmin. There is no need for any change in the .sql file. It will create the required database tables for the application to function correctly.
2. Insert the url rewrites from ``http_redirects.txt``  into your Nginx server config. It is required to make the proper URL work. Some additional pieces of information are in this file.

> If you do not use Nginx and use Apache, you can easily convert Nginx rewrites rules to .htaccess rewrites with [this website](https://www.winginx.com/en/htaccess).

3. Edit the ``configs.ini`` database section for database connection. Change the parameters to the ones used within your current installation of phpMyAdmin.
```ini
[database]
host = localhost
port = 3306
database = YOUR_DATABASE
username = YOUR_USERNAME
password = YOUR_PASSWORD
```
> Except for the database, username, and password, there is almost no need to change anything else under normal circumstances.

4. Edit the ``config.ini`` email section for mailing features. Change the parameters to the one used within your STMP service. It is required to make the mailing functions such as ``Email Verification``, ``Password Recovery`` and ``Welcome Email`` work.
```ini
[email]
host = smtp.zoho.com
auth = true
port = 587
security = tls
username = YOUR_EMAIL
password = YOUR_PASSWORD
```
> Currently, only STMP services are supported.

5. Edit the ``config.ini`` FFmpeg section for media file processing features. Change the parameters to the FFmpeg directory. It is necessary for media files such as ``Image``, ``Video``, and ``Audio`` files to be processed. Threads parameters should be the threads count your computer or server has.
```ini
[ffmpeg]
ffmpeg = YOUR_FFMPEG_DIRECTORY
ffprobe = YOUR_FFMPEG_PROPE_DIRECTORY
timeout = 36000
threads = 20
```
> You need to do all the required steps to make it work. If the requirements are not met, the site will not work. Everything should work if the required steps are met.

### Post-installation Steps

It is not possible to signup as an administrator through the application, since we decided that it was an exploitable weakness. Therefore, you need to follow these steps to create an account with administration features.
1. Create an account the typical way you create an account on a social media website.
2. Set the user ``administrator`` key from ``false`` to ``true`` in phpMyAdmin in the data column. It is required to have access to all the administrator features such as ``Banning``, ``Handling Reports`` and more.
```json
{
  "username": "YourName",
  "profile": {
    "description": "This is your default profile description. You can change it at any time.",
    "banner": "https://static.darflen.com/uploads/default-banner.png",
    "icon": "https://static.darflen.com/uploads/default-icon.png"
  },
  "miscellaneous": {
    "administrator": false,
    "email_verified": false,
    "user_verified": false,
    "creation_time": 1667780508
  }
}
```

## Demo
https://darflen.com/explore/
