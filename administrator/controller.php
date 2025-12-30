<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator;

// no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

Use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

require_once(JPATH_SITE .'/administrator/components/com_contentbuilder/classes/controllerlegacy.php');

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
