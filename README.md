# typo3-composer-maintenance

When you are using [Typo3 with composer mode](https://composer.typo3.org/) this scripts automatically put your website to maintenance mode when you run `composer update`.
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
		"OGrosko\\Composer\\Typo3ComposerMaintenance::maintenance_enable"
	],
	"post-update-cmd": [
		"OGrosko\\Composer\\Typo3ComposerMaintenance::maintenance_disable"
	]
},
```