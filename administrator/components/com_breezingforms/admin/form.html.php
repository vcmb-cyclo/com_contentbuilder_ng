<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.9
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Event\Event;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HTML_facileFormsForm
{

    static function edit($option, $tabpane, $pkg, &$row, &$lists, $caller)
    {
        global $ff_admsite, $ff_config;

        //Factory::getDocument()->addScript(Juri::root(true) . '/components/com_breezingforms/libraries/codemirror/mode/htmlmixed/htmlmixed.js');

        ToolbarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_SAVE'), false);
        ToolbarHelper::custom('cancel', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE_CLOSE'), false);
        ?>
        <script type="text/javascript" src="<?php echo $ff_admsite; ?>/admin/areautils.js"></script>
        <script type="text/javascript">
            <!--
        <?php
        $jquery = "
        jQuery(document).ready(function(){
                
            setInterval(function(){
                    
                let bfScript1CodeVisible = false;
                let bfScript2CodeVisible = false;
                let bfPiece1CodeVisible = false;
                let bfPiece2CodeVisible = false;
                let bfPiece3CodeVisible = false;
                let bfPiece4CodeVisible = false;
                        
                if(!bfPiece1CodeVisible && jQuery('#bfPiece1Code').is(':visible')){
                    bfPiece1CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['piece1code'].refresh();
                }else if(bfPiece1CodeVisible && jQuery('#bfPiece1Code').is(':hidden')){
                    bfPiece1CodeVisible = false;
                }
                        
                if(!bfPiece2CodeVisible && jQuery('#bfPiece2Code').is(':visible')){
                    bfPiece2CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['piece2code'].refresh();
                }else if(bfPiece2CodeVisible && jQuery('#bfPiece2Code').is(':hidden')){
                    bfPiece2CodeVisible = false;
                }
                        
                if(!bfPiece3CodeVisible && jQuery('#bfPiece3Code').is(':visible')){
                    bfPiece3CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['piece3code'].refresh();
                }else if(bfPiece3CodeVisible && jQuery('#bfPiece3Code').is(':hidden')){
                    bfPiece3CodeVisible = false;
                }
                        
                if(!bfPiece4CodeVisible && jQuery('#bfPiece4Code').is(':visible')){
                    bfPiece4CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['piece4code'].refresh();
                }else if(bfPiece4CodeVisible && jQuery('#bfPiece4Code').is(':hidden')){
                    bfPiece4CodeVisible = false;
                }
                        
                if(!bfScript1CodeVisible && jQuery('#bfScript1Code').is(':visible')){
                    bfScript1CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['script1code'].refresh();
                }else if(bfScript1CodeVisible && jQuery('#bfScript1Code').is(':hidden')){
                    bfScript1CodeVisible = false;
                }
                        
                if(!bfScript2CodeVisible && jQuery('#bfScript2Code').is(':visible')){
                    bfScript2CodeVisible = true;
                    // XDA-GIL - 20240112 - refresh does not exit with CodeMirror v6.  
                    // Joomla.editors.instances['script2code'].refresh();
                }else if(bfScript1CodeVisible && jQuery('#bfScript2Code').is(':hidden')){
                    bfScript2CodeVisible = false;
                }
                    
            }, 100);
                    
            let bfTabInterval = setInterval(function(){
                if(jQuery('#bfTab ul li').is(':visible')){
                    clearInterval(bfTabInterval);
                    jQuery('#bfTab ul li a[aria-selected=\"true\"]').closest('li').addClass('bfTabSelected');
                    jQuery('#bfTab ul li').on('click', function(){
                        jQuery('#bfTab ul li').removeClass('bfTabSelected');
                        jQuery(this).addClass('bfTabSelected');
                    });
                }
            }, 100);
        
            jQuery('joomla-toolbar-button').on('click', function (e) {

                e.preventDefault();

                let pressbutton = jQuery(this).attr('task');

                var form = document.adminForm;
                var error = '';
                if (pressbutton != 'cancel') {
                    if (form.title.value == '')
                        error += " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_TITLEEMPTY')) . ";
                    error += checkIdentifier(
                        form.name.value,
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_NAMEEMPTY')) . ",
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_NAMEIDENT')) . "\n
                    );
                    error += checkNumber(
                        form.width.value,
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_WIDTHEMPTY')) . ",
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_WIDTHNUMBER')) . "\n
                    );
                    error += checkNumber(
                        form.height.value,
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_HEIGHTEMPTY')) . ",
                        " . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_HEIGHTNUMBER')) . "\n
                    );
                } // if
                if (error != '')
                    alert(error);
                else {
                    document.adminForm.task.value = pressbutton;
                    document.adminForm.submit();
                }
            });
        
        });
    ";

        Factory::getDocument()->addScriptDeclaration($jquery);
        ?>

            function checkNumber(value, msg1, msg2) {
                var nonDigits = /\D/;
                var error = '';
                if (value == '')
                    error += msg1;
                else
                    if (nonDigits.test(value))
                        error += msg2;
                return error;
            } // checkNumber

            function checkIdentifier(value, msg1, msg2) {
                var invalidChars = /\W/;
                var error = '';
                if (value == '')
                    error += msg1;
                else
                    if (invalidChars.test(value))
                        error += msg2;
                return error;
            } // checkIdentifier

            function createInitCode() {
                form = document.adminForm;
                name = form.name.value;
                if (name == '') {
                    alert("<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ENTNAMEFIRST'); ?>");
                    return;
                } // if
                if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CREATEINITNOW'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_EXISTINGAPPENDED'); ?>"))
                    return;
                code =
                    "function ff_" + name + "_init()\n" +
                    "{\n" +
                    "} // ff_" + name + "_init\n";
                oldcode = Joomla.editors.instances["script1code"].getValue();
                if (oldcode != '')
                    Joomla.editors.instances["script1code"].setValue(
                        code +
                        "\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_OLDCODEBELOW'); ?> --------------\n\n" +
                        oldcode);
                else
                    Joomla.editors.instances["script1code"].setValue(code);
                //codeAreaChange(form.script1code);
            } // createInitCode

            function createSubmittedCode() {
                form = document.adminForm;
                name = form.name.value;
                if (name == '') {
                    alert("<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ENTNAMEFIRST'); ?>");
                    return;
                } // if
                if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CREATESUBMITTEDNOW'); ?>\n<?php echo BFText::_('COM_BREEZINGFORMS_FORMS_EXISTINGAPPENDED'); ?>"))
                    return;
                code =
                    "function ff_" + name + "_submitted(status, message)\n" +
                    "{\n" +
                    "    switch (status) {\n" +
                    "        case FF_STATUS_OK:\n" +
                    "           // do whatever desired on success\n" +
                    "           break;\n" +
                    "        case FF_STATUS_UNPUBLISHED:\n" +
                    "        case FF_STATUS_SAVERECORD_FAILED:\n" +
                    "        case FF_STATUS_SAVESUBRECORD_FAILED:\n" +
                    "        case FF_STATUS_UPLOAD_FAILED:\n" +
                    "        case FF_STATUS_ATTACHMENT_FAILED:\n" +
                    "        case FF_STATUS_SENDMAIL_FAILED:\n" +
                    "        default:\n" +
                    "           alert(message);\n" +
                    "    } // switch\n" +
                    "} // ff_" + name + "_submitted\n";
                oldcode = Joomla.editors.instances['script2code'].getValue();
                if (oldcode != '')
                    Joomla.editors.instances['script2code'].setValue(
                        code +
                        "\n// -------------- <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_OLDCODEBELOW'); ?> --------------\n\n" +
                        oldcode);
                else
                    Joomla.editors.instances['script2code'].setValue(code);
                //codeAreaChange(form.script2code);
            } // createSubmittedCode

            function dispheight(value) {
                switch (value) {
                    case '0':
                        document.getElementById('heightmargin').style.display = 'none';
                        break;
                    default:
                        document.getElementById('heightmargin').style.display = '';
                } // switch
            } // dispheight

            function dispprevwidth() {
                var form = document.adminForm;
                if (form.widthmode.value == '0' || form.prevmode.value == '0')
                    document.getElementById('prevwidthvalue').style.display = 'none';
                else
                    document.getElementById('prevwidthvalue').style.display = '';
            } // dispprevwidth

            function dispinit(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('initlib').style.display = '';
                            document.getElementById('initcode').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('initlib').style.display = 'none';
                            document.getElementById('initcode').style.display = '';
                            break;
                        default:
                            document.getElementById('initlib').style.display = 'none';
                            document.getElementById('initcode').style.display = 'none';
                    } // switch
                } // if
            } // dispinit

            function dispsubmitted(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('submittedlib').style.display = '';
                            document.getElementById('submittedcode').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('submittedlib').style.display = 'none';
                            document.getElementById('submittedcode').style.display = '';
                            break;
                        default:
                            document.getElementById('submittedlib').style.display = 'none';
                            document.getElementById('submittedcode').style.display = 'none';
                    } // switch
                } // if
            } // dispsubmitted

            function dispemail(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '0':
                            document.getElementById('emaillogging').style.display = 'none';
                            document.getElementById('emailattachment').style.display = 'none';
                            document.getElementById('emailaddress').style.display = 'none';
                            break;
                        case '1':
                            document.getElementById('emaillogging').style.display = '';
                            document.getElementById('emailattachment').style.display = '';
                            document.getElementById('emailaddress').style.display = 'none';
                            break;
                        default:
                            document.getElementById('emaillogging').style.display = '';
                            document.getElementById('emailattachment').style.display = '';
                            document.getElementById('emailaddress').style.display = '';
                    } // switch
                } // if
            } // dispemail

            function dispp1(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('p1lib').style.display = '';
                            document.getElementById('p1code').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('p1lib').style.display = 'none';
                            document.getElementById('p1code').style.display = '';
                            break;
                        default:
                            document.getElementById('p1lib').style.display = 'none';
                            document.getElementById('p1code').style.display = 'none';
                    } // switch
                } // if
            } // dispp1

            function dispp2(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('p2lib').style.display = '';
                            document.getElementById('p2code').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('p2lib').style.display = 'none';
                            document.getElementById('p2code').style.display = '';
                            break;
                        default:
                            document.getElementById('p2lib').style.display = 'none';
                            document.getElementById('p2code').style.display = 'none';
                    } // switch
                } // if
            } // dispp2

            function dispp3(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('p3lib').style.display = '';
                            document.getElementById('p3code').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('p3lib').style.display = 'none';
                            document.getElementById('p3code').style.display = '';
                            break;
                        default:
                            document.getElementById('p3lib').style.display = 'none';
                            document.getElementById('p3code').style.display = 'none';
                    } // switch
                } // if
            } // dispp3

            function dispp4(value) {
                if (document.getElementById) {
                    switch (value) {
                        case '1':
                            document.getElementById('p4lib').style.display = '';
                            document.getElementById('p4code').style.display = 'none';
                            break;
                        case '2':
                            document.getElementById('p4lib').style.display = 'none';
                            document.getElementById('p4code').style.display = '';
                            break;
                        default:
                            document.getElementById('p4lib').style.display = 'none';
                            document.getElementById('p4code').style.display = 'none';
                    } // switch
                } // if
            } // dispp4

            onload = function () {
                <?php
                if ($row->script1cond != 0)
                    echo "\t\t\tdispinit('" . $row->script1cond . "');\n";
                if ($row->script2cond != 0)
                    echo "\t\t\tdispsubmitted('" . $row->script2cond . "');\n";
                if ($row->piece1cond != 0)
                    echo "\t\t\tdispp1('" . $row->piece1cond . "');\n";
                if ($row->piece2cond != 0)
                    echo "\t\t\tdispp2('" . $row->piece2cond . "');\n";
                if ($row->piece3cond != 0)
                    echo "\t\t\tdispp3('" . $row->piece3cond . "');\n";
                if ($row->piece4cond != 0)
                    echo "\t\t\tdispp4('" . $row->piece4cond . "');\n";
                switch ($tabpane) {
                    case 1:
                    case 2:
                    case 3:
                        echo "\t\t\ttabPane1.setSelectedIndex($tabpane);\n";
                        break;
                    default:
                        echo "\t\t\tdocument.adminForm.title.focus();\n";
                } // switch
                ?>
            } // onload
            //-->
        </script>
        <fieldset>
            <legend>
                <?php echo Text::_('COM_BREEZINGFORMS_FORMSETUP'); ?>:
                <?php echo htmlentities($row->title, ENT_QUOTES, 'UTF-8') ?>
            </legend>

            <div style="clear:both;"></div>
            <form action="index.php?format=html" method="post" name="adminForm" id="adminForm" class="adminForm"
                onsubmit="return false;">
                <table cellpadding="4" cellspacing="1" border="0" style="width: 100%;" width="100%">
                    <tr>
                        <td></td>
                        <td width="100%">
                            <?php
                            echo HTMLHelper::_('uitab.startTabSet', 'bfTab', array('active' => 'tab_settings'));

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_settings', BFText::_('COM_BREEZINGFORMS_FORMS_SETTINGS'));
                            ?>

                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SETTINGS'); ?>
                                </legend>
                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td style="width: 200px;" valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TITLE'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="50" name="title"
                                                value="<?php echo $row->title; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PACKAGE'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="30" id="package" name="package"
                                                value="<?php echo $row->package; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NAME'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="30" name="name"
                                                value="<?php echo $row->name; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;div&gt;
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="30" name="class1"
                                                value="<?php echo $row->class1; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CLASSFOR'); ?> &lt;form&gt;
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="30" name="class2"
                                                value="<?php echo $row->class2; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ORDERING'); ?>
                                        </td>
                                        <td valign="top">
                                            <?php echo $lists['ordering']; ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PUBLISHED'); ?>
                                        </td>
                                        <td valign="top">
                                            <?php echo HTMLHelper::_('select.booleanlist', "published", "", $row->published); ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_RUNMODE'); ?>
                                        </td>
                                        <td valign="top">
                                            <select name="runmode" size="1" class="inputbox">
                                                <option value="0" <?php if ($row->runmode == 0)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ANY'); ?>
                                                </option>
                                                <option value="1" <?php if ($row->runmode == 1)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_FRONTEND'); ?>
                                                </option>
                                                <option value="2" <?php if ($row->runmode == 2)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_BACKEND'); ?>
                                                </option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_WIDTH'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="6" maxlength="6" name="width"
                                                value="<?php echo $row->width; ?>" class="inputbox" />
                                            <br />
                                            <select name="widthmode" size="1" onchange="dispprevwidth();" class="inputbox">
                                                <option value="0" <?php if ($row->widthmode == 0)
                                                    echo ' selected="selected"'; ?>>
                                                    px</option>
                                                <option value="1" <?php if ($row->widthmode == 1)
                                                    echo ' selected="selected"'; ?>>
                                                    %</option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_HEIGHT'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="6" maxlength="6" name="height"
                                                value="<?php echo $row->height; ?>" class="inputbox" /> px
                                            <?php
                                            if ($row->template_code_processed != 'QuickMode') {
                                                ?>
                                                <br />
                                                <select name="heightmode" size="1" onchange="dispheight(this.value);"
                                                    class="inputbox">
                                                    <option value="0" <?php if ($row->heightmode == 0)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_FIXED'); ?>
                                                    </option>
                                                    <option value="1" <?php if ($row->heightmode == 1)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_AUTO'); ?>
                                                    </option>
                                                    <option value="2" <?php if ($row->heightmode == 2)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_AUTOMAX'); ?>
                                                    </option>
                                                </select><span id="heightmargin" <?php if ($row->heightmode == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                </span>
                                                <?php
                                            } else {
                                                ?>
                                                <input type="hidden" name="heightmode" value="0" />
                                                <?php
                                            }
                                            ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <?php
                                    if ($row->template_code_processed == 'QuickMode' || $row->template_code != '') {
                                        ?>
                                        <tr>
                                            <td valign="top">
                                                <?php echo BFText::_('COM_BREEZINGFORMS_AUTOHEIGHT'); ?>
                                            </td>
                                            <td valign="top">
                                                <select name="autoheight" class="inputbox">
                                                    <option value="0" <?php if ($row->autoheight == 0)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_AUTOHEIGHT_OFF'); ?>
                                                    </option>
                                                    <option value="1" <?php if ($row->autoheight == 1)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_AUTOHEIGHT_ON'); ?>
                                                    </option>
                                                </select>
                                                <br />
                                                <i>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_AUTOHEIGHT_INFO'); ?>
                                                </i>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <?php
                                    if ($row->template_code == '') {
                                        ?>
                                        <tr>
                                            <td valign="top">
                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PREVMODE'); ?>
                                            </td>
                                            <td valign="top">
                                                <select name="prevmode" size="1" onchange="dispprevwidth();" class="inputbox">
                                                    <option value="0" <?php if ($row->prevmode == 0)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                    </option>
                                                    <option value="1" <?php if ($row->prevmode == 1)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_BELOW'); ?>
                                                    </option>
                                                    <option value="2" <?php if ($row->prevmode == 2)
                                                        echo ' selected="selected"'; ?>>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_OVERLAYED'); ?>
                                                    </option>
                                                </select>
                                                <span id="prevwidthvalue" <?php if ($row->widthmode == 0 || $row->prevmode == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_WIDTH'); ?>: <input size="6"
                                                        maxlength="6" name="prevwidth" value="<?php echo $row->prevwidth; ?>"
                                                        class="inputbox" /> px
                                                </span>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LOGTODB'); ?>
                                        </td>
                                        <td valign="top">
                                            <select name="dblog" size="1" class="inputbox">
                                                <option value="0" <?php if ($row->dblog == 0)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NO'); ?>
                                                </option>
                                                <option value="1" <?php if ($row->dblog == 1)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONEMPTY'); ?>
                                                </option>
                                                <option value="2" <?php if ($row->dblog == 2)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ALLVALS'); ?>
                                                </option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_DOUBLE_OPT'); ?>
                                        </td>
                                        <td valign="top">
                                            <?php echo HTMLHelper::_('select.booleanlist', "double_opt", "", $row->double_opt); ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_OPT_IN_MAIL_NAME'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" size="30" maxlength="30" name="opt_mail"
                                                value="<?php echo $row->opt_mail; ?>" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>


                                    <tr>
                                        <td colspan="2" valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_DESCRIPTION'); ?>
                                            <a href="javascript:void(0);"
                                                onClick="textAreaResize('description',<?php echo $ff_config->areasmall; ?>);">[
                                                <?php echo $ff_config->areasmall; ?>]
                                            </a>
                                            <a href="javascript:void(0);"
                                                onClick="textAreaResize('description',<?php echo $ff_config->areamedium; ?>);">[
                                                <?php echo $ff_config->areamedium; ?>]
                                            </a>
                                            <a href="javascript:void(0);"
                                                onClick="textAreaResize('description',<?php echo $ff_config->arealarge; ?>);">[
                                                <?php echo $ff_config->arealarge; ?>]
                                            </a>
                                            <br />
                                            <textarea wrap="off" name="description" style="width:100%;"
                                                rows="<?php echo $ff_config->areasmall; ?>"
                                                class="inputbox"><?php echo htmlspecialchars($row->description, ENT_QUOTES); ?></textarea>
                                        </td>
                                        <td></td>
                                    </tr>

                                </table>
                            </fieldset>

                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_admin_emails', BFText::_('COM_BREEZINGFORMS_ADMIN_EMAILS'));
                            ?>

                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_ADMIN_EMAILS'); ?>
                                </legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>

                                        <td style="width: 200px;" valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_EMAILNOTIFY'); ?>
                                        </td>
                                        <td valign="top">
                                            <select style="width: 335px;" name="emailntf" size="1"
                                                onchange="dispemail(this.value);"
                                                class="inp                                                    utbox">
                                                <option value="0" <?php if ($row->emailntf == 0)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NO'); ?>
                                                </option>
                                                <option value="1" <?php if ($row->emailntf == 1)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_DEFADDR'); ?>
                                                </option>
                                                <option value="2" <?php if ($row->emailntf == 2)
                                                    echo ' selected="selected"'; ?>>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTADDR'); ?>
                                                </option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td valign="top"></td>
                                        <td valign="top">
                                            <table cellpadding="4" cellspacing="4" border="0">
                                                <tr id="emailaddress" <?php if ($row->emailntf != 2)
                                                    echo ' style="display:none;"'; ?>>
                                                    <td>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_EMAIL'); ?>
                                                    </td>
                                                    <td><input type="text" size="30" name="emailadr"
                                                            value="<?php echo $row->emailadr; ?>" class="inputbox" /></td>
                                                </tr>
                                                <tr id="emaillogging" <?php if ($row->emailntf == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                    <td>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_REPORT'); ?>
                                                    </td>
                                                    <td>
                                                        <select name="emaillog" size="1" class="inputbox">
                                                            <option value="0" <?php if ($row->emaillog == 0)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_HDRONLY'); ?>
                                                            </option>
                                                            <option value="1" <?php if ($row->emaillog == 1)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONEMPTY'); ?>
                                                            </option>
                                                            <option value="2" <?php if ($row->emaillog == 2)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ALLVALS'); ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr id="emailattachment" <?php if ($row->emailntf == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                    <td>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ATTACHMENT'); ?>
                                                    </td>
                                                    <td>
                                                        <select name="emailxml" size="1" class="inputbox">
                                                            <option value="0" <?php if ($row->emailxml == 0)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NO'); ?>
                                                            </option>
                                                            <option value="1" <?php if ($row->emailxml == 1)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_XML'); ?>
                                                            </option>
                                                            <!--<option value="2"<?php if ($row->emailxml == 2)
                                                                echo ' selected="selected"'; ?>><?php echo BFText::_('COM_BREEZINGFORMS_FORMS_XML_ALLVALS'); ?></option>-->
                                                            <option value="3" <?php if ($row->emailxml == 3)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_CSV'); ?>
                                                            </option>
                                                            <option value="4" <?php if ($row->emailxml == 4)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_PDF'); ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_ALT_MAILFROM'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" name="alt_mailfrom" value="<?php echo $row->alt_mailfrom; ?>"
                                                size="50" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_ALT_FROMNAME'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" name="alt_fromname" value="<?php echo $row->alt_fromname; ?>"
                                                size="50" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM_MAIL_SUBJECT'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="text" name="custom_mail_subject"
                                                value="<?php echo $row->custom_mail_subject; ?>" size="50" class="inputbox" />
                                        </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <td valign="top" nowrap>
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_EMAILS'); ?>
                                            <br />
                                            <br />
                                            <div style="height: 250px; overflow: auto;<?php echo $row->email_type == 0 ? ' display: none;' : '' ?>"
                                                id="email_custom_template_picker">
                                                <?php echo bf_getFieldSelectorListEditor($row->id, 'email_custom_template'); ?>
                                            </div>
                                        </td>
                                        <td valign="top">
                                            <input
                                                onclick="document.getElementById('email_custom_template_div').style.display = 'none';document.getElementById('email_custom_template_picker').style.display = 'none';"
                                                type="radio" name="email_type" value="0" <?php echo $row->email_type == 0 ? ' checked="checked"' : '' ?> />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_TYPE_DEFAULT'); ?>
                                            <input
                                                onclick="document.getElementById('email_custom_template_div').style.display = '';
                                                            document.getElementById('email_custom_template_picker').style.display = '';"
                                                type="radio" name="email_type" value="1" <?php echo $row->email_type == 1 ? ' checked="checked"' : '' ?> />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_TYPE_CUSTOM'); ?>
                                            <div id="email_custom_html" style="display: none;">
                                                <br />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_CUSTOM_HTML'); ?>
                                                <input type="radio" name="email_custom_html" value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                <input type="radio" name="email_custom_html" value="1" checked="checked" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                            </div>
                                            <br />
                                            <div id="email_custom_template_div" <?php echo $row->email_type == 0 ? 'style="display: none;"' : '' ?>>
                                                <p>If you want to use HTML in mail, please enter it through Tools &gt; Source
                                                    Code in Editor</p>
                                                <?php
                                                $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
                                                echo $editor->display('email_custom_template', htmlentities($row->email_custom_template, ENT_QUOTES, 'UTF-8'), '100%', '500px', '20', '20', true, 'email_custom_template', null, null, array());
                                                ?>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>
                            </fieldset>

                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_mailback_emails', BFText::_('COM_BREEZINGFORMS_MAILBACK_EMAILS'));
                            ?>
                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_MAILBACK_EMAILS'); ?>
                                </legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td style="width: 200px;" valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_EMAILNOTIFY'); ?>
                                        </td>

                                        <td valign="top">
                                            <table cellpadding="4" cellspacing="1" border="0">
                                                <tr id="bf_emaillogging" <?php if ($row->mb_emailntf == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                    <td>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_REPORT'); ?>
                                                    </td>
                                                    <td>
                                                        <select name="mb_emaillog" size="1" class="inputbox">
                                                            <option value="0" <?php if ($row->mb_emaillog == 0)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_HDRONLY'); ?>
                                                            </option>
                                                            <option value="1" <?php if ($row->mb_emaillog == 1)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONEMPTY'); ?>
                                                            </option>
                                                            <option value="2" <?php if ($row->mb_emaillog == 2)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ALLVALS'); ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr id="bf_emailattachment" <?php if ($row->mb_emailntf == 0)
                                                    echo ' style="display:none;"'; ?>>
                                                    <td>
                                                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ATTACHMENT'); ?>
                                                    </td>
                                                    <td>
                                                        <select name="mb_emailxml" size="1" class="inputbox">
                                                            <option value="0" <?php if ($row->mb_emailxml == 0)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NO'); ?>
                                                            </option>
                                                            <option value="1" <?php if ($row->mb_emailxml == 1)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_XML'); ?>
                                                            </option>
                                                            <!--<option value="2"<?php if ($row->mb_emailxml == 2)
                                                                echo ' selected="selected"'; ?>><?php echo BFText::_('COM_BREEZINGFORMS_FORMS_XML_ALLVALS'); ?></option>-->
                                                            <option value="3" <?php if ($row->mb_emailxml == 3)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_CSV'); ?>
                                                            </option>
                                                            <option value="4" <?php if ($row->mb_emailxml == 4)
                                                                echo ' selected="selected"'; ?>>
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_PDF'); ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td valign="top"></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_ALT_MAILFROM'); ?>
                                        </td>
                                        <td>
                                            <input type="text" name="mb_alt_mailfrom"
                                                value="<?php echo $row->mb_alt_mailfrom; ?>" size="50" class="inputbox" />
                                        </td>
                                        <td valign="top"></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_ALT_FROMNAME'); ?>
                                        </td>
                                        <td>
                                            <input type="text" name="mb_alt_fromname"
                                                value="<?php echo $row->mb_alt_fromname; ?>" size="50" class="inputbox" />
                                        </td>
                                        <td valign="top"></td>
                                    </tr>
                                    <tr>

                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_CUSTOM_MAIL_SUBJECT'); ?>
                                        </td>
                                        <td>
                                            <input type="text" name="mb_custom_mail_subject"
                                                value="<?php echo $row->mb_custom_mail_subject; ?>" size="50"
                                                class="inputbox" />
                                        </td>
                                        <td valign="top"></td>
                                    </tr>

                                    <tr>
                                        <td valign="top" nowrap>
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EDIT_EMAILS'); ?>
                                            <br />
                                            <br />
                                            <div style="height: 250px; overflow: auto;<?php echo $row->mb_email_type == 0 ? ' display: none;' : '' ?>"
                                                id="mb_email_custom_template_picker">
                                                <?php echo bf_getFieldSelectorListEditor($row->id, 'mb_email_custom_template'); ?>
                                            </div>
                                        </td>
                                        <td valign="top">
                                            <input
                                                onclick="document.getElementById('mb_email_custom_template_div').style.display = 'none';
                                                            document.getElementById('mb_email_custom_template_picker').style.display = 'none';"
                                                type="radio" name="mb_email_type" value="0" <?php echo $row->mb_email_type == 0 ? ' checked="checked"' : '' ?> />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_TYPE_DEFAULT'); ?>
                                            <input
                                                onclick="document.getElementById('mb_email_custom_template_div').style.display = '';
                                                            document.getElementById('mb_email_custom_template_picker').style.display = '';"
                                                type="radio" name="mb_email_type" value="1" <?php echo $row->mb_email_type == 1 ? ' checked="checked"' : '' ?> />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_TYPE_CUSTOM'); ?>
                                            <div id="mb_email_custom_html" style="display: none;">
                                                <br />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_CUSTOM_HTML'); ?>
                                                <input type="radio" name="mb_email_custom_html" value="0" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                                <input type="radio" name="mb_email_custom_html" value="1" checked="checked" />
                                                <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?>
                                            </div>
                                            <br />
                                            <div id="mb_email_custom_template_div" <?php echo $row->mb_email_type == 0 ? 'style="display: none;"' : '' ?>>
                                                <p>If you want to use HTML in mail, please enter it through Tools &gt; Source
                                                    Code in Editor</p>
                                                <?php
                                                $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
                                                echo $editor->display('mb_email_custom_template', htmlentities($row->mb_email_custom_template, ENT_QUOTES, 'UTF-8'), '100%', '500px', '20', '20', true, 'mb_email_custom_template', null, null, array());
                                                ?>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>

                                </table>
                            </fieldset>

                            <?php
                            if ($row->template_code != '') {
                                ?>
                                <input type="hidden" name="prevmode" value="2" />
                                <input type="hidden" name="nonclassic" value="1" />
                                <input type="hidden" name="quickmode"
                                    value="<?php echo $row->template_code_processed == 'QuickMode' ? '1' : '0' ?>" />
                                <?php
                            }

                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_scripts', BFText::_('COM_BREEZINGFORMS_FORMS_SCRIPTS'));

                            $subsize = $initsize = $ff_config->areasmall;
                            if ($row->script1cond == 2)
                                $initsize = $ff_config->areamedium;
                            else
                                if ($row->script2cond == 2)
                                    $subsize = $ff_config->areamedium;
                            ?>
                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SCRIPTS'); ?>
                                </legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td valign="top"></td>
                                        <td valign="top">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_INITSCRIPT'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="script1cond1" name="script1cond" value="0"
                                                                onclick="dispinit(this.value)" <?php if ($row->script1cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label for="script1cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="script1cond2" name="script1cond" value="1"
                                                                onclick="dispinit(this.value)" <?php if ($row->script1cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label for="script1cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="script1cond3" name="script1cond" value="2"
                                                                onclick="dispinit(this.value)" <?php if ($row->script1cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label for="script1cond3">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="initlib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SCRIPT'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="script1id" class="inputbox">
                                                                <?php
                                                                $scripts = $lists['init'];
                                                                for ($i = 0; $i < count($scripts); $i++) {
                                                                    $script = $scripts[$i];
                                                                    $selected = '';
                                                                    if ($script->id == $row->script1id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $script->id . '"' . $selected . '>' . $script->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="initcode" style="display:none;">
                                                        <td valign="top" colspan="2">
                                                            <a href="javascript:void(0);" onClick="createInitCode();">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CREATEFRAME'); ?>
                                                            </a>
                                                            <div id="bfScript1Code">
                                                                <?php
                                                                $params = array('syntax' => 'javascript');
                                                                $editor = Editor::getInstance('codemirror');
                                                                // XDA-GIL 
                                                                echo $editor->display('script1code', $row->script1code, '100%', 450, 40, 20, false, 'script1code', null, null, $params);
                                                                ?>
                                                            </div>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SUBMITTEDSCRIPT'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="script2cond1" name="script2cond" value="0"
                                                                onclick="dispsubmitted(this.value)" <?php if ($row->script2cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label
                                                                for="script2cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="script2cond2" name="script2cond" value="1"
                                                                onclick="dispsubmitted(this.value)" <?php if ($row->script2cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label
                                                                for="script2cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="script2cond3" name="script2cond" value="2"
                                                                onclick="dispsubmitted(this.value)" <?php if ($row->script2cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label
                                                                for="script2cond3">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="submittedlib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SCRIPT'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="script2id" class="inputbox" size="1">
                                                                <?php
                                                                $scripts = $lists['submitted'];
                                                                for ($i = 0; $i < count($scripts); $i++) {
                                                                    $script = $scripts[$i];
                                                                    $selected = '';
                                                                    if ($script->id == $row->script2id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $script->id . '"' . $selected . '>' . $script->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="submittedcode" style="display:none;">
                                                        <td valign="top" colspan="2">
                                                            <a href="javascript:void(0);" onClick="createSubmittedCode();">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CREATEFRAME'); ?>
                                                            </a>
                                                            <div id="bfScript2Code">
                                                                <?php
                                                                $params = array('syntax' => 'javascript');
                                                                $editor = Editor::getInstance('codemirror');
                                                                echo $editor->display('script2code', $row->script2code, '100%', 450, 40, 20, false, 'script2code', null, null, $params);
                                                                ?>
                                                            </div>
                                                        </td>
                                                        <td valign="top"></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>
                            </fieldset>
                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'ptab_formpieces', BFText::_('COM_BREEZINGFORMS_FORMS_FORMPIECES'));

                            $p1size = $p2size = $ff_config->areasmall;
                            if ($row->piece1cond == 2)
                                $p1size = $ff_config->areamedium;
                            else
                                if ($row->piece2cond == 2)
                                    $p2size = $ff_config->areamedium;
                            ?>
                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_FORMPIECES'); ?>
                                </legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_BEFOREFORM'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="piece1cond0" name="piece1cond" value="0"
                                                                onclick="dispp1(this.value)" <?php if ($row->piece1cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label for="piece1cond0">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="piece1cond1" name="piece1cond" value="1"
                                                                onclick="dispp1(this.value)" <?php if ($row->piece1cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label for="piece1cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="piece1cond2" name="piece1cond" value="2"
                                                                onclick="dispp1(this.value)" <?php if ($row->piece1cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label for="piece1cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p1lib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PIECE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="piece1id" class="inputbox" size="1">
                                                                <?php
                                                                $pieces = $lists['piece1'];
                                                                for ($i = 0; $i < count($pieces); $i++) {
                                                                    $piece = $pieces[$i];
                                                                    $selected = '';
                                                                    if ($piece->id == $row->piece1id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $piece->id . '"' . $selected . '>' . $piece->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p1code" style="display:none;">
                                                        <td valign="top" colspan="2">

                                                            <div id="bfPiece1Code">
                                                                <?php
                                                                $params = array(
                                                                    'syntax' => 'php',
                                                                );
                                                                $editor = Editor::getInstance('codemirror');
                                                                echo $editor->display('piece1code', $row->piece1code, '100%', 450, 40, 20, false, 'piece1code', null, null, $params);
                                                                ?>
                                                            </div>

                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_AFTERFORM'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="piece2cond0" name="piece2cond" value="0"
                                                                onclick="dispp2(this.value)" <?php if ($row->piece2cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label for="piece2cond0">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="piece2cond1" name="piece2cond" value="1"
                                                                onclick="dispp2(this.value)" <?php if ($row->piece2cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label for="piece2cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="piece2cond2" name="piece2cond" value="2"
                                                                onclick="dispp2(this.value)" <?php if ($row->piece2cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label for="piece2cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p2lib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PIECE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="piece2id" class="inputbox" size="1">
                                                                <?php
                                                                $pieces = $lists['piece2'];
                                                                for ($i = 0; $i < count($pieces); $i++) {
                                                                    $piece = $pieces[$i];
                                                                    $selected = '';
                                                                    if ($piece->id == $row->piece2id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $piece->id . '"' . $selected . '>' . $piece->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p2code" style="display:none;">
                                                        <td valign="top" colspan="2">

                                                            <div id="bfPiece2Code">
                                                                <?php
                                                                $params = array(
                                                                    'syntax' => 'php',
                                                                );
                                                                $editor = Editor::getInstance('codemirror');
                                                                echo $editor->display('piece2code', $row->piece2code, '100%', 450, 40, 20, false, 'piece2code', null, null, $params);
                                                                ?>
                                                            </div>

                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>
                            </fieldset>
                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_submpieces', BFText::_('COM_BREEZINGFORMS_FORMS_SUBMPIECES'));

                            $p3size = $p4size = $ff_config->areasmall;
                            if ($row->piece3cond == 2)
                                $p3size = $ff_config->areamedium;
                            else
                                if ($row->piece4cond == 2)
                                    $p4size = $ff_config->areamedium;
                            ?>
                            <fieldset>
                                <legend>
                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SUBMPIECES'); ?>
                                </legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_BEGINSUBMIT'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="piece3cond0" name="piece3cond" value="0"
                                                                onclick="dispp3(this.value)" <?php if ($row->piece3cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label for="piece3cond0">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="piece3cond1" name="piece3cond" value="1"
                                                                onclick="dispp3(this.value)" <?php if ($row->piece3cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label for="piece3cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="piece3cond2" name="piece3cond" value="2"
                                                                onclick="dispp3(this.value)" <?php if ($row->piece3cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label for="piece3cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p3lib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PIECE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="piece3id" class="inputbox" size="1">
                                                                <?php
                                                                $pieces = $lists['piece3'];
                                                                for ($i = 0; $i < count($pieces); $i++) {
                                                                    $piece = $pieces[$i];
                                                                    $selected = '';
                                                                    if ($piece->id == $row->piece3id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $piece->id . '"' . $selected . '>' . $piece->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p3code" style="display:none;">
                                                        <td valign="top" colspan="2">

                                                            <div id="bfPiece3Code">
                                                                <?php
                                                                $params = array(
                                                                    'syntax' => 'php',
                                                                );
                                                                $editor = Editor::getInstance('codemirror');
                                                                echo $editor->display('piece3code', $row->piece3code, '100%', 450, 40, 20, false, 'piece3code', null, null, $params);
                                                                ?>
                                                            </div>

                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <fieldset>
                                                <legend>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_ENDSUBMIT'); ?>
                                                </legend>
                                                <table width="100%" cellpadding="4" cellspacing="1" border="0">
                                                    <tr>
                                                        <td style="width: 200px;" valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TYPE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <input type="radio" id="piece4cond0" name="piece4cond" value="0"
                                                                onclick="dispp4(this.value)" <?php if ($row->piece4cond == 0)
                                                                    echo ' checked="checked"'; ?> /><label for="piece4cond0">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NONE'); ?>
                                                            </label>
                                                            <input type="radio" id="piece4cond1" name="piece4cond" value="1"
                                                                onclick="dispp4(this.value)" <?php if ($row->piece4cond == 1)
                                                                    echo ' checked="checked"'; ?> /><label for="piece4cond1">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_LIBRARY'); ?>
                                                            </label>
                                                            <input type="radio" id="piece4cond2" name="piece4cond" value="2"
                                                                onclick="dispp4(this.value)" <?php if ($row->piece4cond == 2)
                                                                    echo ' checked="checked"'; ?> /><label for="piece4cond2">
                                                                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_CUSTOM'); ?>
                                                            </label>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p4lib" style="display:none;">
                                                        <td valign="top">
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PIECE'); ?>
                                                        </td>
                                                        <td valign="top">
                                                            <select name="piece4id" class="inputbox" size="1">
                                                                <?php
                                                                $pieces = $lists['piece4'];
                                                                for ($i = 0; $i < count($pieces); $i++) {
                                                                    $piece = $pieces[$i];
                                                                    $selected = '';
                                                                    if ($piece->id == $row->piece4id)
                                                                        $selected = ' selected';
                                                                    echo '<option value="' . $piece->id . '"' . $selected . '>' . $piece->text . '</option>';
                                                                } // for
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr id="p4code" style="display:none;">
                                                        <td nowrap valign="top" colspan="2">

                                                            <div id="bfPiece4Code">
                                                                <?php
                                                                $params = array(
                                                                    'syntax' => 'php',
                                                                );
                                                                $editor = Editor::getInstance('codemirror');
                                                                echo $editor->display('piece4code', $row->piece4code, '100%', 450, 40, 20, false, 'piece4code', null, null, $params);
                                                                ?>
                                                            </div>

                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>
                            </fieldset>
                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_mailchimp', 'MailChimp®');
                            ?>

                            <fieldset>
                                <legend>MailChimp®</legend>
                                <table width="100%" cellpadding="4" cellspacing="1" border="0">

                                    <tr>
                                        <td valign="top" style="width: 200px;">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_API_KEY'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_api_key"
                                                value="<?php echo $row->mailchimp_api_key; ?>" size="50" class="inputbox" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_LIST_ID'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_list_id"
                                                value="<?php echo $row->mailchimp_list_id; ?>" size="50" class="inputbox" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_EMAIL_FIELD'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_email_field"
                                                value="<?php echo $row->mailchimp_email_field; ?>" size="50" class="inputbox" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_CHECKBOX_FIELD'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_checkbox_field"
                                                value="<?php echo $row->mailchimp_checkbox_field; ?>" size="50"
                                                class="inputbox" /></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_UNSUBSCRIBE_FIELD'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_unsubscribe_field"
                                                value="<?php echo $row->mailchimp_unsubscribe_field; ?>" size="50"
                                                class="inputbox" /></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_TEXT_HTML_MOBILE_FIELD'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_text_html_mobile_field"
                                                value="<?php echo $row->mailchimp_text_html_mobile_field; ?>" size="50"
                                                class="inputbox" /></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_MERGE_VARS'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="mailchimp_mergevars"
                                                value="<?php echo $row->mailchimp_mergevars; ?>" style="width:90%;"
                                                class="inputbox" /></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_DEFAULT_TYPE'); ?>
                                        </td>
                                        <td valign="top">
                                            <select name="mailchimp_default_type" class="inputbox">
                                                <option value="text" <?php echo $row->mailchimp_default_type == 'text' ? ' selected="selected"' : ''; ?>>Text</option>
                                                <option value="html" <?php echo $row->mailchimp_default_type == 'html' ? ' selected="selected"' : ''; ?>>HTML</option>
                                                <option value="mobile" <?php echo $row->mailchimp_default_type == 'mobile' ? ' selected="selected"' : ''; ?>>Mobile</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_DOUBLE_OPTIN'); ?>
                                        </td>
                                        <td valign="top"><input type="radio" name="mailchimp_double_optin" class="inputbox"
                                                <?php echo $row->mailchimp_double_optin ? ' checked="checked"' : ''; ?>
                                                value="1" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?> <input type="radio"
                                                name="mailchimp_double_optin" class="inputbox" <?php echo !$row->mailchimp_double_optin ? ' checked="checked"' : ''; ?> value="0" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_UNSUBSCRIBE_DELETE_MEMBER'); ?>
                                        </td>
                                        <td valign="top"><input type="radio" name="mailchimp_delete_member" class="inputbox"
                                                <?php echo $row->mailchimp_delete_member ? ' checked="checked"' : ''; ?>
                                                value="1" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?> <input type="radio"
                                                name="mailchimp_delete_member" class="inputbox" <?php echo !$row->mailchimp_delete_member ? ' checked="checked"' : ''; ?> value="0" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_SEND_ERRORS'); ?>
                                        </td>
                                        <td valign="top"><input type="radio" name="mailchimp_send_errors" class="inputbox" <?php echo $row->mailchimp_send_errors ? ' checked="checked"' : ''; ?> value="1" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_YES'); ?> <input type="radio"
                                                name="mailchimp_send_errors" class="inputbox" <?php echo !$row->mailchimp_send_errors ? ' checked="checked"' : ''; ?> value="0" />
                                            <?php echo BFText::_('COM_BREEZINGFORMS_NO'); ?>
                                        </td>
                                    </tr>

                                </table>
                            </fieldset>

                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_salesforce', 'Salesforce®');
                            ?>

                            <fieldset>
                                <legend>Salesforce®</legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">

                                    <?php
                                    if ($row->salesforce_error) {
                                        ?>
                                        <tr>
                                            <td style="width: 200px;" valign="top">
                                                <?php echo BFText::_('COM_BREEZINGFORMS_SF_ERROR'); ?>
                                            </td>
                                            <td style="color: red;" valign="top">
                                                <?php echo $row->salesforce_error; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                    <tr>
                                        <td style="width: 200px;" valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_SF_ENABLED'); ?>
                                        </td>
                                        <td valign="top">
                                            <input type="checkbox"
                                                onclick="document.getElementById('salesforce_flag').value = 1;"
                                                name="salesforce_enabled" value="1" size="50" class="inputbox" <?php echo $row->salesforce_enabled == 1 ? ' checked="checked"' : ''; ?> />
                                            <input type="hidden" name="salesforce_flag" id="salesforce_flag" value="0" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_SF_TOKEN'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="salesforce_token"
                                                value="<?php echo $row->salesforce_token; ?>" size="50" class="inputbox" /></td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_SF_USERNAME'); ?>
                                        </td>
                                        <td valign="top"><input type="text" name="salesforce_username"
                                                value="<?php echo $row->salesforce_username; ?>" size="50" class="inputbox" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top">
                                            <?php echo BFText::_('COM_BREEZINGFORMS_SF_PASSWORD'); ?>
                                        </td>
                                        <td valign="top"><input type="password" name="salesforce_password"
                                                value="<?php echo $row->salesforce_password; ?>" size="50" class="inputbox" />
                                        </td>
                                    </tr>

                                    <?php
                                    if (count($row->salesforce_types) != 0) {
                                        ?>
                                        <tr>
                                            <td valign="top">
                                                <?php echo BFText::_('COM_BREEZINGFORMS_SF_TYPE'); ?>
                                            </td>
                                            <td valign="top">
                                                <select name="salesforce_type">
                                                    <?php
                                                    foreach ($row->salesforce_types as $stype) {
                                                        ?>
                                                        <option value="<?php echo $stype->name; ?>" <?php echo $stype->name == $row->salesforce_type ? ' selected="selected"' : '' ?>>
                                                            <?php echo $stype->label; ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top"><b>
                                                    <?php echo BFText::_('COM_BREEZINGFORMS_SF_FIELDS'); ?>
                                                </b></td>
                                            <td valign="top"></td>
                                        </tr>
                                        <?php
                                        foreach ($row->breezingforms_fields as $bfField) {
                                            ?>
                                            <tr>
                                                <td valign="top">
                                                    <?php echo $bfField->title; ?> (
                                                    <?php echo $bfField->name ?>)
                                                </td>
                                                <td valign="top">
                                                    <select name="salesforce_fields[]">
                                                        <option value=""> -
                                                            <?php echo BFText::_('COM_BREEZINGFORMS_SF_UNUSED'); ?> -
                                                        </option>
                                                        <?php
                                                        foreach ($row->salesforce_type_fields as $stypefields) {
                                                            ?>
                                                            <option value="<?php echo $bfField->name ?>::<?php echo $stypefields->name; ?>"
                                                                <?php echo in_array($bfField->name . '::' . $stypefields->name, $row->salesforce_fields) ? ' selected="selected"' : '' ?>>
                                                                <?php echo $stypefields->label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <?php
                                    }
                                    ?>

                                </table>
                            </fieldset>

                            <?php
                            echo HTMLHelper::_('uitab.endTab');

                            echo HTMLHelper::_('uitab.addTab', 'bfTab', 'tab_dropbox', 'Dropbox®');
                            $failed = false;
                            ?>

                            <fieldset>
                                <legend>Dropbox®</legend>
                                <table width="80%" cellpadding="4" cellspacing="1" border="0">
                                    <tr>
                                        <td style="width: 200px;" valign="top" colspan="2">
                                            <?php
                                            try {
                                                if (!$failed) {

                                                    if ($row->dropbox_password == '' && $row->dropbox_email == '') {
                                                        $authorizeUrl = 'https://www.dropbox.com/oauth2/authorize?client_id=wfqreycemugothg&response_type=code';
                                                        echo "<h3>Dropbox uses a secure way to connect with other apps like BreezingForms, please follow the steps below to connect this form with Dropbox</h3><br/>";
                                                        echo "1. Open this Dropbox link: <a target=\"_blank\" href=\"" . $authorizeUrl . "\">Dropbox</a><br/><i>(you need to be logged into Dropbox)</i><br/><br/>\n";
                                                        echo "2. Click \"Allow\".<br/><br/>\n";
                                                        echo "3. Copy the authorization below into 'Authentication Code' and save.<br/><br/>\n";
                                                        echo "4. After you finished the connection, your Dropbox instance is active and the Access Token field appears, displaying your personal token. You don't need to do anything further with it. If you need to reset the entire process, use the reset option, save and try again. Otherwise you are ready to use BreezingForms &amp; Dropbox.<br/><br/>\n";
                                                    } else if ($row->dropbox_password != '' && $row->dropbox_email == '') {

                                                        require_once JPATH_SITE . '/administrator/components/com_breezingforms/libraries/dropbox/v2/autoload.php';
                                                        $auth = new \Alorel\Dropbox\Operation\Users\GetAuthorizeToken(null, 'NoTokenOperation');
                                                        $db_response = $auth->raw($row->dropbox_password);

                                                        $accessToken = '';
                                                        if (!isset($db_response->access_token)) {
                                                            echo '<i style="color:red;">An error occured, no access token available.</i>';
                                                        } else {
                                                            echo '<i style="color:red;">Now please save again to store your personal Access Token and you can start using BreezingForms &amp; Dropbox!</i>';
                                                            $accessToken = $db_response->access_token;
                                                        }
                                                        $row->dropbox_password = '';
                                                        $row->dropbox_email = $accessToken;
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                echo 'Something went wrong with Dropbox. Please save the page once and try again.<br />';
                                                echo 'Message: ' . $e->getMessage();
                                                $row->dropbox_password = '';
                                                $row->dropbox_email = '';
                                            }
                                            ?>
                                        </td>
                                    </tr>

                                    <tr<?php echo $row->dropbox_email == '' ? ' style="display:none;"' : ''; ?>>
                                        <td style="width: 200px;" valign="top">Access Token</td>
                                        <td valign="top"><input type="text" name="dropbox_email"
                                                value="<?php echo $row->dropbox_email; ?>" size="50" class="inputbox" />
                                        </td>
                    </tr>

                    <tr<?php echo $row->dropbox_email != '' ? ' style="display:none;"' : ''; ?>>
                        <td valign="top">Authentication Code</td>
                        <td valign="top"><input type="text" name="dropbox_password"
                                value="<?php echo $row->dropbox_password; ?>" size="50" class="inputbox" /></td>
                        </tr>
                        <?php
                        if ($row->dropbox_password != '' || $row->dropbox_email != '') {
                            ?>
                            <tr>
                                <td valign="top">Reset Authentication</td>
                                <td valign="top"><input name="dropbox_reset_auth" value="1" type="checkbox" /> <label>check and save
                                        to restart the process</label></td>
                            </tr>
                            <?php
                        }
                        ?>

                        <tr>
                            <td valign="top">Folder (leave empty for form name)</td>
                            <td valign="top"><input type="text" name="dropbox_folder"
                                    value="<?php echo $row->dropbox_folder; ?>" size="50" class="inputbox" /></td>
                        </tr>

                        <tr>
                            <td valign="top">Upload Submission</td>
                            <td valign="top"><input type="checkbox" name="dropbox_submission_enabled" value="1" size="50"
                                    class="inputbox" <?php echo $row->dropbox_submission_enabled == 1 ? ' checked="checked"' : ''; ?> /></td>
                        </tr>

                        <tr>
                            <td valign="top">Submission Types</td>
                            <td valign="top">
                                <input type="checkbox" name="dropbox_submission_types[]" value="pdf" size="50" class="inputbox"
                                    <?php echo in_array('pdf', $row->dropbox_submission_types) ? ' checked="checked"' : ''; ?> /> <label>PDF</label>
                                <input type="checkbox" name="dropbox_submission_types[]" value="csv" size="50" class="inputbox"
                                    <?php echo in_array('csv', $row->dropbox_submission_types) ? ' checked="checked"' : ''; ?> /> <label>CSV</label>
                                <input type="checkbox" name="dropbox_submission_types[]" value="xml" size="50" class="inputbox"
                                    <?php echo in_array('xml', $row->dropbox_submission_types) ? ' checked="checked"' : ''; ?> /> <label>XML</label>
                            </td>
                        </tr>

                </table>
        </fieldset>

        <?php
        echo HTMLHelper::_('uitab.endTab');

        JPluginHelper::importPlugin('breezingforms_addons');
        $addons = Factory::getApplication()->triggerEvent('onPropertiesDisplay', array(BFRequest::getInt('form', 0)));

        foreach ($addons as $addon) {
            echo $addon;
        }

        echo HTMLHelper::_('uitab.endTabSet');
        ?>
        </td>
        <td></td>
        </tr>
        </table>

        <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
        <input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
        <input type="hidden" name="option" value="<?php echo $option; ?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="act" value="manageforms" />
        <input type="hidden" name="pages" value="<?php echo $row->pages; ?>" />
        <input type="hidden" name="caller_url" value="<?php echo htmlentities($caller, ENT_QUOTES, 'UTF-8'); ?>" />
        </form>

        </fieldset>
        <?php
    }

    // edit

    static function listitems($option, &$rows, &$pkglist, $total = 0)
    {
        global $ff_config, $ff_version;
        ?>
        <script type="text/javascript">
            <!--
                <?php
                $jquery = "
                
            jQuery('document').ready(function(){

                jQuery('#pkgsel').on('change', function(){
                    document.adminForm.pkg.value = document.adminForm.pkgsel.value;
                    document.adminForm.task.value = '';
                    document.adminForm.submit();
                });

                jQuery('joomla-toolbar-button').on('click', function(e){

                    e.preventDefault();

                    let pressbutton = jQuery(this).attr('task');

                    var form = document.adminForm;
                    switch (pressbutton) {
                        case 'copy':
                        case 'publish':
                        case 'unpublish':
                        case 'remove':
                            if (form.boxchecked.value==0) {
                                alert(" . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_SELFORMSFIRST')) . ");
                                return;
                            } // if
                            break;
                        default:
                            break;
                    } // switch
                    if (pressbutton == 'remove')
                        if (!confirm(" . json_encode(BFText::_('COM_BREEZINGFORMS_FORMS_ASKDEL')) . ")) return;
                    if (pressbutton == '' && form.pkgsel.value == '')
                        form.pkg.value = '- blank -';
                    if (pressbutton == 'easymode')
                        form.act.value = 'easymode'
                    if (pressbutton == 'quickmode')
                        form.act.value = 'quickmode'
                    else
                        form.pkg.value = form.pkgsel.value;
                    form.task.value = pressbutton;
                    form.submit();
                });
            
            });
            ";

                Factory::getDocument()->addScriptDeclaration($jquery);

                if ($ff_config->enable_classic == 1) {
                    ToolbarHelper::custom('quickmode', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_QUICKMODE'), false);
                    ToolbarHelper::custom('easymode', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_EASYMODE'), false);
                    ToolbarHelper::custom('new', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_CLASSICMODE'), false);
                } else {
                    ToolbarHelper::custom('quickmode', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_NEW'), false);
                }
                ToolbarHelper::custom('copy', 'copy.png', 'copy_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_COPY'), false);
                ToolbarHelper::custom('publish', 'publish.png', 'publish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_PUBLISH'), false);
                ToolbarHelper::custom('unpublish', 'unpublish.png', 'unpublish_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_UNPUBLISH'), false);
                ToolbarHelper::custom('remove', 'delete.png', 'delete_f2.png', BFText::_('COM_BREEZINGFORMS_TOOLBAR_DELETE'), false);
                ?>

            function listItemTask(id, task) {
                var f = document.adminForm;
                cb = eval('f.' + id);
                if (cb) {
                    for (i = 0; true; i++) {
                        cbx = eval('f.cb' + i);
                        if (!cbx)
                            break;
                        cbx.checked = false;
                    } // for
                    cb.checked = true;
                    f.boxchecked.value = 1;
                    Joomla.submitbutton(task);
                }
                return false;
            } // listItemTask
            //-->
        </script>
        <form action="index.php?format=html" method="post" name="adminForm" id="adminForm" onsubmit="return false;">
            <label class="bfPackageSelector">
                <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PACKAGE'); ?>
                <select id="pkgsel" name="pkgsel" class="inputbox" size="1">
                    <?php
                    if (count($pkglist))
                        foreach ($pkglist as $pkg) {
                            $selected = '';
                            if ($pkg[0])
                                $selected = ' selected';
                            echo '<option value="' . $pkg[1] . '"' . $selected . '>' . ($pkg[1] == '' ? ' - ' . BFText::_('COM_BREEZINGFORMS_SELECT') . ' - ' : $pkg[1]) . '&nbsp;</option>';
                        } // foreach
                    ?>
                </select>
            </label>
            <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist table table-striped">
                <tr>
                    <th style="width: 25px;" nowrap align="right">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_SCRIPTID'); ?>
                    </th>
                    <th style="width: 25px;" nowrap align="center"><input type="checkbox" name="toggle" value=""
                            onclick="Joomla.checkAll(this);" /></th>
                    <th align="left">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_TITLE'); ?>
                    </th>
                    <th align="left">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_NAME'); ?>
                    </th>
                    <th align="left">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PAGES'); ?>
                    </th>
                    <th align="left">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_DESCRIPTION'); ?>
                    </th>
                    <th align="center">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_PUBLISHED'); ?>
                    </th>
                    <th align="center" colspan="2">
                        <?php echo BFText::_('COM_BREEZINGFORMS_FORMS_REORDER'); ?>
                    </th>
                </tr>
                <?php
                $k = 0;
                for ($i = 0; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $desc = $row->description;
                    if (strlen($desc) > $ff_config->limitdesc)
                        $desc = substr($desc, 0, $ff_config->limitdesc) . '...';
                    ?>
                    <tr class="row<?php echo $k; ?>">
                        <td nowrap valign="top" align="right">
                            <?php echo $row->id; ?>
                        </td>
                        <td nowrap valign="top" align="center"><input type="checkbox" id="cb<?php echo $i; ?>" name="ids[]"
                                value="<?php echo $row->id; ?>" onclick="Joomla.isChecked(this.checked);" /></td>

                        <?php
                        if ($row->template_code_processed != '' && $row->template_code_processed != 'QuickMode') {
                            ?>
                            <td valign="top" align="left"><a
                                    href="index.php?option=com_breezingforms&amp;format=html&amp;act=easymode&amp;formName=<?php echo $row->name ?>&amp;form=<?php echo $row->id; ?>">
                                    <?php echo $row->title; ?>
                                </a></td>
                            <td valign="top" align="left"><a
                                    href="index.php?option=com_breezingforms&amp;format=html&amp;act=easymode&amp;formName=<?php echo $row->name ?>&amp;form=<?php echo $row->id; ?>">
                                    <?php echo $row->name; ?>
                                </a></td>
                        <?php } else if ($row->template_code_processed == 'QuickMode') { ?>
                                <td valign="top" align="left"><a
                                        href="index.php?option=com_breezingforms&amp;format=html&amp;act=quickmode&amp;formName=<?php echo $row->name ?>&amp;form=<?php echo $row->id; ?>">
                                    <?php echo $row->title; ?>
                                    </a></td>
                                <td valign="top" align="left"><a
                                        href="index.php?option=com_breezingforms&amp;format=html&amp;act=quickmode&amp;formName=<?php echo $row->name ?>&amp;form=<?php echo $row->id; ?>">
                                    <?php echo $row->name; ?>
                                    </a></td>
                        <?php } else { ?>
                                <td valign="top" align="left"><a href="#editpage1"
                                        onclick="return listItemTask('cb<?php echo $i; ?>', 'editpage1')">
                                    <?php echo $row->title; ?>
                                    </a></td>
                                <td valign="top" align="left"><a href="#editform"
                                        onclick="return listItemTask('cb<?php echo $i; ?>', 'edit')">
                                    <?php echo $row->name; ?>
                                    </a></td>
                        <?php } ?>

                        <td nowrap valign="top" align="left">
                            <a
                                href="index.php?option=com_breezingforms&amp;format=html&amp;act=quickmode&amp;formName=<?php echo $row->name ?>&amp;form=<?php echo $row->id; ?>">
                                <?php echo $row->pages; ?>
                            </a>
                        </td>
                        <td valign="top" align="left">
                            <?php echo htmlspecialchars($desc, ENT_QUOTES); ?>
                        </td>
                        <td valign="top" align="center">
                            <?php
                            if ($row->published == "1") {
                                ?><a class="tbody-icon active" href="#"
                                    onClick="return listItemTask('cb<?php echo $i; ?>', 'unpublish')"><span class="icon-publish"
                                        aria-hidden="true"></span></a>
                                <?php
                            } else {
                                ?><a class="tbody-icon" href="#"
                                    onClick="return listItemTask('cb<?php echo $i; ?>', 'publish')"><span class="icon-unpublish"
                                        aria-hidden="true"></span></a>
                                <?php
                            } // if
                            ?>
                        </td>
                        <td valign="top" align="right">
                            <?php
                            if ($i > 0) {
                                ?><a href="#"
                                    onClick="return listItemTask('cb<?php echo $i; ?>', 'orderup')"><img
                                        src="components/com_breezingforms/images/icons/uparrow.png" alt="^" border="0" /></a>
                                <?php
                            } // if
                            ?>
                        </td>
                        <td valign="top" align="left">
                            <?php
                            if ($i < count($rows) - 1) {
                                ?><a href="#"
                                    onClick="return listItemTask('cb<?php echo $i; ?>', 'orderdown')"><img
                                        src="components/com_breezingforms/images/icons/downarrow.png" alt="v" border="0" /></a>
                                <?php
                            } // if
                            ?>
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                } // for
        
                $limit = Factory::getApplication()->getUserStateFromRequest('global.list.limit', 'limit', Factory::getApplication()->getCfg('list_limit'), 'int');
                $pagination = facileFormsForm::getPagination($total, $limit, BFRequest::getInt('limitstart', 0));
                $pages_links = $pagination->getPagesLinks();
                ?>

                <tfoot>
                    <tr>
                        <td colspan="1000" valign="middle" align="center">
                            <?php echo $pages_links; ?>

                            <br />

                            <?php
                            echo $pagination->getLimitBox();
                            ?>

                            <br />

                            <div style="margin-top: 20px;">
                                <?php echo $pagination->getPagesCounter(); ?>
                            </div>

                            <!-- (<?php echo BFText::_('COM_BREEZINGFORMS_AMOUNT'); ?>: <?php echo $total; ?>) -->
                        </td>
                    </tr>
                </tfoot>

            </table>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="option" value="<?php echo $option; ?>" />
            <input type="hidden" name="act" value="manageforms" />
            <input type="hidden" name="limitstart" value="<?php echo BFRequest::getInt('limitstart', 0); ?>" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="form" value="" />
            <input type="hidden" name="page" value="" />
            <input type="hidden" name="pkg" value="" />
        </form>
        <?php
    }

    // listitems
}