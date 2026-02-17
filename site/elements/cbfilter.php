<?php

\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once __DIR__ . '/../src/Element/cbfilter.php';

if (!\class_exists('JFormFieldCbfilter', false) && \class_exists(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCbfilter::class)) {
    \class_alias(\CB\Component\Contentbuilder_ng\Site\Element\JFormFieldCbfilter::class, 'JFormFieldCbfilter');
}
