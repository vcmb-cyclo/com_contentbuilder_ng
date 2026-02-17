<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/categories.php';

if (!\class_exists('JFormFieldCategories', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCategories::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCategories::class, 'JFormFieldCategories');
}
