<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/cbfilterhidden.php';

if (!\class_exists('JFormFieldCbfilterhidden', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCbfilterhidden::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCbfilterhidden::class, 'JFormFieldCbfilterhidden');
}
