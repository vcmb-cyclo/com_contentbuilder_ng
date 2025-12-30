<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */

// admin/src/View/Test/HtmlView.php
// Vue simple de test.
namespace CB\Component\Contentbuilder\Administrator\View\Test;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Database\DatabaseInterface;

class HtmlView extends BaseHtmlView
{
    protected $layout = 'default';
    protected $db;

    public function setDatabase(DatabaseInterface $db): void
    {
        $this->db = $db;
    }

    public function display($tpl = null): void
    {
        // Ici tu peux utiliser $this->db
        parent::display($tpl);
    }
}
