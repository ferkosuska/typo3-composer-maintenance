<?php 
namespace OGrosko\Composer;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use OGrosko\Composer\Exception\InstallerScriptException;

/**
 * Class for Composer install scripts
 */
class InstallerScripts {

    const HTACCESS_MARKER_START = '###OGrosko\Composer\InstallerScripts_start###';
    const HTACCESS_MARKER_END = '###OGrosko\Composer\InstallerScripts_end###';
    const HTACCESS_FILENAME = '.htaccess';

    /**
     * @param PackageEvent|Event $event
     */
	public static function htaccessInstall($event) {
        if (file_exists(self::getHtaccessFilePath($event))){
            $event->getIO()->write('-- Updating '.self::HTACCESS_FILENAME.' file --');
            self::updateHtaccess($event);
        }
        else {
            self::createHtaccess($event);
            $event->getIO()->write('-- Creating '.self::HTACCESS_FILENAME.' file --');
        }

    }

    /**
     * @param PackageEvent|Event $event
     * @throws InstallerScriptException
     */
    private static function updateHtaccess($event) {
        $path = self::getHtaccessFilePath($event);
        $content = file_get_contents($path);

        if ($content === false) throw new InstallerScriptException('Problem with reading '.$path);
        if (strpos($content, self::HTACCESS_MARKER_START) !== false) {
            $content = self::replaceHtaccessConfig($content, self::getHtaccessString($event));
            $r = file_put_contents($path, $content);
        }
        else {
            $r = file_put_contents($path, self::getHtaccessString($event), FILE_APPEND);
        }
        if ($r === false) throw new InstallerScriptException('Problem with writing to '.$path);
    }

    /**
     * @param PackageEvent|Event $event
     * @throws InstallerScriptException
     */
    private static function createHtaccess($event) {
        $path = self::getHtaccessFilePath($event);
        $r = file_put_contents($path, self::getHtaccessString($event));
        if ($r === false) throw new InstallerScriptException('Problem with creating '.$path);
    }

    /**
     * @param string $str
     * @param string $replacement
     * @return string
     */
    public static function replaceHtaccessConfig($str, $replacement) {
        $pos = strpos($str, self::HTACCESS_MARKER_START);
        $start = $pos === false ? 0 : $pos;

        $pos = strpos($str, self::HTACCESS_MARKER_END, $start);
        $end = $pos === false ? strlen($str) : $pos + strlen(self::HTACCESS_MARKER_END);

        return substr_replace($str, $replacement, $start, $end - $start);
    }


    /**
     * @param PackageEvent|Event $event
     * @return string
     */
    public static function getHtaccessString($event) {
        $maintenanceFile = DIRECTORY_SEPARATOR . Typo3ComposerMaintenance::MAINTENANCE_FILENAME;
        $ipExclusions = self::getIpExclusions($event);
        $ipExclusionsHtaccessString = '';

        if (is_array($ipExclusions) and count($ipExclusions) > 0) {
            foreach ($ipExclusions as $ip) {
                $ipExclusionsHtaccessString .= 'RewriteCond %{REMOTE_ADDR} !^'.$ip.'$'.PHP_EOL;
            }
        }

        return self::HTACCESS_MARKER_START.PHP_EOL.
'<IfModule mod_rewrite.c>
    RewriteCond %{DOCUMENT_ROOT}'. $maintenanceFile .' -f
    RewriteCond %{REQUEST_URI} !'. $maintenanceFile .'
    '. $ipExclusionsHtaccessString .'
    RewriteCond %{REQUEST_FILENAME} !.(gif|jpe?g|png|css|js)$
    RewriteRule  ^(.*) '. $maintenanceFile .' [R=503,L]
    ErrorDocument 503 '. $maintenanceFile . '
</IfModule>'
                .PHP_EOL.self::HTACCESS_MARKER_END;
    }

    /**
     * @param PackageEvent|Event $event
     * @return array
     */
    public static function getIpExclusions($event) {
        $exclusions = array();
        $extra = $event->getComposer()->getPackage()->getExtra();

        if (isset($extra[Typo3ComposerMaintenance::EXTRA_KEY])
            and isset($extra[Typo3ComposerMaintenance::EXTRA_KEY][Typo3ComposerMaintenance::EXTRA_MAINTENANCE_EXEPTIONS_IP_KEY])
        ){
            $exclusions = $extra[Typo3ComposerMaintenance::EXTRA_KEY][Typo3ComposerMaintenance::EXTRA_MAINTENANCE_EXEPTIONS_IP_KEY];
            $exclusions = array_map(function($ip){
                return str_ireplace('.', '\.', $ip);
            }, $exclusions);
        }

        return $exclusions;
    }

    /**
     * @param PackageEvent|Event $event
     * @return string
     */
    private static function getHtaccessFilePath($event){
        $dir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return $dir . self::HTACCESS_FILENAME;
    }
}