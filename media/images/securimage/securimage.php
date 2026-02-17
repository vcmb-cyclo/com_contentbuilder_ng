<?php
/**
 * @package     ContentBuilder NG
 * @author      XDA+GIL
 * @license     GNU/GPL
 */

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$composerAutoload = JPATH_ADMINISTRATOR . '/components/com_contentbuilder_ng/vendor/autoload.php';
$vendorSecurimage = JPATH_ADMINISTRATOR . '/components/com_contentbuilder_ng/vendor/bgli100/securimage/securimage.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

if (!class_exists('Securimage') && is_file($vendorSecurimage)) {
    require_once $vendorSecurimage;
}

if (!class_exists('Securimage')) {
    throw new \RuntimeException(
        'Securimage class not found. Checked: '
        . $composerAutoload
        . ' and '
        . $vendorSecurimage
    );
}
