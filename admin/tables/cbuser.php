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

class TableCbuser extends Table
{
    public $id = 0;
    public $userid = 0;
    public $form_id = 0;
    public $records = 0;
    public $published = 1;
    public $verified_view = 0;
    public $verification_date_view = '0000-00-00 00:00:00';
    public $verified_new = 0;
    public $verification_date_new = '0000-00-00 00:00:00';
    public $verified_edit = 0;
    public $verification_date_edit = '0000-00-00 00:00:00';
    public $limit_add = 0;
    public $limit_edit = 0;
    
    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct( $db ) {
        parent::__construct('#__contentbuilder_users', 'id', $db);
    }
}

// as of J! 2.5
class userTableCbuser extends TableCbuser{}

