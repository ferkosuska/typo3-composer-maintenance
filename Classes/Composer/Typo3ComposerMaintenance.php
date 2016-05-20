<?php 
namespace OGrosko\Composer;

use Composer\Script\Event;
use OGrosko\Composer\Exception\Typo3ComposerMaintenanceException;

class Typo3ComposerMaintenance {

	const MAINTENANCE_FILENAME = '503.html';
	const MAINTENANCE_TEMPLATE_PATH = '../../Templates';
    const EXTRA_KEY = 'ogrosko-composer-typo3composermaintenance';
    const EXTRA_TEMPLATE_KEY = 'template-path';
    const EXTRA_MAINTENANCE_EXEPTIONS_IP_KEY = 'exclude-ips';

    /**
     * Enable maintenance mode
     *
     * @param Event $event
     * @return int
     * @throws Typo3ComposerMaintenanceException
     */
	public static function maintenance_enable(Event $event) {
        $io = $event->getIO();

        if (self::isMaintenanceEnabled()) {
            $io->write('Maintenance mode already enabled. Nothing to do.');
            if (!$io->askConfirmation('Do you want to update templates or '.InstallerScripts::HTACCESS_FILENAME.' files? [yes/no:default]:', false)) {
                return;
            }
        }
        else {
            $proceed = $io->askConfirmation('Do you want to enable maintenance mode? [yes/no:default]:', false);
            if (!$proceed) return;
        }


        $file = self::getMaintenanceFilePath();
		
		$maintenance_string = file_get_contents(self::getTemplatePath($event));
		if (file_exists($file)) {
			unlink($file);
		}
		$result = file_put_contents($file, $maintenance_string);
        InstallerScripts::htaccessInstall($event);

		if ($result !== false) {
			$event->getIO()->write("-- Maintenance mode successfully enabled --");
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
        $io = $event->getIO();

        if (!self::isMaintenanceEnabled()) {
            $io->write('Maintenance mode is disabled. Nothing to do.');
            return;
        }

        $proceed = $io->askConfirmation('Do you want to disable maintenance mode? [yes/no:default]:', false);
        if (!$proceed) return;

		$file = self::getMaintenanceFilePath();

		$result = true;

		if (file_exists($file)) {
			$result = unlink($file);
		}

		if ($result) {
			$event->getIO()->write('-- Maintenance mode successfully disabled --');
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
     * @return bool
     */
    private static function isMaintenanceEnabled() {
        $filePath = self::getMaintenanceFilePath();
        return file_exists($filePath);
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

        if (isset($extra[self::EXTRA_KEY]) and isset($extra[self::EXTRA_KEY][self::EXTRA_TEMPLATE_KEY])){
            $dir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..';
            $path = $extra[self::EXTRA_KEY][self::EXTRA_TEMPLATE_KEY];
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tplName;

        if (!file_exists($filePath))
            throw new Typo3ComposerMaintenanceException('Template file doesn\'t exists. (file path: '.$filePath.')');

		return $filePath;
	}


}