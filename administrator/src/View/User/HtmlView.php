<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\View\User;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    function display($tpl = null)
    {
        echo '<link rel="stylesheet" href="' . Uri::root(true) . '/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';

        // Get data from the model
        $subject = $this->get('Data');
        $this->subject = $subject;
        parent::display($tpl);
    }
}
