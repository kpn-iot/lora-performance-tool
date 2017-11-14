LoRa Performance Tool
=====================
(c) 2017 KPN

License: GNU General Public License v3.0

## About
The LoRa Performance Tool was initially developed to easily generate standard reports about the accuracy of LoRa Geolocation. Later the more general coverage reports were added to make the Performance Tool a universal tool to assess network performance. The code now supports the ingestion of data and metadata from the KPN LoRa Network, which runs Actility Thingpark.

The LoRa Performance Tool has been made available as open source project by KPN to provide the LoRa Community with a starting point for similar projects.

The code is based on [Yii 2.0](http://www.yiiframework.com).

## Prerequisites
* PHP>=5.4. For Windows [XAMPP](https://www.apachefriends.org/index.html) is an easy way to get started with a local web server
* [Composer - Dependency Manager for PHP](https://getcomposer.org/)
* [Bower - A package manager for the web](https://bower.io/)

## Getting started
* `$ composer global require fxp/composer-asset-plugin` - asure correct installation of bower via composer
* `$ composer install` - install php dependencies
* `$ bower install` - install js dependencies
* Use `config/db.php.example` as example to create the database connection configuration file `config/db.php`
* Use `config/users.php.example` as example to create the database users configuration file `config/users.php`
* Set a cookieValidationKey in `config/web.php:26`
* `$ yii migrate` - perform database migrations

## Short description
After going through the Getting started you should be able to reach the web interface and log in. Have your the data of your devices point to the API endpoint that is shown on the front page of the tool. (Note: this will only work if it is on an Internet facing interface with HTTPS). Then create a device in the tool to provision the device on the tool. After creating the device all incoming data (called frames) will be put in a session. If not, check the Api Log page for debug information. The tool will start new sessions for the incoming frames when the frame counter resets or after midnight. Metadata of gateways will be put in the gateway table on the fly. 

There are two different reports: Coverage report and Geoloc reports. Coverage reports show channel and spreading factor usage and RSSI/SNR information. Geoloc reports show the accuracy and success rate of Geolocation. Reports can be generated for sessions, session sets (a collection of sessions) and for an arbitrary set of frames (in menu Live measurements > Report).

## Authors
* Paul Marcelis <paul.marcelis@kpn.com>
