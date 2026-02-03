<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// administrator/src/View/Test/HtmlView.php
// Vue simple de test.
namespace CB\Component\Contentbuilder_ng\Administrator\View\Test;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;
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
