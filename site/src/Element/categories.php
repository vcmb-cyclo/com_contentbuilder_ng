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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\Database\DatabaseInterface;
use Joomla\Application\ApplicationInterface;

class JFormFieldCategories extends FormField
{

    protected $type = 'Forms';

    protected function getInput()
    {
        $class = $this->element['class'] ? $this->element['class'] : "text_area";

        // Initialise variables.
        $options = array();

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text, a.level');
        $query->from('#__categories AS a');
        $query->join('LEFT', '`#__categories` AS b ON a.lft > b.lft AND a.rgt < b.rgt');

        // Filter by the type
        $query->where('(a.extension = ' . $db->quote('com_content') . ' OR a.parent_id = 0)');

        $query->where('a.published IN (0,1)');
        $query->group('a.id');
        $query->order('a.lft ASC');

        // Get the options.
        $db->setQuery($query);

        // Check for a database error.
        try {
            $options = $db->loadObjectList();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        } // try

        // Pad the option text with spaces using depth level as a multiplier.
        for ($i = 0, $n = count($options); $i < $n; $i++) {
            // Translate ROOT
            if ($options[$i]->level == 0) {
                $options[$i]->text = Text::_('JGLOBAL_ROOT_PARENT');
            }

            $options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
        }

        // Initialise variables.
        $user = Factory::getApplication()->getIdentity();

        if (empty($id)) {
            // New item, only have to check core.create.
            foreach ($options as $i => $option) {
                // Unset the option if the user isn't authorised for it.
                if (!$user->authorise('core.create', 'com_content' . '.category.' . $option->value)) {
                    unset($options[$i]);
                }
            }
        } else {
            // Existing item is a bit more complex. Need to account for core.edit and core.edit.own.
            foreach ($options as $i => $option) {
                // Unset the option if the user isn't authorised for it.
                if (!$user->authorise('core.edit', $extension . '.category.' . $option->value)) {
                    // As a backup, check core.edit.own
                    if (!$user->authorise('core.edit.own', $extension . '.category.' . $option->value)) {
                        // No core.edit nor core.edit.own - bounce this one
                        unset($options[$i]);
                    } else {
                        // TODO I've got a funny feeling we need to check core.create here.
                        // Maybe you can only get the list of categories you are allowed to create in?
                        // Need to think about that. If so, this is the place to do the check.
                    }
                }
            }
        }


        if (isset($row) && !isset($options[0])) {
            if ($row->parent_id == '1') {
                $parent = new \stdClass();
                $parent->text = Text::_('JGLOBAL_ROOT_PARENT');
                array_unshift($options, $parent);
            }
        }

        // Merge any additional options in the XML definition.
        //$options = array_merge(parent::getOptions(), $options);

        $out = '<select style="max-width: 200px;" name="' . $this->name . '" id="' . $this->id . '" class="' . $this->element['class'] . '">' . "\n";

        $out .= '<option value="-2">' . Text::_('COM_CONTENTBUILDER_INHERIT') . '</option>' . "\n";

        foreach ($options as $category) {
            $out .= '<option ' . ($this->value == $category->value ? ' selected="selected"' : '') . 'value="' . $category->value . '">' . htmlentities($category->text, ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
        }
        $out .= '</select>' . "\n";

        return $out;
    }
}
