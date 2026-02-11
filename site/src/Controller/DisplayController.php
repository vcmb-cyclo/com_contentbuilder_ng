<?php
/**
 * @package     ContentBuilder NG
 * @author      Xavier DANO / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 * 
 * Custom dispatcher to Controllers.
*/
namespace CB\Component\Contentbuilder_ng\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

class Dispatcher extends ComponentDispatcher
{
    public function dispatch(): void
    {
        // On lit les variables AVANT la sélection du controller
        $view = $this->input->getCmd('view', '');
        $task = $this->input->getCmd('task', '');

        // Mapping propre: menu Joomla => view=list, task vide => on force ListController::display
        if ($view === 'list' && $task === '') {
            $this->input->set('task', 'list.display');
        }

        parent::dispatch();
    }
}
