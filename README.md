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
 5. [Support](#support)

## Introduction
AdSky is a way for you, as a Minecraft server owner, to monetize your server. Currently, there are two kinds of ads : [Title ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-0.png) and [Chat ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-1.png). Advertisers choose the type of ad they want to broadcast on your server, customize it (Title / subtitle, duration, display per day, ...), pay it according to the price you setup and voilà !

There are two parts : *Server* and *Plugin*.

### Server part
The server part is a PHP + MySQL application that you install on your own web server, it is where advertisers will register and broadcast their ads. To see how to install it and the requirements, please check the [*Installation*](#installation) section.

### Plugin part
The plugin part is a simple Bukkit plugin that is going to link up with the server part. To see how to install it and the requirements, please check the [*Installation*](https://github.com/Skyost/AdSky/tree/plugin#installation) section of the plugin branch's README.

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

## Contributing
It's easy to contribute to AdSky ! If you are a developer, first, you have to create a [fork](https://github.com/Skyost/AdSky/fork) and make your changes. Then make a pull request describing what are the improvements.

If you are not a developer, well, you can contribute as well ! Report bugs, problems and improvements in the [*Issues*](https://github.com/Skyost/AdSky/issues) section. You can also contribute by [making a donation](https://www.paypal.com/cgi-bin/webscr?hosted_button_id=XLEBVBMQNTXMY&item_name=AdSky&cmd=_s-xclick).

**Anyway, help is greatly appreciated under any form !**

## Support
If you want to report a bug / suggest an improvement / anything else, do not hesitate to [open an issue](https://github.com/Skyost/AdSky/issues/new).