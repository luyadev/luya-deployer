# LUYA DEPLOYER CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.0.9 (7. May 2020)

+ Automatically set the deploy tag as app version.

## 1.0.8 (22. October 2019)

+ [#11](https://github.com/luyadev/luya-deployer/issues/11) Support new `config.php` file policy with `luya\Config` objects.

## 1.0.7 (22. July 2019)

+ Add runtime to shared folders, flush application cache on deploy instead.

## 1.0.6.1 (20. May 2019)

+ Try to improve the composer global require command, certain options could be ignored.

## 1.0.6 (18. November 2018)

+ Add option `ignorePlatformReqs` for composer install `--ignore-platform-reqs` flag.

## 1.0.5.1 (16. April 2018

+ Fixed bug when cleaning up .git folder data.

## 1.0.5 (16. April 2018)

+ Remove sensitive git and gitignore data after deployment.

## 1.0.4 (28. March 2018)

+ Remove more sensitive data after deployment.
+ Provide better verbose informations.
+ Add option to disable fxp plugin installation. (installFxpPlugin)

## 1.0.3 (22. January 2018)

+ [#4](https://github.com/luyadev/luya-deployer/issues/4) Added beforeCoreCommands() option.

## 1.0.2 (25. October 2017)

+ Update FXP Plugin to Version 1.4.2 (ensure valid bower package url).

## 1.0.1 (4. May 2017)

+ Update FXP Plugin to Version 1.3

## 1.0.0 (24. April 2017)

+ First stable release of LUYA deployer. As we are going to increase to the next Deployer Version, this 1.0.0 Version provides current implementation behavior.
