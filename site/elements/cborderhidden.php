<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/cborderhidden.php';

if (!\class_exists('JFormFieldCborderhidden', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCborderhidden::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCborderhidden::class, 'JFormFieldCborderhidden');
}
