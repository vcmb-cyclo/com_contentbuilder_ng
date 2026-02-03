<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class plgContentbuilder_ng_themesBlank extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentTemplateJavascript' => 'onContentTemplateJavascript',
            'onEditableTemplateJavascript' => 'onEditableTemplateJavascript',
            'onListViewJavascript' => 'onListViewJavascript',
            'onContentTemplateCss' => 'onContentTemplateCss',
            'onEditableTemplateCss' => 'onEditableTemplateCss',
            'onListViewCss' => 'onListViewCss',
            'onContentTemplateSample' => 'onContentTemplateSample',
            'onEditableTemplateSample' => 'onEditableTemplateSample',
        ];
    }

    /**
     * Any content template specific JS?
     * Return it here
     * 
     * @return string
     */
    function onContentTemplateJavascript()
    {

        return '';
    }

    /**
     * Any editable template specific JS?
     * Return it here
     * 
     * @return string
     */
    function onEditableTemplateJavascript()
    {

        return '';
    }

    /**
     * Any list view specific JS?
     * Return it here
     * 
     * @return string
     */
    function onListViewJavascript()
    {

        return '';
    }

    /**
     * Any content template specific CSS?
     * Return it here
     * 
     * @return string
     */
    function onContentTemplateCss()
    {

        return '';
    }

    /**
     * Any editable template specific CSS?
     * Return it here
     * 
     * @return string
     */
    function onEditableTemplateCss()
    {

        return $this->onContentTemplateCss();
    }

    /**
     * Any list view specific CSS?
     * Return it here
     * 
     * @return string
     */
    function onListViewCss()
    {

        return '';
    }

    /**
     * Return the sample html code for content here (triggered in view admin, after checking "SAMPLE"
     * 
     * @return string
     */
    function onContentTemplateSample($contentbuilder_ng_form_id, $form)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $out = '<table border="0" width="100%" class="blanktable_content"><tbody>' . "\n";
        $names = $form->getElementNames();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_ng_elements Where published = 1 And form_id = " . intval($contentbuilder_ng_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $result = $db->loadAssoc();
            if (is_array($result)) {
                if ($result['type'] != 'hidden') {
                    $out .= '{hide-if-empty ' . $name . '}' . "\n\n";
                    $out .= '<tr class="blanktable_content_row"><td width="20%" class="key" valign="top"><label>{' . $name . ':label}</label></td><td>{' . $name . ':value}</td></tr>' . "\n\n";
                    $out .= '{/hide}' . "\n\n";
                }
            }
        }
        $out .= '</tbody></table>' . "\n";
        return $out;
    }

    /**
     * Return the sample html code for editables here (triggered in view admin, after checking "SAMPLE"
     * 
     * @return string
     */
    function onEditableTemplateSample($contentbuilder_ng_form_id, $form)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $checkEditable = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__contentbuilder_ng_elements')
            ->where('published = 1')
            ->where('editable = 1')
            ->where('form_id = ' . (int) $contentbuilder_ng_form_id);
        $db->setQuery($checkEditable);
        $hasEditable = (int) $db->loadResult() > 0;
        if (!$hasEditable) {
            $msg = 'No editable elements configured; generated editable sample uses all elements.';
            Factory::getApplication()->enqueueMessage($msg, 'warning');
            Log::add($msg, Log::WARNING, 'com_contentbuilder_ng');
        }
        $out = '<table border="0" width="100%" class="blanktable_edit"><tbody>' . "\n";
        $names = $form->getElementNames();
        $hidden = array();
        foreach ($names as $reference_id => $name) {
            $whereEditable = $hasEditable ? " And editable = 1" : "";
            $db->setQuery("Select id, `type` From #__contentbuilder_ng_elements Where published = 1" . $whereEditable . " And form_id = " . intval($contentbuilder_ng_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $result = $db->loadAssoc();
            if (is_array($result)) {
                if ($result['type'] != 'hidden') {
                    $out .= '<tr class="blanktable_edit_row"><td width="20%" class="key" valign="top">{' . $name . ':label}</td><td>{' . $name . ':item}</td></tr>' . "\n";
                } else {
                    $hidden[] = '{' . $name . ':item}' . "\n";
                }
            }
        }
        $out .= '</tbody></table>' . "\n";
        foreach ($hidden as $hid) {
            $out .= $hid;
        }
        return $out;
    }
}
