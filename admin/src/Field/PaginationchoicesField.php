<?php

namespace CB\Component\Contentbuilderng\Administrator\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\ListLimitHelper;
use Joomla\CMS\Form\Field\TextField;

final class PaginationchoicesField extends TextField
{
    protected $type = 'Paginationchoices';

    protected function getInput(): string
    {
        if (trim((string) $this->value) === '') {
            $this->value = ListLimitHelper::FACTORY_CHOICES;
        } else {
            try {
                $this->value = ListLimitHelper::formatPaginationChoices(
                    ListLimitHelper::parsePaginationChoices((string) $this->value)
                );
            } catch (\UnexpectedValueException) {
                // Keep the invalid value visible so Joomla can display the validation error.
            }
        }

        return parent::getInput();
    }
}
