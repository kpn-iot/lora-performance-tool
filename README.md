LoRa Performance Tool
=====================
(c) 2017 KPN

License: GNU General Public License v3.0

## About
The LoRa Performance Tool was initially developed to easily generate standard reports about the accuracy of LoRa Geolocation. Later the more general coverage reports were added to make the Performance Tool a universal tool to assess network performance. The code now supports the ingestion of data and metadata from the KPN LoRa Network, which runs Actility Thingpark.

The LoRa Performance Tool has been made available as open source project by KPN to provide the LoRa Community with a starting point for similar projects.

The code is based on [Yii 2.0](http://www.yiiframework.com).

## Prerequisites
* PHP>=7.0
* A database like MySQL
* [Composer - Dependency Manager for PHP](https://getcomposer.org/)
* [Bower - A package manager for the web](https://bower.io/)

For Windows [XAMPP](https://www.apachefriends.org/index.html) is an easy way to get started with a local web server. It offers Apache, MySQL and PHP in a single install.

## Getting started
* `$ composer install` - install php dependencies
* Use `config/db.php.example` as example to create the database connection configuration file `config/db.php`
* Use `config/users.php.example` as example to create the database users configuration file `config/users.php`
* Set a cookieValidationKey in `config/web.php:27`
* `$ ./yii migrate --interactive=0` - perform database migrations
* (If you are upgrading from v1.0.2 to a higher version) `$ ./yii sessions/update-properties` to eager calculate the session properties cache table (can take some time if you have a lot of sessions)

`$` indicates a command to be executed in the root folder of the project.

## Short description
After going through the Getting started you should be able to reach the web interface and log in. Have your the data of your devices point to the API endpoint that is shown on the front page of the tool. (Note: this will only work if it is on an Internet facing interface with HTTPS). Then create a device in the tool to provision the device on the tool. After creating the device all incoming data (called frames) will be put in a session. If not, check the Api Log page for debug information. The tool will start new sessions for the incoming frames when the frame counter resets or after midnight. Metadata of gateways will be put in the gateway table on the fly. 

There are two different reports: Coverage report and Location reports. Coverage reports show channel and spreading factor usage and RSSI/SNR information. Location reports show the accuracy and success rate of Geolocation. Reports can be generated for sessions, session sets (a collection of sessions) and for an arbitrary set of frames (in menu Live measurements > Report).

## Authors
* Paul Marcelis <paul.marcelis@kpn.com>

## Useful queries
### Radius of LoRa geolocation vs calculated location
```
SELECT d.id as device_id, d.name AS device_name, d.device_eui,
s.id AS session_id, s.description AS session_description,
f.id AS frame_id, f.count_up, replace(f.latitude, '.', ',') AS latitude,  replace(f.longitude, '.', ',') AS longitude, f.gateway_count, f.`channel`, f.sf,
replace(f.latitude_lora, '.', ',') AS latitude_lora, replace(f.longitude_lora, '.', ',') AS longitude_lora, f.location_age_lora, replace(f.location_radius_lora, '.', ',') AS location_radius, f.location_algorithm_lora, f.time,
if(f.latitude is not NULL AND f.latitude != 0 AND f.longitude != 0 AND f.longitude is not null AND f.latitude_lora is not null AND f.longitude_lora is not NULL AND location_age_lora < 10, round(coordinate_distance(f.latitude, f.longitude, latitude_lora, longitude_lora)*1000,5), NULL) AS calculated_distance
FROM device_group_links dgl
LEFT JOIN devices d ON d.id = dgl.device_id
LEFT JOIN sessions s ON s.device_id = d.id
LEFT JOIN session_properties sp ON sp.session_id = s.id
LEFT JOIN frames f ON f.session_id = s.id
WHERE dgl.group_id = 3 AND sp.session_date_at > SUBDATE(UTC_TIMESTAMP(),7)
ORDER BY device_id, f.created_at desc
```
