<?php

namespace CB\Component\Contentbuilderng\Site\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use CB\Component\Contentbuilderng\Site\Helper\MenuViewDefaultsHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class MenucategoryField extends ListField
{
    public $type = 'Menucategory';

    protected function getOptions(): array
    {
        $formId = MenuViewDefaultsHelper::getSelectedFormId($this->form);
        $default = MenuViewDefaultsHelper::get($formId)['cb_category_id'] ?? '';
        $default = $default !== '' ? $default : Text::_('JGLOBAL_ROOT_PARENT');
        $options = [
            HTMLHelper::_('select.option', '-2', Text::sprintf('COM_CONTENTBUILDERNG_USE_DEFAULT_VALUE', $default)),
        ];
        $db = RuntimeContextHelper::getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('a.id', 'value'),
                $db->quoteName('a.title', 'text'),
                $db->quoteName('a.level'),
            ])
            ->from($db->quoteName('#__categories', 'a'))
            ->where('(' . $db->quoteName('a.extension') . ' = ' . $db->quote('com_content')
                . ' OR ' . $db->quoteName('a.parent_id') . ' = 0)')
            ->whereIn($db->quoteName('a.published'), [0, 1])
            ->order($db->quoteName('a.lft') . ' ASC');
        $db->setQuery($query);

        foreach ((array) $db->loadObjectList() as $category) {
            if (
                !RuntimeContextHelper::getApplication()->getIdentity()->authorise(
                    'core.create',
                    'com_content.category.' . $category->value
                )
            ) {
                continue;
            }

            $text = (int) $category->level === 0
                ? Text::_('JGLOBAL_ROOT_PARENT')
                : str_repeat('- ', (int) $category->level) . $category->text;
            $options[] = HTMLHelper::_('select.option', (string) $category->value, $text);
        }

        return array_merge($options, parent::getOptions());
    }
}
