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

class TableElements extends Table
{
    /**
     * Primary Key
     *
     * @var int
     */
    public $id = null;

    public $reference_id = null;

    public $type = '';
    
    public $change_type = '';

    public $options = null;

    public $custom_init_script = '';
    
    public $custom_action_script = '';
    
    public $custom_validation_script = '';
    
    public $validation_message = '';
    
    public $default_value = '';
    
    public $hint = '';
    
    /**
     * @var string
     */
    public $label = null;

    public $list_include = null;

    public $search_include = null;

    /**
     * @var int
     */
    public $ordering = 0;

    public $linkable = 1;

    public $editable = 0;

    /**
     * @var int
     */
    public $published = 1;

    public $item_wrapper = '';

    public $wordwrap = 0;

    public $order_type = '';
    
    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct( $db ) {
        parent::__construct('#__contentbuilder_elements', 'id', $db);
    }
}

// as of J! 2.5
class formTableElements extends TableElements{}
