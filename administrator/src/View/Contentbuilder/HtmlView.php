<?php
/**
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder\Administrator\View\Contentbuilder;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        // 1️⃣ Récupération du WebAssetManager
        $wa = $this->getDocument()->getWebAssetManager();

        // 2️⃣ Enregistrement + chargement du CSS
        $wa->registerAndUseStyle(
            'com_contentbuilder.admin',
            'com_contentbuilder/admin.css',
            [],
            ['media' => 'all']
        );

        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';
        ToolBarHelper::title(Text::_('COM_CONTENTBUILDER_ABOUT') . '</span>', 'logo_left.png');

        // 3️⃣ Affichage du layout
        parent::display($tpl);
    }
}
