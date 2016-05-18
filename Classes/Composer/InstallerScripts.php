<?php 
namespace OGrosko\Composer;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use OGrosko\Composer\Exception\InstallerScriptException;

/**
 * Class for Composer install scripts
 */
class InstallerScripts {

    const HTACCESS_MARKER_START = '###'.self::class.'_start###'.PHP_EOL;
    const HTACCESS_MARKER_END = PHP_EOL.'###'.self::class.'_end###';
    const HTACCESS_FILENAME = '.htaccess';

    /**
     * @param PackageEvent|Event $event
     */
	public static function postPackageInstall($event) {
        $dir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        $filePath = $dir . self::HTACCESS_FILENAME;
        if (file_exists($filePath)){
            $event->getIO()->write('-- Updating '.self::HTACCESS_FILENAME.' file --');
            self::updateHtaccess($filePath);
        }
        else {
            self::createHtaccess($filePath);
            $event->getIO()->write('-- Creating '.self::HTACCESS_FILENAME.' file --');
        }

    }

    /**
     * @param string $path
     * @throws InstallerScriptException
     */
    private static function updateHtaccess($path) {
        $content = file_get_contents($path);
        if ($content === false) throw new InstallerScriptException('Problem with reading '.$path);
        if (strpos($content, self::HTACCESS_MARKER_START) !== false) {
            $content = self::replace_between($content, self::getHtaccessString());
            $r = file_put_contents($path, $content);
        }
        else {
            $r = file_put_contents($path, self::getHtaccessString(), FILE_APPEND);
        }
        if ($r === false) throw new InstallerScriptException('Problem with writing to '.$path);
    }

    /**
     * @param string $path
     * @throws InstallerScriptException
     */
    private static function createHtaccess($path) {
        $r = file_put_contents($path, self::getHtaccessString());
        if ($r === false) throw new InstallerScriptException('Problem with creating '.$path);
    }

    /**
     * @param string $str
     * @param string $replacement
     * @return string
     */
    public static function replace_between($str, $replacement) {
        $pos = strpos($str, self::HTACCESS_MARKER_START);
        $start = $pos === false ? 0 : $pos;

        $pos = strpos($str, self::HTACCESS_MARKER_END, $start);
        $end = $pos === false ? strlen($str) : $pos + strlen(self::HTACCESS_MARKER_END);

        return substr_replace($str, $replacement, $start, $end - $start);
    }

    private static function getHtaccessString() {
        $maintenanceFile = DIRECTORY_SEPARATOR . Typo3ComposerMaintenance::MAINTENANCE_FILENAME;
        return self::HTACCESS_MARKER_START.
'<IfModule mod_rewrite.c>
    RewriteCond %{DOCUMENT_ROOT}'. $maintenanceFile .' -f
    RewriteCond %{REQUEST_URI} !'. $maintenanceFile .'
    #RewriteCond %{REMOTE_ADDR} !^127\.0\.0\.1$
    RewriteCond %{REQUEST_FILENAME} !.(gif|jpe?g|png|css|js)$
    RewriteRule  ^(.*) '. $maintenanceFile .' [R=503,L]
    ErrorDocument 503 '. $maintenanceFile . '
</IfModule>'
                .self::HTACCESS_MARKER_END;
    }
}