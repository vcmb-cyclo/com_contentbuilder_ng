<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Table;

// No direct access
\defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class StorageTable extends Table
{
    public $id = 0;
    public $name = '';
    public $title = '';
    public $bytable = 0;
    public $ordering = 0;
    public $created = null;
    public $modified = null;
    public $created_by = '';
    public $modified_by = '';
    public $published = 0;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__contentbuilder_ng_storages', 'id', $db);

        // Joomla attend un champ "state" pour publish/unpublish au lieu de "published"
        $this->setColumnAlias('state', 'published');
    }
}
