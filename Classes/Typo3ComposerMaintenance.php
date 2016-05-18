<?php 
namespace OGrosko\Composer;

use Composer\Script\Event;
use OGrosko\Composer\Exception\Typo3ComposerMaintenanceException;

class Typo3ComposerMaintenance {

	const MAINTENANCE_FILENAME = 'maintenance.html';
	const MAINTENANCE_TEMPLATE_PATH = 'Templates';
    const EXTRA_TEMPLATE_KEY = 'og-composer-template-path';

    /**
     * Enable maintenance mode
     *
     * @param Event $event
     * @return int
     * @throws Typo3ComposerMaintenanceException
     */
	public static function maintenance_enable(Event $event) {
		$file = self::getMaintenanceFilePath($event);
		
		$maintenance_string = file_get_contents(self::getTemplatePath($event));
		if (file_exists($file)) {
			unlink($file);
		}
		$result = file_put_contents($file, $maintenance_string);

		if ($result !== false) {
			$event->getIO()->write(">> Maintenance mode successfully enabled <<");
		}
		else {
			throw new Exception\Typo3ComposerMaintenanceException('Problem with enabling maintenance mode');
		}

		return $result;
	}


    /**
     * Disable maintenance mode
     *
     * @param Event $event
     * @return bool
     * @throws Typo3ComposerMaintenanceException
     */
	public static function maintenance_disable(Event $event) {
		$file = self::getMaintenanceFilePath($event);

		$result = true;

		if (file_exists($file)) {
			$result = unlink($file);
		}

		if ($result) {
			$event->getIO()->write('>> Maintenance mode successfully disabled <<');
		}
		else {
			throw new Exception\Typo3ComposerMaintenanceException('Problem with disabling Wordpress maintenance mode');
		}

		return $result;
	}

    /**
     *
     * @return string
     */
	private static function getMaintenanceFilePath() {		
		return getcwd() . DIRECTORY_SEPARATOR . self::MAINTENANCE_FILENAME;
	}

    /**
     * Get template path
     *
     * @param Event $event
     * @param string $tplName
     * @return string
     * @throws Typo3ComposerMaintenanceException
     */
	private static function getTemplatePath(Event $event, $tplName = '') {
        if ($tplName === '') $tplName = self::MAINTENANCE_FILENAME;
        $extra = $event->getComposer()->getPackage()->getExtra();
        $path = self::MAINTENANCE_TEMPLATE_PATH;
        $dir = __DIR__;

        if (isset($extra[self::EXTRA_TEMPLATE_KEY])){
            $dir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..';
            $path = $extra[self::EXTRA_TEMPLATE_KEY];
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tplName;

        if (!file_exists($filePath))
            throw new Typo3ComposerMaintenanceException('Template file doesn\'t exists. (file path: '.$filePath.')');

		return $filePath;
	}
}