<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

final class FormsController extends AdminController
{
    /**
     * Nom de la vue liste et item (convention Joomla 6).
     */
    protected $view_list = 'forms';
    protected $view_item = 'form';

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Si tu veux absolument garder ces paramètres en session (legacy),
        // tu peux le faire proprement via $this->input.
        $session = Factory::getApplication()->getSession();

        if ($this->input->getInt('email_users', -1) !== -1) {
            $session->set('email_users', $this->input->get('email_users', 'none'), 'com_contentbuilder');
        }

        if ($this->input->getInt('email_admins', -1) !== -1) {
            $session->set('email_admins', $this->input->get('email_admins', ''), 'com_contentbuilder');
        }

        if ($this->input->getInt('slideStartOffset', -1) !== -1) {
            $session->set('slideStartOffset', $this->input->getInt('slideStartOffset', 1));
        }

        if ($this->input->getInt('tabStartOffset', -1) !== -1) {
            $session->set('tabStartOffset', $this->input->getInt('tabStartOffset', 0));
        }
    }

   
    /**
     * Copie (custom)
     */
    public function copy(): void
    {
        $cid = (array) $this->input->get('cid', [], 'array');

        if (!empty($cid)) {
            $model = $this->getModel('Forms');
            $model->copy();
        }

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&limitstart=' . $this->input->getInt('limitstart'), false),
            Text::_('COM_CONTENTBUILDER_COPIED')
        );
    }

    public function display($cachable = false, $urlparams = []): void
    {
        $this->input->set('view', $this->view_list);
        parent::display($cachable, $urlparams);
    }


    /**
     * Task: forms.delete (au lieu de remove)
     * Joomla va passer cid[] dans l’input.
     */
    public function delete()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->getInput();

        $cid = $input->get('cid', [], 'array');
        ArrayHelper::toInteger($cid);

        if (!$cid) {
            $this->setRedirect(
                Route::_('index.php?option=com_contentbuilder&view=forms', false),
                Text::_('JERROR_NO_ITEMS_SELECTED'),
                'warning'
            );
            return false;
        }

        $model = $this->getModel('Form');
        $ok = $model->delete($cid);

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms', false),
            $ok ? Text::_('COM_CONTENTBUILDER_DELETED') : Text::_('COM_CONTENTBUILDER_ERROR'),
            $ok ? 'message' : 'error'
        );

        return $ok;
    }


    public function listorderup(): void
    {
        $model = $this->getModel('Forms');
        $model->listMove(-1);

        // Après une action mutative : redirect (PRG), pas display()
        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function listorderdown(): void
    {
        $model = $this->getModel('Forms');
        $model->listMove(1);

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function listsaveorder(): void
    {
        $model = $this->getModel('Forms');
        $model->listSaveOrder();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

 
    public function linkable(): void
    {
        $model = $this->getModel('Forms');
        $model->setListLinkable();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function not_linkable(): void
    {
        $model = $this->getModel('Forms');
        $model->setListNotLinkable();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function editable(): void
    {
        $model = $this->getModel('Forms');
        $model->setListEditable();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function not_editable(): void
    {
        $model = $this->getModel('Forms');
        $model->setListNotEditable();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function list_include(): void
    {
        $model = $this->getModel('Forms');
        $model->setListListInclude();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function no_list_include(): void
    {
        $model = $this->getModel('Forms');
        $model->setListNoListInclude();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function search_include(): void
    {
        $model = $this->getModel('Forms');
        $model->setListSearchInclude();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }

    public function no_search_include(): void
    {
        $model = $this->getModel('Forms');
        $model->setListNoSearchInclude();

        $this->setRedirect(
            Route::_('index.php?option=com_contentbuilder&view=forms&layout=edit&cid[]=' . $this->input->getInt('id', 0), false)
        );
    }
}
