<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/forms.php';

if (!\class_exists('JFormFieldForms', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldForms::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldForms::class, 'JFormFieldForms');
}
