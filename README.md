<div align="center">

![AdSky](https://i.imgur.com/iXDw1o9.png)

</div>

----------

![License](https://img.shields.io/github/license/Skyost/AdSky.svg?style=flat-square)
![Tag](https://img.shields.io/github/tag/Skyost/AdSky.svg?style=flat-square)

## Table of contents
 1. [Introduction](#introduction)
 2. [Features](#features)
 3. [Installation](#installation)
 4. [Contributing](#contributing)
 5. [API](#api)
 6. [Support](#support)

## Introduction
AdSky is a way for you, as a Minecraft server owner, to monetize your server. Currently, there are two kinds of ads : [Title ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-0.png) and [Chat ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-1.png). Advertisers choose the type of ad they want to broadcast on your server, customize it (Title / subtitle, duration, display per day, ...), pay it according to the price you setup and voilà !

There are two parts : *Server* and *Plugin*.

### Server part
The server part is a PHP + MySQL application that you install on your own web server, it is where advertisers will register and broadcast their ads. To see how to install it and the requirements, please check the [*Installation*](#installation) section.

### Plugin part
The plugin part is a simple Bukkit / Sponge plugin that is going to link up with the server part. To see how to install it and the requirements, please check the [*Installation*](https://github.com/Skyost/AdSky/tree/plugin#installation) section of the plugin branch's README.

## Features
AdSky is built to be lightweight, but it still has a lot of features :

 - Free and open-source.
 - Can be run on *almost* any server that as PHP + MySQL installed.
 - Everything is configurable.
 - Lightweight.
 - Android application. <sup>Coming soon !</sup>

## Installation
To install the plugin part, please check the [*Installation*](https://github.com/Skyost/AdSky/tree/plugin#installation) section of the plugin branch's README.

To install the server part, you have to download [this archive](https://github.com/Skyost/AdSky/archive/server.zip) and unzip it on your web server. Once uncompressed, go to `http://yourwebsite.com/adsky/install/` and follow the steps.

Please make sure that your web server meets the following requirements :

 - **PHP 5.6.0+** with the [following extensions](https://scripts.mit.edu/faq/64/how-do-i-enable-additional-php-extensions) : PDO (`pdo`), MySQL Native Driver (`mysqlnd`) and OpenSSL (`openssl`).
 - **MySQL 5.5.3+**.
 - **[mail()](http://php.net/manual/function.mail.php) and [URL Rewriting](https://gist.github.com/bramus/5332525) support**.
 
If you want to edit messages and the global look of AdSky, do not hesitate to edit `.twig` and `.css` files (located in `views/` and `assets/css/`, respectively).
 
Oh and I forgot one thing : **everything is configurable**. Just go to `core/settings/` and open the PHP file you want.

## API

AdSky server is provided with a little API. Below are the available methods.
Each of these will return you a JSON response containing : an `error`, an `message` and an `object` (all values can be null).

### Ads

Here are the available ads operations :

#### List all ads

**POST /api/v1/ads** : Allows you to list all ads on the server.

* _"page" (int)_ : The page you want to see (optional).

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "data":[
         {
            "id":16,
            "username":"Skyost",
            "type":1,
            "title":"&lChat Ad",
            "message":"&4Hi, this is a Chat Ad.",
            "interval":2,
            "expiration":1528156800,
            "duration":-1
         },
         {
            "id":12,
            "username":"SimpleUser",
            "type":0,
            "title":"Wonderful Ad",
            "message":"This is wonderful.",
            "interval":4,
            "expiration":1528761600,
            "duration":6
         }
      ],
      "page":1,
      "minPage":1,
      "maxPage":1,
      "hasPrevious":false,
      "hasNext":false
   }
}
```

#### Get an ad

**\* /api/v1/ads/:id** : Allows you to get an ad by its ID.
You must be an admin to execute this on someone else's ad.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "title":"Wonderful Ad",
      "message":"This is wonderful.",
      "username":"SimpleUser",
      "interval":4,
      "expiration":1528761600,
      "type":0,
      "duration":6
   }
}
```

#### Delete an ad

**\* /api/v1/ads/:id/delete** : Allows you to delete an ad. You must either be an admin or the owner of the specified ad.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

#### Pay for an ad

**POST /api/v1/ads/:id/pay** : Send a pay request to the PayPal REST API. An admin will see its ad immediately registered (if valid).

* _"title" (string)_ : Ad's title.
* _"type" (int)_ : Ad's type (0 for Title Ad, 1 for Chat Ad).
* _"message" (string)_ : Ad's message (optional).
* _"interval" (int)_ : Times to display ad per day.
* _"expiration" (long)_ : Expiration date (in timestamp).
* _"duration" (int)_ : Time to stay on screen (for Title ads), optional if you want a Chat ad.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":"https:\/\/www.sandbox.paypal.com\/cgi-bin\/webscr?cmd=_express-checkout&token=XX-XXXXXXXXXXXXXXXXX"
}
```

#### Renew an ad

**POST /api/v1/ads/:id/renew** : Sends a renew request to the PayPal REST API.  An admin will see its ad immediately renewed (if valid).

* _"days" (int)_ : Number of days you want to add to the current expiration date.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":"https:\/\/www.sandbox.paypal.com\/cgi-bin\/webscr?cmd=_express-checkout&token=XX-XXXXXXXXXXXXXXXXX"
}
```

#### Updates an ad

**POST /api/v1/ads/:id/update** : Allows you to update an ad (for administrators only).

* _"title" (string)_ : New ad's title (optional).
* _"type" (int)_ : New ad's type (0 for Title Ad, 1 for Chat Ad), optional.
* _"message" (string)_ : New ad's message (optional).
* _"interval" (int)_ : New times to display ad per day (optional).
* _"expiration" (long)_ : New expiration date (in timestamp, optional).
* _"duration" (int)_ : New time to stay on screen (for Title ads), optional.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

### Plugin

Here are the available plugin operations. These are special, because you do not have to be authenticated or anything,
but for each request you have to send the plugin key (see `core/settings/PluginSettings.php`) :

#### Delete expired ads

**POST /api/v1/plugin/delete-expired** : Allows you to delete expired ads.

* _"key" (string)_ : Plugin key.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

#### Get today ads

**POST /api/v1/plugin/today** : Allows you to get today ads.

* _"key" (string)_ : Plugin key.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

### Update

Here are the available AdSky update operations :

#### Check for updates

**\* /api/v1/update/check** : Allows you to check for AdSky updates.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "version":"v0.1",
      "download":"https:\/\/github.com\/Skyost\/AdSky\/releases\/download\/v0.1\/adsky-server.zip"
   }
}
```

#### Update

**\* /api/v1/update/update** : Allows you to update AdSky.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

### Users

Here are the available users operations :

#### List all users

**\* /api/v1/users** : Allows you to list all users (for administrators only).

* _"page" (int)_ : The page you want to see (optional).

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "data":[
         {
            "username":"Skyost",
            "email":"my@mail.com",
            "type":0,
            "verified":"1",
            "last_login":1526144440,
            "registered":1526136318
         },
         {
            "username":"SimpleUser",
            "email":"another@mail.com",
            "type":1,
            "verified":"1",
            "last_login":1526144710,
            "registered":1524926401
         }
      ],
      "page":1,
      "minPage":1,
      "maxPage":1,
      "hasPrevious":false,
      "hasNext":false
   }
}
```

#### Get an user

**\* /api/v1/users/:email** : Allows you to get an user. Put `current` in _email_ to target current user.
You must be an admin to get another user.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "username":"Skyost",
      "email":"my@mail.com",
      "type":0
   }
}
```

#### List an user's ads

**\* /api/v1/users/:email/ads** : Lists all user's ads. Put `current` in _email_ to target current user.
You must be an admin to list another user's ads.

* _"page" (int)_ : The page you want to see (optional).

```json
{
   "error":null,
   "message":"Success.",
   "object":{
      "data":[
         {
            "id":16,
            "username":"Skyost",
            "type":1,
            "title":"&lChat Ad",
            "message":"&4Hi, this is a Chat Ad.",
            "interval":2,
            "expiration":1528156800,
            "duration":-1
         }
      ],
      "page":1,
      "minPage":1,
      "maxPage":1,
      "hasPrevious":false,
      "hasNext":false
   }
}
```

#### Delete an user

**\* /api/v1/users/:email/delete** : Allows you to delete someone's account. Put `current` in _email_ to target current user.
You must be an admin to delete someone else's account.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

#### Update an user

**POST /api/v1/users/:email/update** : Updates someone's account. Put `current` in _email_ to target current user.
You must be an admin to update someone else's account.

* _"email" (string)_ : The new email (optional).
* _"password" (string)_ : The new password (optional).
* _"force" (boolean)_ : Allows to not enter "oldpassword" parameter, to edit the type and to not confirm the new email (for admins only, optional).
* _"type" (int)_ : The new type (0 for admin, 1 for publisher), optional.
* _"oldpassword" (string)_ : Confirmation password.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

#### Register an user

**POST /api/v1/users/register** : Registers an account.

* _"username" (string)_ : The username.
* _"email" (string)_ : The email.
* _"password" (string)_ : The password.

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

#### Login an user

**POST /api/v1/users/login** : Allows you to login an user.

* _"email" (string)_ : The email.
* _"password" (string)_ : The password.
* _"rememberduration" (long)_ : Remember duration in seconds (optional).

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

Above is the JSON output. A cookie named `PHPSESSID` will also be returned and you must send it back at each request you want to send with a logged-in user.

If you have sent a _rememberduration_ parameter, a cookie named `remember_x[...]xx` will be returned. You must also send it with the other one to keep the user logged-in.

#### Logout the current user

**\* /api/v1/users/logout** : Allows you to logout the current user.

_No additional parameters needed._

Sample output :

```json
{
   "error":null,
   "message":"Success.",
   "object":null
}
```

## Contributing
It's easy to contribute to AdSky ! If you are a developer, first, you have to create a [fork](https://github.com/Skyost/AdSky/fork) and make your changes. Then make a pull request describing what are the improvements.

If you are not a developer, well, you can contribute as well ! Report bugs, problems and improvements in the [*Issues*](https://github.com/Skyost/AdSky/issues) section. You can also contribute by [making a donation](https://www.paypal.com/cgi-bin/webscr?hosted_button_id=XLEBVBMQNTXMY&item_name=AdSky&cmd=_s-xclick).

**Anyway, help is greatly appreciated under any form !**

## Support
If you want to report a bug / suggest an improvement / anything else, do not hesitate to [open an issue](https://github.com/Skyost/AdSky/issues/new).
