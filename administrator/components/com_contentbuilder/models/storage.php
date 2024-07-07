<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL * 
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;

HTMLHelper::_('behavior.keepalive');


require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelStorage extends CBModel
{
    private $_storage_data = null;

    function __construct($config)
    {
        parent::__construct();

        $this->_db = Factory::getContainer()->get(DatabaseInterface::class);

        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $array = CBRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
        if (CBRequest::getInt('id', 0) > 0) {
            $this->setId(CBRequest::getInt('id', 0));
        }

        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
        $limitstart = CBRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $filter_order = $mainframe->getUserStateFromRequest($option . 'fields_filter_order', 'filter_order', 'ordering', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'fields_filter_order_Dir', 'filter_order_Dir', 'asc', 'word');

        $this->setState('fields_filter_order', $filter_order);
        $this->setState('fields_filter_order_Dir', $filter_order_Dir);

        $filter_state = $mainframe->getUserStateFromRequest($option . 'fields_filter_state', 'filter_state', '', 'word');
        $this->setState('fields_filter_state', $filter_state);
    }

    function setPublished()
    {
        if (empty($this->_data)) {
            $this->_db->setQuery(' Update #__contentbuilder_storages ' .
                '  Set published = 1 Where id = ' . $this->_id);
            $this->_db->execute();
        }
    }

    function setUnpublished()
    {
        if (empty($this->_data)) {
            $this->_db->setQuery(' Update #__contentbuilder_storages ' .
                '  Set published = 0 Where id = ' . $this->_id);
            $this->_db->execute();
        }
    }

    function setListPublished()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $this->_db->setQuery(' Update #__contentbuilder_storage_fields ' .
                '  Set published = 1 Where id In ( ' . implode(',', $items) . ')');
            $this->_db->execute();
        }
    }

    function setListUnpublished()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $this->_db->setQuery(' Update #__contentbuilder_storage_fields ' .
                '  Set published = 0 Where id In ( ' . implode(',', $items) . ')');
            $this->_db->execute();
        }
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setId($id)
    {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    function getDbTables()
    {

        $tables = Factory::getContainer()->get(DatabaseInterface::class)->getTableList();
        return $tables;
    }

    function getStorage()
    {
        $query = ' Select * From #__contentbuilder_storages ' .
            '  Where id = ' . $this->_id;
        $this->_db->setQuery($query);
        $data = $this->_db->loadObject();

        if (!$data) {
            $data = new stdClass();
            $data->id = 0;
            $data->name = null;
            $data->title = null;
            $data->bytable = 0;
            $data->published = null;
            $data->ordering = 0;
        }

        $this->_storage_data = $data;

        return $data;
    }

    function getFields()
    {
        $query = ' Select * From #__contentbuilder_storage_fields ' .
            '  Where storage_id = ' . $this->_id;
        $this->_db->setQuery($query);
        $data = $this->_db->loadObjectList();

        if (!$data) {
            $data = new stdClass();
            $data->id = 0;
            $data->storage_id = 0;
            $data->name = null;
            $data->title = null;
            $data->is_group = 0;
            $data->group_definition = "Label 1;value1\nLabel 2;value2\nLabel 3;value3";
            $data->published = null;
            $data->ordering = 0;
        }

        return $data;
    }

    private function buildOrderBy()
    {
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = '';
        $filter_order = $this->getState('fields_filter_order');
        $filter_order_Dir = $this->getState('fields_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if (!empty($filter_order) && !empty($filter_order_Dir)) {
            $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , ordering ';
        } else {
            $orderby = ' ORDER BY ordering ';
        }

        return $orderby;
    }


    function _buildQuery()
    {
        $filter_state = '';
        if ($this->getState('fields_filter_state') == 'P' || $this->getState('fields_filter_state') == 'U') {
            $published = 0;
            if ($this->getState('fields_filter_state') == 'P') {
                $published = 1;
            }

            $filter_state .= ' And published = ' . $published;
        }

        return "Select * From #__contentbuilder_storage_fields Where storage_id = " . $this->_id . $filter_state . $this->buildOrderBy();
    }

    function getData()
    {
        $this->_db->setQuery($this->_buildQuery(), $this->getState('limitstart'), $this->getState('limit'));
        $entries = $this->_db->loadObjectList();
        return $entries;
    }

    function storeCsv($file)
    {
        $data = CBRequest::get('post');

        if (isset($data['bytable']) && $data['bytable']) {
            return Text::_('COM_CONTENTBUILDER_CANNOT_USE_CSV_WITH_FOREIGN_TABLE');
        }

        if (isset($data['bytable'])) {
            unset($data['bytable']);
        }

        $dest = JPATH_SITE . DS . 'tmp' . DS . md5(mt_rand(0, mt_getrandmax())) . '_' . $file['name'];
        $uploaded = File::upload($file['tmp_name'], $dest, false, true);

        if ($uploaded) {
            @ini_set('auto_detect_line_endings', TRUE);
            $retval = $this->csv_file_to_table($dest, $data);
            File::delete($dest);
            return $retval;
        }

        return false;
    }

    function utf8_fopen_read($fileName, $encoding)
    {
        $fc = iconv($encoding, 'UTF-8//TRANSLIT', file_get_contents($fileName));
        $handle = fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);
        return $handle;
    }

    // required for CSV
    private $target_table = '';

    function csv_file_to_table($source_file, $data, $max_line_length = 1000000)
    {

        $encoding = '';

        switch (CBRequest::getVar('csv_repair_encoding', '')) {
            case 'WINDOWS-1250':
            case 'WINDOWS-1251':
            case 'WINDOWS-1252':
            case 'WINDOWS-1253':
            case 'WINDOWS-1254':
            case 'WINDOWS-1255':
            case 'WINDOWS-1256':
            case 'ISO-8859-1':
            case 'ISO-8859-2':
            case 'ISO-8859-3':
            case 'ISO-8859-4':
            case 'ISO-8859-5':
            case 'ISO-8859-6':
            case 'ISO-8859-7':
            case 'ISO-8859-8':
            case 'ISO-8859-9':
            case 'ISO-8859-10':
            case 'ISO-8859-11':
            case 'ISO-8859-12':
            case 'ISO-8859-13':
            case 'ISO-8859-14':
            case 'ISO-8859-15':
            case 'ISO-8859-16':
            case 'UTF-8-MAC':
            case 'UTF-16':
            case 'UTF-16BE':
            case 'UTF-16LE':
            case 'UTF-32':
            case 'UTF-32BE':
            case 'UTF-32LE':
            case 'ASCII':
            case 'BIG-5':
            case 'HEBREW':
            case 'CYRILLIC':
            case 'ARABIC':
            case 'GREEK':
            case 'CHINESE':
            case 'KOREAN':
            case 'KOI8-R':
            case 'KOI8-U':
            case 'KOI8-RU':
            case 'EUC-JP':
                $encoding = CBRequest::getVar('csv_repair_encoding', '');
                break;
        }

        $handle = null;

        if ($encoding) {
            if (!function_exists('iconv')) {
                return Text::_('COM_CONTENTBUILDER_CSV_IMPORT_REPAIR_NO_ICONV');
            }
            $handle = $this->utf8_fopen_read("$source_file", $encoding);
        } else {
            $handle = fopen("$source_file", "rb");
        }

        if ($handle !== FALSE) {

            $last_update = Factory::getDate()->toSql();

            $fieldnames = array();

            $columns = fgetcsv($handle, $max_line_length, CBRequest::getVar('csv_delimiter', ','), '"');

            $colCheck = array();
            foreach ($columns as &$column) {
                $col = str_replace(".", "", trim($column));
                if (in_array($col, $colCheck)) {
                    return Text::_('COM_CONTENTBUILDER_CSV_IMPORT_COLUMN_NOT_UNIQUE');
                }
                $colCheck[] = $col;
            }

            foreach ($columns as &$column) {
                $column = str_replace(".", "", trim($column));
                $data['fieldname'] = $column;
                $data['fieldtitle'] = $column;
                $data['is_group'] = false;
                $fieldnames[] = $this->store($data);
                $data['id'] = $this->_id;
            }

            if (CBRequest::getBool('csv_drop_records', false)) {
                $this->_db->setQuery("Truncate Table #__" . $this->target_table);
                $this->_db->execute();
                $this->_db->setQuery("Delete From #__contentbuilder_records Where `type` = 'com_contentbuilder' And reference_id = " . $this->_db->Quote($this->_id));
                $this->_db->execute();
                $this->_db->setQuery("Delete a.*, c.* From #__contentbuilder_articles As a, #__content As c Where c.id = a.article_id And a.`type` = 'com_contentbuilder' And a.reference_id = " . $this->_db->Quote($this->_id));
                $this->_db->execute();
            }

            $insert_query_prefix = "INSERT INTO #__" . $this->target_table . " (" . join(",", $fieldnames) . ")\nVALUES";

            while (($data = fgetcsv($handle, $max_line_length, CBRequest::getVar('csv_delimiter', ','), '"')) !== FALSE) {
                while (count($data) < count($columns))
                    array_push($data, NULL);
                $query = "$insert_query_prefix (" . join(", ", $this->quote_all_array($data)) . ")";
                $this->_db->setQuery($query);
                $this->_db->execute();
                $this->_db->setQuery("Insert Into #__contentbuilder_records (`type`,last_update,is_future,lang_code, sef, published, record_id, reference_id) Values ('com_contentbuilder'," . $this->_db->Quote($last_update) . ",0,'*',''," . CBRequest::getInt('csv_published', 0) . ", " . $this->_db->Quote(intval($this->_db->insertid())) . ", " . $this->_db->Quote($this->_id) . ")");
                $this->_db->execute();
            }
            fclose($handle);
        }
        return $this->_id;
    }

    function quote_all_array($values)
    {
        foreach ($values as $key => $value)
            if (is_array($value))
                $values[$key] = $this->quote_all_array($value);
            else
                $values[$key] = $this->quote_all($value);
        return $values;
    }

    function quote_all($value)
    {
        if (is_null($value))
            return "''";

        $value = $this->_db->Quote($value);
        return $value;
    }

    function store($post_replace = null)
    {
        $isNew = false;
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $row = $this->getTable();
        $storage = $this->getStorage();
        $storage_id = 0;

        if ($post_replace === null) {
            $data = CBRequest::get('post');
        } else {
            $data = $post_replace;
        }

        $bytable = isset($data['bytable']) ? $data['bytable'] : '';

        if (isset($data['bytable'])) {
            unset($data['bytable']);
        }

        // forcing to lower as database exports may lead to tablename lowering
        $data['name'] = isset($data['name']) ? strtolower($data['name']) : '';

        if ($bytable) {

            $data['bytable'] = 1;

            $newname = $bytable;
            $data['name'] = $newname;

            if (!trim($data['title'])) {
                $newtitle = $newname;
            } else {
                $newtitle = trim($data['title']);
            }
            $data['title'] = $newtitle;
        } else {

            $data['bytable'] = 0;

            $newname = str_replace(array(' ', "\n", "\r", "\t"), array(''), preg_replace("/[^a-zA-Z0-9_\s]/isU", "_", trim($data['name'])));
            $newname = preg_replace("/^([0-9\s])/isU", "field$1$2", $newname);
            $newname = $newname == '' ? 'field' . mt_rand(0, mt_getrandmax()) : $newname;

            // required for csv
            $this->target_table = $newname;

            $data['name'] = $newname;
            if (!trim($data['title'])) {
                $newtitle = $newname;
            } else {
                $newtitle = trim($data['title']);
            }
            $data['title'] = $newtitle;
        }

        $listnames = isset($data['itemNames']) ? $data['itemNames'] : array();
        $listtitles = isset($data['itemTitles']) ? $data['itemTitles'] : array();
        $listisgroup = isset($data['itemIsGroup']) ? $data['itemIsGroup'] : array();
        $listgroupdefinitions = CBRequest::getVar('itemGroupDefinitions', array(), 'POST', 'ARRAY', CBREQUEST_ALLOWRAW);

        unset($data['itemIsGroup']);
        unset($data['itemGroupDefinitions']);
        unset($data['itemNames']);
        unset($data['itemTitles']);

        // case of new field
        $newfieldname = '';
        $newfieldtitle = '';
        $is_group = 0;
        $group_definition = '';

        $fieldexists = false;

        if (isset($data['fieldname']) && trim($data['fieldname'])) {
            $newfieldname = str_replace(array(' ', "\n", "\r", "\t"), array('_'), preg_replace("/[^a-zA-Z0-9_\s]/isU", "_", trim($data['fieldname'])));
            $newfieldname = preg_replace("/^([0-9\s])/isU", "field$1$2", $newfieldname);
            $newfieldname = $newfieldname == '' ? 'field' . mt_rand(0, mt_getrandmax()) : $newfieldname;
            if (!trim($data['fieldtitle'])) {
                $newfieldtitle = $newfieldname;
            } else {
                $newfieldtitle = trim($data['fieldtitle']);
            }
            $this->_db->setQuery("Select `name` From #__contentbuilder_storage_fields Where `name` = " . $this->_db->Quote($newfieldname) . " And storage_id = " . CBRequest::getInt('id', 0));
            $fieldexists = $this->_db->loadResult();
            if ($fieldexists) {
                $newfieldname = $fieldexists;
            }
            $is_group = intval($data['is_group']);
            $group_definition = $data['group_definition'];
            unset($data['is_group']);
            unset($data['group_definition']);
            unset($data['fieldname']);
            unset($data['fieldtitle']);
        }

        try {
            $rr = $row->bind($data);
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        } finally {
            if (!$rr) {
                return false;
            }
        }


        try {
            $r = $row->check();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        } finally {
            if (!$r) {
                return false;
            }
        }



        try {
            $storeRes = $row->store();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }


        if (!$storeRes) {
            return false;
        } else {
            if (intval($data['id']) != 0) {
                $storage_id = intval($data['id']);
            } else {
                $isNew = true;
                $storage_id = $this->_db->insertid();
                $this->_id = $storage_id;
            }

            // required for csv
            $this->_id = $storage_id;
        }

        $row->reorder();

        $this->_db->setQuery("Select Max(ordering)+1 From #__contentbuilder_storage_fields Where storage_id = " . $this->_id . "");
        $max = intval($this->_db->loadResult());

        // we have a new field, so let's add it
        if (!$bytable && $this->_id && $newfieldname && !$fieldexists) {

            $this->_db->setQuery("Insert Into #__contentbuilder_storage_fields (ordering, storage_id,`name`,`title`,`is_group`,`group_definition`) Values ($max," . intval($this->_id) . "," . $this->_db->Quote($newfieldname) . "," . $this->_db->Quote($newfieldtitle) . "," . $is_group . "," . $this->_db->Quote($group_definition) . ")");
            $this->_db->execute();
        }

        // table
        // create or update the corresponding table, field synch below

        $last_update = Factory::getDate()->toSql;

        $tables = CBCompat::getTableFields(Factory::getContainer()->get(DatabaseInterface::class)->getTableList());

        if (!$bytable && !isset($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . $data['name']])) {
            if ($storage->name && isset($tables[Factory::getContainer()->get(DatabaseInterface::class)->getPrefix() . $storage->name])) {

                $this->_db->setQuery("Rename Table #__" . $storage->name . " To #__" . $data['name']);
                $this->_db->execute();
            } else {
                try {
                    $this->_db->setQuery('
                    CREATE TABLE `#__' . $data['name'] . '` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `storage_id` INT NOT NULL DEFAULT "' . $this->_id . '",
                    `user_id` INT NOT NULL DEFAULT "0",
                    `created` DATETIME NOT NULL DEFAULT "' . $last_update . '",
                    `created_by` VARCHAR( 255 ) NOT NULL DEFAULT "",
                    `modified_user_id` INT NOT NULL DEFAULT "0",
                    `modified` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
                    `modified_by` VARCHAR( 255 ) NOT NULL DEFAULT ""
                    ) ;
                    ');
                    $this->_db->execute();
                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD INDEX ( `storage_id` )");
                    $this->_db->execute();
                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD INDEX ( `user_id` )");
                    $this->_db->execute();
                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD INDEX ( `created` )");
                    $this->_db->execute();
                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD INDEX ( `modified_user_id` )");
                    $this->_db->execute();
                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD INDEX ( `modified` )");
                    $this->_db->execute();
                } catch (Exception $e) {
                }
            }
        } else if ($bytable) {
            // creating the storage fields in custom table if not existing already
            $system_fields = array('id', 'storage_id', 'user_id', 'created', 'created_by', 'modified_user_id', 'modified', 'modified_by');
            $allfields = array();
            $fieldin = '';
            $fields = $tables[$data['name']];
            foreach ($fields as $field => $type) {
                $fieldin .= "'" . $field . "',";
            }
            $fieldin = rtrim($fieldin, ',');
            if ($fieldin) {
                $this->_db->setQuery("Select `name` From #__contentbuilder_storage_fields Where `name` In (" . $fieldin . ") And storage_id = " . $this->_id);

                $fieldnames = $this->_db->loadColumn();
                foreach ($fields as $field => $type) {
                    if (!in_array($field, $fieldnames) && !in_array($field, $system_fields)) {
                        $this->_db->setQuery("Insert Into #__contentbuilder_storage_fields (ordering,storage_id,`name`,`title`,`is_group`,`group_definition`) Values ($max," . intval($this->_id) . "," . $this->_db->Quote($field) . "," . $this->_db->Quote($field) . ",0,'')");
                        $this->_db->execute();
                    }
                    $allfields[] = $field;
                }

                // now we add missing system columns into the custom table
                try {
                    foreach ($system_fields as $missing) {
                        if (!in_array($missing, $allfields)) {
                            switch ($missing) {
                                case 'id':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ");
                                    $this->_db->execute();
                                    break;
                                case 'storage_id':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `storage_id` INT NOT NULL DEFAULT " . $this->_id . ", ADD INDEX ( `storage_id` )");
                                    $this->_db->execute();
                                    break;
                                case 'user_id':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `user_id` INT NOT NULL DEFAULT 0, ADD INDEX ( `user_id` ) ");
                                    $this->_db->execute();
                                    break;
                                case 'created':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `created` DATETIME NOT NULL DEFAULT '" . $last_update . "', ADD INDEX ( `created` ) ");
                                    $this->_db->execute();
                                    break;
                                case 'created_by':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `created_by` VARCHAR( 255 ) NOT NULL DEFAULT '' ");
                                    $this->_db->execute();
                                    break;
                                case 'modified_user_id':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `modified_user_id` INT NOT NULL DEFAULT 0, ADD INDEX ( `modified_user_id` ) ");
                                    $this->_db->execute();
                                    break;
                                case 'modified':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ADD INDEX ( `modified` ) ");
                                    $this->_db->execute();
                                    break;
                                case 'modified_by':
                                    $this->_db->setQuery("ALTER TABLE `" . $data['name'] . "` ADD `modified_by` VARCHAR( 255 ) NOT NULL DEFAULT '' ");
                                    $this->_db->execute();
                                    break;
                            }
                        }
                    }
                } catch (Exception $e) {
                }

                // importing records
                if ($isNew) {

                    $this->_db->setQuery("Alter Table `" . $data['name'] . "` Alter Column `storage_id` Set Default '" . $this->_id . "'");
                    $this->_db->execute();

                    $this->_db->setQuery("Update `" . $data['name'] . "` Set `storage_id` = '" . $this->_id . "'");
                    $this->_db->execute();

                    $this->_db->setQuery("Select id From `" . $data['name'] . "`");

                    $third_party_ids = $this->_db->loadColumn();

                    foreach ($third_party_ids as $third_party_id) {
                        $this->_db->setQuery("Insert Into #__contentbuilder_records (
                            `type`,
                            last_update,
                            is_future,
                            lang_code, 
                            sef, 
                            published, 
                            record_id, 
                            reference_id
                        ) 
                        Values 
                        (
                            'com_contentbuilder',
                            " . $this->_db->Quote($last_update) . ",
                            0,
                            '*',
                            '',
                            1,
                            " . $this->_db->Quote(intval($third_party_id)) . ",
                            " . $this->_db->Quote($this->_id) . "
                        )"); // ignore already imported records

                        $this->_db->execute();
                    }
                }
            }
        }

        $tables = CBCompat::getTableFields($this->_db->getTableList());

        foreach ($listnames as $field_id => $name) {

            $name = str_replace(array(' ', "\n", "\r", "\t"), array(''), preg_replace("/[^a-zA-Z0-9_\s]/isU", "_", trim($name)));
            $name = preg_replace("/^([0-9\s])/isU", "field$1$2", $name);
            $name = $name == '' ? 'field' . mt_rand(0, mt_getrandmax()) : $name;

            if (!trim($listtitles[$field_id])) {
                $listtitles[$field_id] = $name;
            } else {
                $listtitles[$field_id] = trim($listtitles[$field_id]);
            }

            if (!$bytable) {

                $this->_db->setQuery("Select `name` From #__contentbuilder_storage_fields Where id = " . intval($field_id));
                $old_name = $this->_db->loadResult();

                $this->_db->setQuery("Update #__contentbuilder_storage_fields Set group_definition = " . $this->_db->Quote($listgroupdefinitions[$field_id]) . ", is_group = " . intval($listisgroup[$field_id]) . ",`name` = " . $this->_db->Quote($name) . ", `title` = " . $this->_db->Quote($listtitles[$field_id]) . " Where id = " . intval($field_id));
                $this->_db->execute();

                if ($old_name != $name) {

                    $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` CHANGE `" . $old_name . "` `" . $name . "` TEXT ");
                    $this->_db->execute();
                }
            } else {
                $this->_db->setQuery("Update #__contentbuilder_storage_fields Set group_definition = " . $this->_db->Quote($listgroupdefinitions[$field_id]) . ", is_group = " . intval($listisgroup[$field_id]) . ", `title` = " . $this->_db->Quote($listtitles[$field_id]) . " Where id = " . intval($field_id));
                $this->_db->execute();
            }
        }

        $this->getTable('storage_fields')->reorder('storage_id = ' . $this->_id);

        if (!$bytable) {

            // fields
            // synch non-existing fields
            $fields = $this->getFields();

            foreach ($fields as $field) {
                if (!isset($field->name)) {
                    continue;
                }
                $fieldname = $field->name;
                if ($fieldname && !isset($tables[$this->_db->getPrefix() . $data['name']][$fieldname])) {
                    try {
                        $this->_db->setQuery("ALTER TABLE `#__" . $data['name'] . "` ADD `" . $fieldname . "` TEXT NULL ");
                        $this->_db->execute();
                    } catch (Exception $e) {
                    }
                }
            }
        }

        if ($post_replace === null) {
            return $this->_id;
        } else {
            return $newfieldname;
        }
    }

    function delete()
    {
        $storage = $this->getStorage();
        $cids = CBRequest::getVar('cid', array(0), 'post', 'array');
        ArrayHelper::toInteger($cids);
        $row = $this->getTable();

        foreach ($cids as $cid) {

            $this->_db->setQuery("
                Delete
                    `elements`.*
                From
                    #__contentbuilder_storage_fields As `elements`
                Where
                    `elements`.storage_id = " . $cid);


            $this->_db->execute();

            $this->getTable('storage_fields')->reorder('storage_id = ' . $cid);

            if (!$row->delete($cid)) {
                $this->setError($row->getErrorMsg());
                return false;
            }

            if (!$storage->bytable) {
                try {
                    $this->_db->setQuery("DROP TABLE `#__" . $storage->name . "`");
                    $this->_db->execute();
                } catch (Exception $e) {
                }
            }
        }

        $row->reorder();

        return true;
    }

    function listDelete()
    {
        $storage = $this->getStorage();

        $cids = CBRequest::getVar('cid', array(0), 'post', 'array');
        ArrayHelper::toInteger($cids);
        foreach ($cids as $cid) {

            $this->_db->setQuery("Select `name` From #__contentbuilder_storage_fields Where id = " . $cid);
            $field_name = $this->_db->loadResult();

            $this->_db->setQuery("
                Delete
                    `elements`.*
                From
                    #__contentbuilder_storage_fields As `elements`
                Where
                    `elements`.id = " . $cid);

            $this->_db->execute();

            if (!$storage->bytable) {
                try {
                    $this->_db->setQuery("ALTER TABLE `#__" . $storage->name . "` DROP `" . $field_name . "`");
                    $this->_db->execute();
                } catch (Exception $e) {
                }
            }

            $this->getTable('storage_fields')->reorder('storage_id = ' . $this->_id);
        }
    }

    function move($direction)
    {

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $mainframe = Factory::getApplication();

        $row = $this->getTable('storage');

        if (!$row->load($this->_id)) {
            $this->setError($db->getErrorMsg());
            return false;
        }

        if (!$row->move($direction)) {
            $this->setError($db->getErrorMsg());
            return false;
        }

        return true;
    }

    function listMove($direction)
    {
        $mainframe = Factory::getApplication();
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);

        if (count($items)) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $row = $this->getTable('storage_fields');

            if (!$row->load($items[0])) {
                $this->setError($db->getErrorMsg());
                return false;
            }

            if (!$row->move($direction, 'storage_id=' . $this->_id)) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }

        return true;
    }

    function getTotal()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    function getPagination()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    function listSaveOrder()
    {
        $items = CBRequest::getVar('cid', array(), 'post', 'array');
        ArrayHelper::toInteger($items);

        $total = count($items);
        $row = $this->getTable('storage_fields');
        $groupings = array();

        $order = CBRequest::getVar('order', array(), 'post', 'array');
        ArrayHelper::toInteger($order);

        // update ordering values
        for ($i = 0; $i < $total; $i++) {
            $row->load($items[$i]);
            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    $this->setError($row->getError());
                    return false;
                }
            } // if
        } // for


        $row->reorder("storage_id = " . $this->_id);
    }
}
