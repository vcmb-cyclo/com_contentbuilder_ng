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

class plgContentbuilder_themesBlank extends CMSPlugin
{
    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
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
    function onContentTemplateSample($contentbuilder_form_id, $form)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $out = '<table border="0" width="100%" class="blanktable_content"><tbody>' . "\n";
        $names = $form->getElementNames();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_elements Where published = 1 And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
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
    function onEditableTemplateSample($contentbuilder_form_id, $form)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $out = '<table border="0" width="100%" class="blanktable_edit"><tbody>' . "\n";
        $names = $form->getElementNames();
        $hidden = array();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_elements Where published = 1 And editable = 1 And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
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
