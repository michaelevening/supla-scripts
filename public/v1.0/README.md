# Supla Scripts

Utilites that leverage Supla REST Api in order to provide some nice URLs.

# Installation

Clone this repo and run `composer install` inside the root directory. You might need to [install composer](https://getcomposer.org/download/) first.

Copy `config.php.sample` into `config.php` and adjust settings there.

Set the `HTTP_BASIC_USER` and `HTTP_BASIC_PASSWORD` if you want to protect
execution of the scripts with additional credentials.

# Features

Instructions below assume you have cloned and configured the scripts and that
the project is available on `https://your-server.com/supla-scripts/`.

## Toggle channel state

Open the `https://your-server.com/supla-scripts/toggle/123`, where `123` is
the id of the channel assigned to your account and that should be toggled.

The script will output `ON` if the channel has been turned on and `OFF` otherwise.

## Read temperature

Open the `https://your-server.com/supla-scripts/temperature/123`, where `123` is
the id of the channel assigned to your account and that is a thermometer.

The script will output the temperature in Celcius degrees, e.g. `21.65°C`.

## Temperature stats

Allow to calculate extreme or avareage values of temperatures of specified channels.

Examples, assuming that `123` is the number of the channel with your thermometer:

* `https://your-server.com/supla-scripts/temperature-stats/123/max/-1day` will return
  max temperature and date from last 24 hours
* `https://your-server.com/supla-scripts/temperature-stats/123/min/-1week` will return
  min temperature and date from last 7 days
* `https://your-server.com/supla-scripts/temperature-stats/123/avg/-1month` will return
  average temperature from the last month

## Thermostat

Simple script that decides based on average temperature from chosen
thermometers whether to turn the heater on or off.

Copy the `thermostat/config.php.sample` into `thermostat/config.php`.
Adjust the values there

* `thermometers` is an array of channel ids that are thermometers to check an
  calculate the average temperature from
* `heater` is the channel id of heater to manage
* `heatFrom` is a temperature when the heating should start
* `heatTo` is a temperature when the heating should end

After configuration, add the script to crontab by running `crontab -e` and adding
the following line (path might need to be tweaked).

```
*/5 * * * * /usr/bin/php /home/supla/supla-scripts/thermostat/thermostat.php
```

## Scenes

Prepare a scene commands by using a notation `action1,channel1|action2,channel2,param`
and make a request to `https://your-server.com/supla-scripts/scenes/command`.

Examples:

 * `https://your-server.com/supla-scripts/scenes/turnOn,123|turnOff,124`
 * `https://your-server.com/supla-scripts/scenes/openClose,111|openClose,112|shut,113`
 * `https://your-server.com/supla-scripts/scenes/setRGB,7330,16776960,100`
 
Make sure the action can be executed on the chosen channel.

Available actions:

 * `turnOn` and `turnOff` for lights/switches
 * `openClose` to change the state of gates
 * `shut` and `reveal` for rolette shutters

# Create widgest on Android

Install the [HTTP Request Widget](https://play.google.com/store/apps/details?id=com.idlegandalf.httprequestwidget)
application.

Add new widget and supply an addres for the script to utilize its feature.

You can use the *parse response* option in order to display the result
(e.g. `ON` or `OFF` or temperature).
