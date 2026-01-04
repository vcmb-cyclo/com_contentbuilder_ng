<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

namespace Component\Contentbuilder\Administrator\Table;

// No direct access
\defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ElementsTable extends Table
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
    function __construct( DatabaseDriver $db ) {
        parent::__construct('#__contentbuilder_elements', 'id', $db);


        // Joomla attend un champ "state" pour publish/unpublish au lieu de "published"
        $this->setColumnAlias('state', 'published');
    }
}
