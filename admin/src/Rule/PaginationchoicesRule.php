<?php

namespace CB\Component\Contentbuilderng\Administrator\Rule;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\ListLimitHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

final class PaginationchoicesRule extends FormRule
{
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null): bool
    {
        try {
            ListLimitHelper::parsePaginationChoices((string) $value);
        } catch (\UnexpectedValueException) {
            return false;
        }

        return true;
    }
}
