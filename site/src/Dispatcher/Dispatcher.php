<?php
namespace CB\Component\Contentbuilder_ng\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

class Dispatcher extends ComponentDispatcher
{
    public function dispatch()
    {
        $input = $this->input; // utilise l'input du dispatcher

        $view = $input->getCmd('view', '');
        $task = $input->getCmd('task', '');

        // Si aucune task n'est fournie, on force <view>.display
        // Exemple: ?view=list  => task=list.display
        if ($task === '' && $view !== '') {
            $input->set('task', $view . '.display');
        }

        parent::dispatch();
    }
}
