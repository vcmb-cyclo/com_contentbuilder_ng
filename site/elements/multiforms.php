<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/multiforms.php';

if (!\class_exists('JFormFieldMultiforms', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldMultiforms::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldMultiforms::class, 'JFormFieldMultiforms');
}
