<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentbuilder_themesJoomla3 extends CMSPlugin
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
        $out = '<ul class="category list-striped list-condensed">' . "\n";
        $names = $form->getElementNames();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_elements Where published = 1 And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $result = $db->loadAssoc();
            if (is_array($result)) {
                if ($result['type'] != 'hidden') {
                    $out .= '{hide-if-empty ' . $name . '}' . "\n\n";
                    $out .= '<li class="cat-list-row0" ><strong class="list-title">{' . $name . ':label}</strong><div>{' . $name . ':value}</div></li>' . "\n\n";
                    $out .= '{/hide}' . "\n\n";
                }
            }
        }
        $out .= '</ul>' . "\n";
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
        $out = '' . "\n";
        $names = $form->getElementNames();
        $hidden = array();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_elements Where published = 1 And editable = 1 And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $result = $db->loadAssoc();
            if (is_array($result)) {
                if ($result['type'] != 'hidden') {
                    if ($result['type'] == 'checkboxgroup') {

                        $out .= '<div class="control-group form-inline"><div class="control-label">{' . $name . ':label}</div> <div class="controls"><fieldset class="checkbox">{' . $name . ':item}</fieldset></div>';

                    } else if ($result['type'] == 'radiogroup') {

                        $out .= '<div class="control-group form-inline"><div class="control-label">{' . $name . ':label}</div> <div class="controls"><fieldset class="radio">{' . $name . ':item}</fieldset></div>';

                    } else {
                        $out .= '<div class="control-group form-inline"><div class="control-label">{' . $name . ':label}</div> 
                                <div class="controls">{' . $name . ':item}</div></div>' . "\n";
                    }
                } else {
                    $hidden[] = '{' . $name . ':item}' . "\n";
                }
            }
        }
        $out .= '' . "\n";
        foreach ($hidden as $hid) {
            $out .= $hid;
        }
        return $out;
    }
}
