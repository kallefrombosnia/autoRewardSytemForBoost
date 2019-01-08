# CS 1.6 Reward for Boost script

This PHP script uses gametracker.rs API to fetch data about server boosts. 
Usage of this scripts lets you to create own boost classes and lets you to automaticly award players who boost your server.

Feel free to submit issues/pull request if you'd like to contribute.

# Features

* Create own boost classes in boosts.json file
* Monitor 24/7 for new boosts
* Easy to start & maintain


# Instructions
To make this working you first need php web server (made on Apache) and MySQL server.
Upload amxx.sql to MySQL server, go to config.php and configure all things.
After that in CS 1.6 plugins.ini disable admin.amxx and uncomment admin_sql.amxx, then go to sql.cfg and configure connection to same MySQL server.

Note: to run this every few minutes you need cron job (external or internal).
# Disclaimer

Use at own risk. Didnt tested in production!
