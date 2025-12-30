<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */

// admin/src/Controller/DisplayController.php

namespace CB\Component\Contentbuilder\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class DisplayController extends BaseController
{

    protected $default_view = 'test';


    public function display($cachable = false, $urlparams = [])
    {
        if (!Factory::getApplication()->getIdentity()
            ->authorise('contentbuilder.admin', 'com_contentbuilder')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $viewName = $this->input->getCmd('view', $this->default_view);

        // Construire le namespace complet de la vue
        $class = "CB\\Component\\Contentbuilder\\Administrator\\View\\" . ucfirst($viewName) . "\\HtmlView";

        if (!class_exists($class)) {
            // Vue non trouvée : erreur claire
            throw new \RuntimeException("La vue '$viewName' n'existe pas dans le composant Contentbuilder.", 404);
        }

        return parent::display($cachable, $urlparams);
    }

    // Optionnel : définir la vue par défaut si vous avez une vue "home" du composant
    // protected $default_view = 'test'; // ou le nom de votre vue principale

    // Vous pouvez laisser vide, ou surcharger display() si besoin plus tard
}