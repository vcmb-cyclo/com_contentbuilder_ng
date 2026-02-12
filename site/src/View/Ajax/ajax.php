<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Site\View\Ajax;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

ob_end_clean();
header("Content-type: text/plain; charset=UTF-8");

echo $this->data;

exit;