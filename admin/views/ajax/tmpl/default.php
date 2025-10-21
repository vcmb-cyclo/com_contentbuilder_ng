<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

ob_end_clean();
header("Content-type: text/plain; charset=UTF-8");

echo $this->data;

exit;