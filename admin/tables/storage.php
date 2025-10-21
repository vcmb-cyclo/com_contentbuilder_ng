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

class TableStorage extends Table
{
    public $id = 0;
    public $name = '';
    public $title = '';
    public $bytable = 0;
    public $ordering = 0;
    public $published = 0;


    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct($db)
    {
        parent::__construct('#__contentbuilder_storages', 'id', $db);
    }
}

// as of J! 2.5
class storageTableStorage extends TableStorage
{
}
