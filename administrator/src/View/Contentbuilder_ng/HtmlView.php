<?php
/**
 * Default page.
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView  as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        // 1️⃣ Récupération du WebAssetManager
        $document = $this->getDocument();
        $wa = $document->getWebAssetManager();

        // 2️⃣ Enregistrement + chargement du CSS
        $wa->registerAndUseStyle(
            'com_contentbuilder_ng.admin',
            'com_contentbuilder_ng/admin.css',
            [],
            ['media' => 'all']
        );

        // Icon addition.
        $wa->addInlineStyle(
            '.icon-logo_icon_cb{
                background-image:url(' . Uri::root(true) . '/media/com_contentbuilder_ng/images/logo_icon_cb.png);
                background-size:contain;
                background-repeat:no-repeat;
                background-position:center;
                display:inline-block;
                width:24px;
                height:24px;
            }'
        );

/*
        ToolbarHelper::title(
            'ContentBuilder :: ' . Text::_('com_contentbuilder_ng'),
            'logo_icon_cb'
        );*/


        // 3️⃣ Affichage du layout
        parent::display($tpl);
    }
}
