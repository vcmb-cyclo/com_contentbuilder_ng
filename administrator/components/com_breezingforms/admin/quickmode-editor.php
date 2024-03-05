<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

if (BFRequest::getVar('task') == 'editor_saved') {
    Factory::getDocument()->addScriptDeclaration('
    parent.jQuery(".modal-header .close").trigger("click");
    ');
}

$active_language_code = BFRequest::getVar('active_language_code', '');
if ($active_language_code != '') {
    $active_language_code = '_translation' . $active_language_code;
}


$editor = Editor::getInstance(Factory::getApplication()->get('editor'));

$description00 = setContent('bfEditor', '\'+item.properties[key]+\'');
$description = setContent('bfEditor', '\'+item.properties[key]+\'');
$description0 = setContent('bfEditor', 'item.properties[key]');

$intro = setContent('bfEditor', '\'+item.properties[key]+\'');
$intro0 = setContent('bfEditor', 'item.properties[key]');
$intro00 = setContent('bfEditor', '\'+item.properties[key]+\'');

// unfortunately, we must replace as setContent adds auto quotes
$description = str_replace('\'', '"', $description);
$description00 = str_replace('\'', '"', $description00);

$intro = str_replace('\'', '"', $intro);
$intro00 = str_replace('\'', '"', $intro);

if ($editor == Editor::getInstance('tinymcebootstrap')) {
    $intro0 = str_replace('item.properties[key]', '""+item.properties[key]+""', $intro0);
    $description0 = str_replace('item.properties[key]', '""+item.properties[key]+""', $description0);
} else {
    $intro0 = str_replace('item.properties[key]', '"+item.properties[key]+"', $intro0);
    $description0 = str_replace('item.properties[key]', '"+item.properties[key]+"', $description0);
}

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));

echo '<form action="index.php" method="post" name="adminForm" id="adminForm">';

echo '<input type="submit" class="btn btn-primary" value="' . Text::_('SAVE') . '" onclick="saveText();"/><br/><br/>';
echo '<div id="bfModalContainer" style="width:900px;">' . $editor->display("bfEditor", '', 900, 300, 40, 20, false) . '</div>';
echo '<br/><input type="submit" class="btn btn-primary" value="' . Text::_('SAVE') . '" onclick="saveText();"/>';

echo '<script>
function bfLoadText(){
        var keyPageIntro   = "pageIntro' . $active_language_code . '";
        var keyDescription = "description' . $active_language_code . '";
            
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);

	// workaround for quote bug with jce
	var testEditor = ' . getContent('bfEditor') . '

	if(testEditor == "item.properties[keyPageIntro]" || testEditor == "item.properties[keyDescription]"){
		if(item && item.properties.type == "page"){
			setTimeout("setIntro()",100);
		} else if(item && item.properties.type == "section"){
			setTimeout("setDescription()",250);
		}
	} else {
                if(item && item.properties.type == "page"){
			setTimeout("setIntro0()",100);
		} else if(item && item.properties.type == "section"){

			setTimeout("setDescription0()",250);
		}
        }
};
function saveText(){
        var keyPageIntro   = "pageIntro' . $active_language_code . '";
        var keyDescription = "description' . $active_language_code . '";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
	if(item && item.properties.type == "page"){
		item.properties[keyPageIntro] = ' . getContent('bfEditor') . '
	} else if(item && item.properties.type == "section"){
		item.properties[keyDescription] = ' . getContent('bfEditor') . '
	}
	' . save('bfEditor') . '
}
function setIntro0(){
        var key = "pageIntro' . $active_language_code . '";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	' . $intro0 . '
        var testEditor = ' . getContent('bfEditor') . '
        
        if( testEditor == "+item.properties[key]+" || testEditor == "<div>\"+item.properties[key]+\"</div>" || testEditor == "<p>\"+item.properties[key]+\"</p>" || testEditor == "\"+item.properties[key]+\"" || testEditor == "item.properties[key]" || testEditor == "<p>item.properties[key]</p>" || testEditor == "<div>item.properties[\'pageIntro' . $active_language_code . '\']</div>" ){
            setTimeout("setIntro00()",250);
        }
}
function setIntro00(){
    var key = "pageIntro' . $active_language_code . '";
    var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
    if(typeof item.properties[key] == "undefined"){
        item.properties[key] = "";
    }
    var testEditor = ' . getContent('bfEditor') . '
    if( testEditor == "+item.properties[key]+" ){
        ' . setContent('bfEditor', 'item.properties[key]') . '
    }
    else{
    ' . $intro00 . '
    }
}
function setDescription0(){
        var key = "description' . $active_language_code . '";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	' . $description0 . '
        var testEditor = ' . getContent('bfEditor') . '
            
        if( testEditor == "+item.properties[key]+" || testEditor == "<div>\"+item.properties[key]+\"</div>" || testEditor == "<p>\"+item.properties[key]+\"</p>" || testEditor == "\"+item.properties[key]+\"" || testEditor == "item.properties[key]" || testEditor == "<p>item.properties[key]</p>" || testEditor == "<div>item.properties[key]</div>"){
        
            setTimeout("setDescription00()",250);
        }
}
function setDescription00(){
    var key = "description' . $active_language_code . '";
    var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
    if(typeof item.properties[key] == "undefined"){
        item.properties[key] = "";
    }
    var testEditor = ' . getContent('bfEditor') . '
    if( testEditor == "+item.properties[key]+" ){
        ' . setContent('bfEditor', 'item.properties[key]') . '
    }
    else{
        ' . $description00 . '
    }
}
function setIntro(){
        var key = "pageIntro' . $active_language_code . '";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
	' . $intro . '
}
function setDescription(){
        var key = "description' . $active_language_code . '";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	' . $description . '
}

setTimeout("bfLoadText()",500);
</script>

    <input type="hidden" name="option" value="com_breezingforms"/>
    <input type="hidden" name="act" value="quickmode_editor"/>
    <input type="hidden" name="tmpl" value="component"/>
    <input type="hidden" name="task" value="editor_saved"/>
</form>
';

function getContent($editor)
{

    return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
}

function setContent($editor, $content)
{

    return 'Joomla.editors.instances[' . json_encode($editor) . '].setValue(' . json_encode($content) . ')';
}

function save($editor)
{
    return 'document.adminForm.submit();';
}



