<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\Ajax;

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

ob_end_clean();
header("Content-type: text/plain; charset=UTF-8");

echo $this->data;

exit;