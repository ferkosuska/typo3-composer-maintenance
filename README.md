# typo3-composer-maintenance

When you are using [Typo3 with composer mode](https://composer.typo3.org/) (I'm sure not only for typo3), this scripts automatically put your website to maintenance mode when you run `composer update`.
typo3-composer-maintenance are collection of simple scripts for composer pre-update/post-update section.

## Installation using [Composer](http://getcomposer.org/)

```bash
$ composer require ogrosko/typo3-composer-maintenance
```

## Usage
Add following config in `scripts` section of your `composer.json` file

```json
"scripts": {
	"pre-update-cmd": [
		"@enableMaintenance"
	],
	"post-update-cmd": [
		"@disableMaintenance"
	],
	"enableMaintenance": "OGrosko\\Composer\\Typo3ComposerMaintenance::maintenance_enable",
	"disableMaintenance": "OGrosko\\Composer\\Typo3ComposerMaintenance::maintenance_disable"
},
```

## Config
Also some extra config avalible in `extra` section

	* `template-path` - use your custom maintenance template path
	* `exclude-ips` - exclude your ip from maintenance mode

```json
"extra": {
	"ogrosko-composer-typo3composermaintenance": {
		"template-path": "tpl/",
		"exclude-ips": [
			"127.0.0.1",
			"192.168.1.1"
		]
	}
}
```		