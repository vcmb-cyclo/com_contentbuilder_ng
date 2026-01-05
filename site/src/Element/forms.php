<?php

/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Site\Element;

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;

class JFormFieldForms extends FormField
{

	protected $type = 'Forms';

	protected function getInput()
	{
		$class = $this->element['class'] ? $this->element['class'] : "text_area";
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$db->setQuery("Select id,`name` From #__contentbuilder_forms Where published = 1 Order By `ordering`");
		$status = $db->loadObjectList();
		return HTMLHelper::_('select.genericlist', $status, $this->name, '" onchange="if(typeof contentbuilder_setFormId != \'undefined\') { contentbuilder_setFormId(this.options[this.selectedIndex].value); }" class="' . $this->element['class'] . '"', 'id', 'name', $this->value);
	}
}