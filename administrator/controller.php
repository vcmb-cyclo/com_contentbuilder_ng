<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Administrator;

// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

Use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use CB\Component\Contentbuilder_ng\Administrator\CBRequest;

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

	    if(Factory::getApplication()->input->get('market', '', 'string') == 'true'){
            Factory::getApplication()->redirect('https://breezingforms.vcmb.fr');
        }
    }

}
