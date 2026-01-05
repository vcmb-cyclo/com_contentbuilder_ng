<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator;

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

Use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class ContentbuilderController extends BaseController
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display($cachable = false, $urlparams = array())
    {
        parent::display();

	    if(CBRequest::getVar('market','') == 'true'){
            Factory::getApplication()->redirect('https://breezingforms.vcmb.fr');
        }
    }

}
