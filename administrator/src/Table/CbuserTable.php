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

class CbuserTable extends Table
{
    public $id = 0;
    public $userid = 0;
    public $form_id = 0;
    public $records = 0;
    public $published = 1;
    public $verified_view = 0;
    public $verification_date_view = null;
    public $verified_new = 0;
    public $verification_date_new = null;
    public $verified_edit = 0;
    public $verification_date_edit = null;
    public $limit_add = 0;
    public $limit_edit = 0;
    
    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct( DatabaseDriver $db ) {
        parent::__construct('#__contentbuilder_ng_users', 'id', $db);

        // Joomla attend un champ "state" pour publish/unpublish au lieu de "published"
        $this->setColumnAlias('state', 'published');       
    }
}
