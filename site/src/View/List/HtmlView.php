<?php

/**
 * ContentBuilder List view.
 *
 * List view of the site interface
 *
 * @package     ContentBuilder
 * @subpackage  Site.View
 * @author      Xavier DANO
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder\Site\View\List;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $state;
    protected $item;
    protected $form;

    public function display($tpl = null): void
    {
        $this->state = $this->getModel->getState();
        $this->item  = $this->getModel()->getItem();   // si ton model fournit Item
        $this->form  = $this->getModel()->getForm();   // si c’est une vue avec formulaire

        parent::display($tpl);
    }
}
