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
 4. [Configuration](#configuration)
 5. [Contributing](#contributing)
 6. [Support](#support)

## Introduction
AdSky is a way for you, as a Minecraft server owner, to monetize your server. Currently, there are two kinds of ads : [Title ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-0.png) and [Chat ads](https://github.com/Skyost/AdSky/blob/server/assets/img/previews/preview-1.png). Advertisers choose the type of ad they want to broadcast on your server, customize it (Title / subtitle, duration, display per day, ...), pay it according to the price you setup and voilà !

There are two parts : *Server* and *Plugin*.

### Server part
The server part is a PHP + MySQL application that you install on your own web server, it is where advertisers will register and broadcast their ads. To see how to install it and the requirements, please check the [*Installation*](https://github.com/Skyost/AdSky/tree/server#installation) section of the server branch's README.

### Plugin part
The plugin part is a simple Bukkit / Sponge plugin that is going to link up with the server part. To see how to install it and the requirements, please check the [*Installation*](#installation) section.

## Features
AdSky is built to be lightweight, but it still has a lot of features :

 - Free and open-source.
 - Can be run on *almost* any server that as PHP + MySQL installed.
 - Everything is configurable.
 - Lightweight.
 - Android application. <sub>Coming soon !</sub>

## Installation

To install the server part, please check the [*Installation*](https://github.com/Skyost/AdSky#installation) section of the server branch's README.

### CraftBukkit / Spigot

To install the Bukkit plugin, you have to download [the JAR](https://dev.bukkit.org/projects/adsky/files) and put it inside the `plugins` folder located in your server directory.

You need either Bukkit or Spigot. Any version above _1.8_ should work.

### Sponge

To install the Sponge plugin, you have to download [the JAR](https://ore.spongepowered.org/Skyost/AdSky/versions/recommended/download) and put it inside the `mods` folder located in your server directory.

Any version Sponge version above _7.0.0_ (included) should work.

## Configuration
The configuration file (`config.yml`) is located in `plugins/AdSky/`. Open it and configure it as you want :

| Depth 0  | Depth 1                 | Description                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| -------- | ----------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `enable` | `updater`               | Whether the updater should be enabled ([Skyupdater](https://www.skyost.eu/skyupdater.txt) for Bukkit, OreUpdater for Sponge).                                                                                                                                                                                                                                                                                                                        |
|          | `metrics`               | **Bukkit only :** Whether [bStats Metrics](https://bstats.org/) should be enabled.                                                                                                                                                                                                                                                                                                                                                                   |
| `server` | `url`                   | AdSky's root URL on your server.                                                                                                                                                                                                                                                                                                                                                                                                                     |
|          | `plugin-key`            | The key given at the end of the installation. If you don't remember me, it is available in `api/settings/Plugin.php`.                                                                                                                                                                                                                                                                                                                                |
|          | `event-scheduler`       | Whether you have scheduled the MySQL event (downloadable [here](https://github.com/Skyost/AdSky/blob/server/install/sql/clearExpiredAds.sql)). If you did not, then the plugin is going to clear expired ads each day at midnight.                                                                                                                                                                                                                   |
| `ads`    | `preferred-hour`        | The preferred hour to broadcast ads to players. 24 hours format. For example, if you want 3PM, put 15.                                                                                                                                                                                                                                                                                                                                               |
|          | `worlds-blacklist`      | Ads are not going to be broadcasted in these worlds.                                                                                                                                                                                                                                                                                                                                                                                                 |
|          | `min-hour`              | Ads are not going to be broadcasted before this hour.                                                                                                                                                                                                                                                                                                                                                                                                |
|          | `max-hour`              | Ads are not going to be broadcasted after this hour.                                                                                                                                                                                                                                                                                                                                                                                                 |
|          | `distribution-function` | Here's how it works. The `h` will be replaced by the preferred hour, the `n` by the number of ads of the day and `x` by the current hour. For instance, to see how many ads will be displayed at 3PM if there are 20 ads to display and if the preferred hour is 12 : `((-1/20) * (15-12)^2) + log(20) = 0.85`. With a rounding mode of [UP](https://docs.oracle.com/javase/7/docs/api/java/math/RoundingMode.html), there will be 1 ad broadcasted. |

**Important :** If you cannot see ads, it means you have the permission `adsky.bypass` (which is granted to ops by default). If you want to see ads, please turn off the permission for yourself.

## Contributing
It's easy to contribute to AdSky ! If you are a developer, first, you have to create a [fork](https://github.com/Skyost/AdSky/fork) and make your changes. Then make a pull request describing what are the improvements.

If you are not a developer, well, you can contribute as well ! Report bugs, problems and improvements in the [*Issues*](https://github.com/Skyost/AdSky/issues) section. You can also contribute by [making a donation](https://www.paypal.com/cgi-bin/webscr?hosted_button_id=XLEBVBMQNTXMY&item_name=AdSky&cmd=_s-xclick).

**Anyway, help is greatly appreciated under any form !**

## Support
If you want to report a bug / suggest an improvement / anything else, do not hesitate to [open an issue](https://github.com/Skyost/AdSky/issues/new).
