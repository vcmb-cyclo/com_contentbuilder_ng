<?php

/**
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 * 
 * Fix pour corriger la corruption de option corrompu pendant le ctor parent
 * com_ontentbuilder au lieu de com_contentbuilder.
 */

namespace CB\Component\Contentbuilder\Administrator\Controller;

// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\AdminController;

abstract class BaseAdminController extends AdminController
{
    protected $option = 'com_contentbuilder';
    protected $text_prefix = 'COM_CONTENTBUILDER';
 
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Fix: option corrompu pendant le ctor parent
        $this->option = 'com_contentbuilder';
        $this->input->set('option', 'com_contentbuilder');
        $this->text_prefix = 'COM_CONTENTBUILDER';
    }
}
