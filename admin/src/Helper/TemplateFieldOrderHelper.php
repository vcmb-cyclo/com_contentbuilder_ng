<?php

namespace CB\Component\Contentbuilderng\Administrator\Helper;

\defined('_JEXEC') or die('Restricted access');

use Joomla\Database\DatabaseInterface;

final class TemplateFieldOrderHelper
{
    public static function getOrderedNames(DatabaseInterface $db, int $formId, object $form): array
    {
        $sourceNames = (array) $form->getElementNames();
        $query = $db->getQuery(true)
            ->select($db->quoteName('reference_id'))
            ->from($db->quoteName('#__contentbuilderng_elements'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('form_id') . ' = ' . $formId)
            ->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC');
        $db->setQuery($query);

        $names = [];
        foreach ((array) $db->loadColumn() as $referenceId) {
            $referenceId = (string) $referenceId;

            if (array_key_exists($referenceId, $sourceNames)) {
                $names[$referenceId] = $sourceNames[$referenceId];
            }
        }

        return $names;
    }
}
