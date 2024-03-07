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

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class BFIntegratorHtml{

    public static function listRules($rules){

        ?>
        <script>
            function listItemTask( id, task ) {
                var f = document.adminForm;
                id.split('cb');
                f.task.value = task;
                f.publish_id.value = id.split('cb')[1];
                Joomla.submitbutton(task);
                return false;
            }
        </script>

        <?php
        Factory::getDocument()->addScriptDeclaration('
            Joomla.listItemTask = listItemTask;
        ');
        ?>

        <form action="index.php" method="post" name="adminForm" id="adminForm">

                <table cellpadding="4" cellspacing="0" border="0" width="100%"  class="adminlist table table-striped">

                    <tr>
                        <th style="width: 60px;" nowrap align="center"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>

                        <th style="width: 20%;">
                            <?php echo Text::_( 'Rulename' ); ?>
                        </th>
                        <th style="width: 20%;">
                            <?php echo Text::_( 'Type' ); ?>
                        </th>
                        <th style="width: 20%;">
                            <?php echo Text::_( 'Form' ); ?>
                        </th>
                        <th style="width: 20%;">
                            <?php echo Text::_( 'Table' ); ?>
                        </th>
                        <th style="width: 20%;">
                            <?php echo Text::_( 'published' ); ?>
                        </th>

                    </tr>

                    <?php
                    $k = 0;
                    $cnt = count( $rules );
                    for($i=0; $i < $cnt; $i++){
                    $rule        = $rules[$i];
                    $checked     = HTMLHelper::_( 'grid.id', $i, $rule->id );
                    ?>

                    <tr class="<?php echo "row$k"; ?>">

                        <td nowrap valign="top" align="center"><input type="checkbox" id="cb<?php echo $rule->id; ?>" name="cid[]" value="<?php echo $rule->id; ?>" onclick="Joomla.isChecked(this.checked);" /></td>

                        <td><a href="index.php?option=com_breezingforms&act=integrate&task=edit&id=<?php echo $rule->id; ?>"><?php echo htmlentities($rule->name, ENT_COMPAT, 'UTF-8') ?></a></td>
                        <td><a href="index.php?option=com_breezingforms&act=integrate&task=edit&id=<?php echo $rule->id; ?>"><?php echo htmlentities($rule->type, ENT_COMPAT, 'UTF-8') ?></a></td>
                        <td><a href="index.php?option=com_breezingforms&act=integrate&task=edit&id=<?php echo $rule->id; ?>"><?php echo htmlentities($rule->form_name, ENT_COMPAT, 'UTF-8') ?></a></td>
                        <td><a href="index.php?option=com_breezingforms&act=integrate&task=edit&id=<?php echo $rule->id; ?>"><?php echo htmlentities($rule->reference_table, ENT_COMPAT, 'UTF-8') ?></a></td>
                        <td valign="top" align="center"><?php
                        if ($rule->published) {
                            ?><a class="tbody-icon active" href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $rule->id; ?>','unpublish')"><span class="icon-publish" aria-hidden="true"></span></a><?php
                        } else {
                            ?><a class="tbody-icon" href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $rule->id; ?>','publish')"><span class="icon-unpublish" aria-hidden="true"></span></a><?php
                        } // if
                        ?></td>

                    </tr>
                    <?php
                    $k = 1 - $k;
                    }
                    ?>
                </table>

            <input type="hidden" name="option" value="com_breezingforms" />
            <input type="hidden" name="act" value="integrate" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="publish_id" value="-1" />

        </form>

        <?php

    }

    public static function edit($rule, $items, $tables, $forms, $formElements, $criteriaForm, $criteriaJoomla, $criteriaFixed, $showType = 'all'){
        ?>

        <?php

        // Custom code added
        $formSelector = array('all' => '', 'published' => '','unpublished' => '');
        $formSelector[$showType] = 'selected="selected"';
        ?>

        <h3><?php echo Text::_('Base Data') ?></h3>

        <form action="index.php" method="post" name="adminForm" id="adminForm">

                <table class="adminlist table table-striped">

                    <tr>
                        <th width="300">
                            <?php echo Text::_( 'Rulename' ); ?>
                        </th>
                        <th width="200">
                            <?php echo Text::_( 'Showing' ); ?>
                        </th>
                        <th width="300">
                            <?php echo Text::_( 'Form' ); ?>
                        </th>
                        <th width="300">
                            <?php echo Text::_( 'Table' ); ?>
                        </th>
                        <th>
                            <?php echo Text::_( 'Type' ); ?>
                        </th>
                    </tr>
                    <tr class="row0">

                        <td><input type="text" <?php echo $rule != null ? ' disabled="disabled" ' : '' ?> name="rule_name" value="<?php echo $rule != null ? htmlentities($rule->name, ENT_COMPAT, 'UTF-8') : '' ?>"/></td>

                        <td>
                            <?php
                            if(isset($rule)){
                                echo $rule->type;
                            } else {
                                // Custom code added
                                echo '
							<select style="width: 120px" name="formSelector" onchange="Joomla.submitbutton(value)">
								<option '.$formSelector['all'].' value="add" >All</option>
								<option '.$formSelector['published'].' value="showPublished">Published</option>
								<option '.$formSelector['unpublished'].' value="showUnpublished">Unpublished</option>
							</select>

						';
                                // END
                            }
                            ?>
                        </td>

                        <td>
                            <?php
                            $disabled = '';
                            foreach($forms As $form){
                                if(isset($rule) && $form->id == $rule->form_id){
                                    $disabled = ' disabled="disabled" ';
                                    break;
                                }
                            }
                            ?>
                            <select name="form_id" <?php echo $disabled ?>>
                                <?php
                                foreach($forms As $form){
                                    $selected = '';
                                    if(isset($rule) && $form->id == $rule->form_id){
                                        $selected = ' selected="selected"';
                                    }
                                    ?>

                                    <option <?php echo $selected ?> value="<?php echo htmlentities($form->id, ENT_COMPAT, 'UTF-8') ?>" ><?php echo htmlentities($form->name, ENT_COMPAT, 'UTF-8') ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <?php
                            $disabled = '';
                            $settings = array();
                            foreach($tables As $tableName => $tableSettings){
                                $selected = '';
                                if(isset($rule) && $tableName == $rule->reference_table){
                                    $settings = $tableSettings;
                                    $disabled = ' disabled="disabled" ';
                                    break;
                                }
                            }
                            ?>
                            <select name="reference_table" <?php echo $disabled ?>>
                                <?php
                                $refTable = '';
                                foreach($tables As $tableName => $tableSettings){
                                    $selected = '';
                                    if(isset($rule) && $tableName == $rule->reference_table){
                                        $refTable = $tableName;
                                        $selected = ' selected="selected"';
                                    }
                                    ?>
                                    <option <?php echo $selected ?> value="<?php echo htmlentities($tableName, ENT_COMPAT, 'UTF-8') ?>"><?php echo htmlentities($tableName, ENT_COMPAT, 'UTF-8') ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <?php
                            if(isset($rule)){
                                echo $rule->type;
                            } else {
                                echo Text::_('Insert') . ' <input checked="checked" type="radio" name="type" value="insert"/> ' . Text::_('Update') . ' <input type="radio" name="type" value="update"/>';
                            }
                            ?>
                        </td>
                    </tr>

                </table>


            <input type="hidden" name="option" value="com_breezingforms" />
            <input type="hidden" name="act" value="integrate" />
            <input type="hidden" name="task" value="update" />

        </form>

        <?php

        if($rule != null){

            ?>

            <br/>
            <h3><?php echo Text::_('Data Integration') ?></h3>

            <script>
                function listItemTask( id, task ) {
                    var f = document.addItemForm;
                    id.split('cb');
                    f.publish_id.value = id.split('cb')[1];

                    if(task == 'publish' || task== 'unpublish'){
                        f.task.value = 'pub';
                        f.pub.value = task;
                        task = 'edit';
                    }

                    //f.task.value = task;
                    f.submit();
                    return false;
                }
            </script>

            <?php
            Factory::getDocument()->addScriptDeclaration('
            Joomla.listItemTask = listItemTask;
            ');
            ?>

            <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="addItemForm">
                <input type="hidden" name="publish_id" value="-1" />
                <input type="hidden" name="task" value="addItem" />
                <input type="hidden" name="id" value="<?php echo $rule->id ?>" />
                <input type="hidden" name="pub" value="" />
                <table class="adminlist table table-striped">

                    <tr>
                        <th width="300">
                            <?php echo Text::_( 'Form Element (incoming)' ); ?>
                        </th>
                        <th width="300">
                            <?php echo Text::_( 'Copy To' ); ?>
                        </th>
                        <th width="300">
                            <?php echo Text::_( 'Database Field (outgoing)' ); ?>
                        </th>
                        <th width="300">
                            <?php echo Text::_( '' ); ?>
                        </th>
                        <th>
                            <?php echo Text::_( 'Publish' ); ?>
                        </th>
                    </tr>

                    <tr class="<?php echo "row0"; ?>">
                        <td>
                            <select name="element_id">
                                <?php
                                foreach($formElements As $formElement){
                                    if($formElement->name != 'bfFakeName' && $formElement->name != 'bfFakeName2' && $formElement->name != 'bfFakeName3' && $formElement->name != 'bfFakeName4'){
                                        ?>
                                        <option value="<?php echo $formElement->id ?>"><?php echo htmlentities($formElement->name, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($formElement->type, ENT_COMPAT, 'UTF-8') ?>)</option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td align="center"><?php echo htmlentities('=>') ?></td>
                        <td>
                            <select name="reference_column">
                                <?php
                                foreach($tables[$refTable] As $fieldName => $fieldSettings){
                                    ?>
                                    <option value="<?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?>"><?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($fieldSettings, ENT_COMPAT, 'UTF-8') ?>)</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td colspan="2"><a style="display: block;" href="javascript:document.addItemForm.submit();"><?php echo Text::_('add') ?></a></td>
                    </tr>

                    <?php
                    $k = 1;

                    foreach($items As $item){
                        $published   = HTMLHelper::_('grid.published', $item, $item->id );
                        ?>
                        <tr class="<?php echo "row$k"; ?>">

                            <td><?php echo htmlentities($item->element_name, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($item->element_type, ENT_COMPAT, 'UTF-8') ?>)</td>
                            <td align="center"><?php echo htmlentities('=>') ?></td>
                            <td><?php echo htmlentities($item->reference_column, ENT_COMPAT, 'UTF-8') ?> (<?php echo $settings[$item->reference_column] ?>)</td>
                            <td>
                                <div id="code<?php echo $item->id ?>" style="display:none">

                                    <?php
                                    $params = array(
                                        'syntax'    => 'php',
                                    );
                                    $editor = Editor::getInstance('codemirror');
                                    echo $editor->display('code'.$item->id, $item->code, '100%', 450, 40, 20, false, 'code'.$item->id, null, null, $params);
                                    ?>

                                    <br/>
                                    <a style="display: block;" href="javascript:document.saveCodeForm.itemId.value=<?php echo $item->id ?>;document.saveCodeForm.code.value=Joomla.editors.instances['code<?php echo $item->id?>'].getValue();document.saveCodeForm.submit();"><?php echo Text::_('Save') ?></a>
                                </div>
                                <a style="display: block;" href="javascript:showCode<?php echo $item->id ?>()"><?php echo Text::_('Code') ?></a>

                                <a style="display: block;" href="index.php?option=com_breezingforms&act=integrate&task=removeItem&itemId=<?php echo $item->id ?>&id=<?php echo $rule->id ?>"><?php echo Text::_('Remove') ?></a></td>
                                <td valign="top" align="center"><?php
                                if ($item->published == "1") {
                                    ?><a class="tbody-icon active" href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $item->id; ?>','unpublish')"><span class="icon-publish" aria-hidden="true"></span></a><?php
                                } else {
                                    ?><a class="tbody-icon" href="javascript:void(0);" onClick="return listItemTask('cb<?php echo $item->id; ?>','publish')"><span class="icon-unpublish" aria-hidden="true"></span></a><?php
                                } // if
                                ?></td>
                        </tr>

                        <script>
                            function showCode<?php echo $item->id?>(){
                                if(document.getElementById('code<?php echo $item->id?>').style.display == 'none') {
                                    document.getElementById('code<?php echo $item->id?>').style.display = '';
                                    // XDA/GIL > 14012024 - Refresh doesn't exist in codemirror 6
									//Joomla.editors.instances['code<?php echo $item->id?>'].refresh();
                                }else {
                                    document.getElementById('code<?php echo $item->id?>').style.display = 'none';
                                }
                            }
                        </script>

                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </table>
            </form>

            <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="saveCodeForm">
                <input type="hidden" name="task" value="saveCode" />
                <input type="hidden" name="code" value="" />
                <input type="hidden" name="itemId" value="-1" />
                <input type="hidden" name="id" value="<?php echo $rule->id ?>" />
            </form>

            <?php
            if($rule->type == 'update'){
                ?>

                <br/>
                <h3><?php echo Text::_('Update Criteria - Form') ?></h3>

                <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="addCriteriaForm">
                    <input type="hidden" name="publish_id" value="-1" />
                    <input type="hidden" name="task" value="addCriteria" />
                    <input type="hidden" name="id" value="<?php echo $rule->id ?>" />
                    <table class="adminlist table table-striped">

                        <tr>
                            <th width="300">
                                <?php echo Text::_( 'Database Field Value' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Operation' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Form Element Value' ); ?>
                            </th>
                            <th width="100">
                                <?php echo Text::_( 'And/Or' ); ?>
                            </th>
                            <th>
                                <?php echo Text::_( '' ); ?>
                            </th>
                        </tr>

                        <tr class="<?php echo "row0"; ?>">
                            <td>
                                <select name="reference_column">
                                    <?php
                                    foreach($tables[$refTable] As $fieldName => $fieldSettings){
                                        ?>
                                        <option value="<?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?>"><?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($fieldSettings, ENT_COMPAT, 'UTF-8') ?>)</option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </td>
                            <td align="center">

                                <select name="operator">
                                    <option value="<?php echo htmlentities('=') ?>">equals</option>
                                    <option value="<?php echo htmlentities('<>') ?>">not equal</option>
                                    <option value="<?php echo htmlentities('>') ?>">greater than</option>
                                    <option value="<?php echo htmlentities('<') ?>">less than</option>
                                    <option value="<?php echo htmlentities('>=') ?>">equals or greater than</option>
                                    <option value="<?php echo htmlentities('<=') ?>">equals or less than</option>
                                    <option value="<?php echo htmlentities('%...%') ?>">in value</option>
                                    <option value="<?php echo htmlentities('%...') ?>">starts with</option>
                                    <option value="<?php echo htmlentities('...%') ?>">ends with</option>
                                </select>

                            </td>
                            <td>
                                <select name="element_id">
                                    <?php
                                    foreach($formElements As $formElement){
                                        if($formElement->name != 'bfFakeName' && $formElement->name != 'bfFakeName2' && $formElement->name != 'bfFakeName3' && $formElement->name != 'bfFakeName4'){
                                            ?>
                                            <option value="<?php echo $formElement->id ?>"><?php echo htmlentities($formElement->name, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($formElement->type, ENT_COMPAT, 'UTF-8') ?>)</option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><?php echo Text::_('A N D') ?> <input type="radio" name="andor" value="AND" checked="checked" /> <?php echo Text::_('O R') ?> <input type="radio" name="andor" value="OR" /></td>
                            <td colspan="2"><a href="javascript:document.addCriteriaForm.submit();"><?php echo Text::_('add') ?></a></td>
                        </tr>

                        <?php
                        $k = 1;

                        foreach($criteriaForm As $criteria){
                            ?>
                            <tr class="<?php echo "row$k"; ?>">

                                <td><?php echo htmlentities($criteria->reference_column, ENT_COMPAT, 'UTF-8') ?> (<?php echo $settings[$criteria->reference_column] ?>)</td>
                                <td align="center"><?php echo htmlentities($criteria->operator, ENT_COMPAT, 'UTF-8') ?></td>
                                <td><?php echo htmlentities($criteria->element_name, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($criteria->element_type, ENT_COMPAT, 'UTF-8') ?>)</td>
                                <td><?php echo htmlentities($criteria->andor, ENT_COMPAT, 'UTF-8') ?> </td>
                                <td><a href="index.php?option=com_breezingforms&act=integrate&task=removeCriteria&criteriaId=<?php echo $criteria->id ?>&id=<?php echo $rule->id ?>"><?php echo Text::_('Remove') ?></a></td>
                            </tr>
                            <?php
                            $k = 1 - $k;
                        }
                        ?>
                    </table>
                </form>

                <br/>
                <h3><?php echo Text::_('Update Criteria - Joomla!') ?></h3>

                <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="addCriteriaJoomlaForm">
                    <input type="hidden" name="publish_id" value="-1" />
                    <input type="hidden" name="task" value="addCriteriaJoomla" />
                    <input type="hidden" name="id" value="<?php echo $rule->id ?>" />
                    <table class="adminlist table table-striped">

                        <tr>
                            <th width="300">
                                <?php echo Text::_( 'Database Field Value' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Operation' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Joomla Object Value' ); ?>
                            </th>
                            <th width="100">
                                <?php echo Text::_( 'And/Or' ); ?>
                            </th>
                            <th>
                                <?php echo Text::_( '' ); ?>
                            </th>
                        </tr>

                        <tr class="<?php echo "row0"; ?>">
                            <td>
                                <select name="reference_column">
                                    <?php
                                    foreach($tables[$refTable] As $fieldName => $fieldSettings){
                                        ?>
                                        <option value="<?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?>"><?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($fieldSettings, ENT_COMPAT, 'UTF-8') ?>)</option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </td>
                            <td align="center">

                                <select name="operator">
                                    <option value="<?php echo htmlentities('=') ?>">equals</option>
                                    <option value="<?php echo htmlentities('<>') ?>">not equal</option>
                                    <option value="<?php echo htmlentities('>') ?>">greater than</option>
                                    <option value="<?php echo htmlentities('<') ?>">less than</option>
                                    <option value="<?php echo htmlentities('>=') ?>">equals or greater than</option>
                                    <option value="<?php echo htmlentities('<=') ?>">equals or less than</option>
                                    <option value="<?php echo htmlentities('%...%') ?>">in value</option>
                                    <option value="<?php echo htmlentities('%...') ?>">starts with</option>
                                    <option value="<?php echo htmlentities('...%') ?>">ends with</option>
                                </select>

                            </td>
                            <td>
                                <select name="joomla_object">

                                    <option value="Userid"><?php echo Text::_('Userid') ?></option>
                                    <option value="Username"><?php echo Text::_('Username') ?></option>
                                    <option value="Language"><?php echo Text::_('Language') ?></option>
                                    <option value="Date"><?php echo Text::_('Date') ?></option>

                                </select>
                            </td>
                            <td><?php echo Text::_('A N D') ?> <input type="radio" name="andor" value="AND" checked="checked" /> <?php echo Text::_('O R') ?> <input type="radio" name="andor" value="OR" /></td>
                            <td colspan="2"><a href="javascript:document.addCriteriaJoomlaForm.submit();"><?php echo Text::_('add') ?></a></td>
                        </tr>

                        <?php
                        $k = 1;

                        foreach($criteriaJoomla As $criteria){
                            ?>
                            <tr class="<?php echo "row$k"; ?>">

                                <td><?php echo htmlentities($criteria->reference_column, ENT_COMPAT, 'UTF-8') ?> (<?php echo $settings[$criteria->reference_column] ?>)</td>
                                <td align="center"><?php echo htmlentities($criteria->operator, ENT_COMPAT, 'UTF-8') ?></td>
                                <td><?php echo htmlentities($criteria->joomla_object, ENT_COMPAT, 'UTF-8') ?></td>
                                <td><?php echo htmlentities($criteria->andor, ENT_COMPAT, 'UTF-8') ?> </td>
                                <td><a href="index.php?option=com_breezingforms&act=integrate&task=removeCriteriaJoomla&criteriaId=<?php echo $criteria->id ?>&id=<?php echo $rule->id ?>"><?php echo Text::_('Remove') ?></a></td>
                            </tr>
                            <?php
                            $k = 1 - $k;
                        }
                        ?>
                    </table>
                </form>

                <br/>
                <h3><?php echo Text::_('Update Criteria - Fixed') ?></h3>

                <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="addCriteriaFixedForm">
                    <input type="hidden" name="publish_id" value="-1" />
                    <input type="hidden" name="task" value="addCriteriaFixed" />
                    <input type="hidden" name="id" value="<?php echo $rule->id ?>" />
                    <table class="adminlist table table-striped">

                        <tr>
                            <th width="300">
                                <?php echo Text::_( 'Database Field Value' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Operation' ); ?>
                            </th>
                            <th width="300">
                                <?php echo Text::_( 'Fixed Value' ); ?>
                            </th>
                            <th width="100">
                                <?php echo Text::_( 'And/Or' ); ?>
                            </th>
                            <th>
                                <?php echo Text::_( '' ); ?>
                            </th>
                        </tr>

                        <tr class="<?php echo "row0"; ?>">
                            <td>
                                <select name="reference_column">
                                    <?php
                                    foreach($tables[$refTable] As $fieldName => $fieldSettings){
                                        ?>
                                        <option value="<?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?>"><?php echo htmlentities($fieldName, ENT_COMPAT, 'UTF-8') ?> (<?php echo htmlentities($fieldSettings, ENT_COMPAT, 'UTF-8') ?>)</option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </td>
                            <td align="center">

                                <select name="operator">
                                    <option value="<?php echo htmlentities('=') ?>">equals</option>
                                    <option value="<?php echo htmlentities('<>') ?>">not equal</option>
                                    <option value="<?php echo htmlentities('>') ?>">greater than</option>
                                    <option value="<?php echo htmlentities('<') ?>">less than</option>
                                    <option value="<?php echo htmlentities('>=') ?>">equals or greater than</option>
                                    <option value="<?php echo htmlentities('<=') ?>">equals or less than</option>
                                    <option value="<?php echo htmlentities('%...%') ?>">in value</option>
                                    <option value="<?php echo htmlentities('%...') ?>">starts with</option>
                                    <option value="<?php echo htmlentities('...%') ?>">ends with</option>
                                </select>

                            </td>
                            <td>
                                <input style="width:100%" type="text" name="fixed_value" value=""/>
                            </td>
                            <td><?php echo Text::_('A N D') ?> <input type="radio" name="andor" value="AND" checked="checked" /> <?php echo Text::_('O R') ?> <input type="radio" name="andor" value="OR" /></td>
                            <td colspan="2"><a href="javascript:document.addCriteriaFixedForm.submit();"><?php echo Text::_('add') ?></a></td>
                        </tr>

                        <?php
                        $k = 1;

                        foreach($criteriaFixed As $criteria){
                            ?>
                            <tr class="<?php echo "row$k"; ?>">

                                <td><?php echo htmlentities($criteria->reference_column, ENT_COMPAT, 'UTF-8') ?> (<?php echo $settings[$criteria->reference_column] ?>)</td>
                                <td align="center"><?php echo htmlentities($criteria->operator, ENT_COMPAT, 'UTF-8') ?></td>
                                <td><?php echo htmlentities($criteria->fixed_value, ENT_COMPAT, 'UTF-8') ?> </td>
                                <td><?php echo htmlentities($criteria->andor, ENT_COMPAT, 'UTF-8') ?> </td>
                                <td><a href="index.php?option=com_breezingforms&act=integrate&task=removeCriteriaFixed&criteriaId=<?php echo $criteria->id ?>&id=<?php echo $rule->id ?>"><?php echo Text::_('Remove') ?></a></td>
                            </tr>
                            <?php
                            $k = 1 - $k;
                        }
                        ?>
                    </table>
                </form>

                <?php
            }
        }
        if($rule != null){
            ?>

            <br/>
            <h3><?php echo Text::_('Finalize Code') ?></h3>

            <form action="index.php?option=com_breezingforms&act=integrate" method="post" name="saveFinalizeCodeForm">
                <input type="hidden" name="publish_id" value="-1" />
                <input type="hidden" name="task" value="saveFinalizeCode" />
                <input type="hidden" name="id" value="<?php echo $rule->id ?>" />

                <?php
                $params = array(
                    'syntax'    => 'php',
                );
                $editor = Editor::getInstance('codemirror');
                echo $editor->display('finalizeCode', $rule->finalize_code, '100%', 450, 40, 20, false, 'finalizeCode', null, null, $params);
                ?>

                <br/>
                <br/>
                <a href="javascript:document.saveFinalizeCodeForm.submit();"><?php echo Text::_('Save') ?></a>
            </form>

            <?php
        }
    }
}
