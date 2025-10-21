<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;

class TableList extends Table
{
    public $id = 0;
    public $type = '';
    public $reference_id = 0;
    public $name = '';
    public $title = '';
    public $created = '0000-00-00 00:00:00';
    public $modified = '0000-00-00 00:00:00';
    public $created_by = '';
    public $details_template = '';
    public $modified_by = '';
    public $print_button = 1;
    public $metadata = 1;
    public $export_xls = 0;
    public $show_id_column = 0;
    public $use_view_name_as_title = 0;
    public $intro_text = '';
    public $display_in = 0;
    public $ordering = 0;
    public $published = 0;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct($db)
    {
        parent::__construct('#__contentbuilder_forms', 'id', $db);
    }
}

// as of J! 2.5
class listTableList extends TableList
{
}
