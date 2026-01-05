<?php
/**
 * @package ContentBuilder
 * @author Markus Bopp / XDA+GIL
 * @link https://breezingforms.vcmb.fr
 * @copyright (C) 2026 by XDA+GIL
 * @license GNU/GPL
 */
namespace CB\Component\Contentbuilder\Administrator\View\Form;

ini_set('display_errors', 1);
error_reporting(E_ALL);

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true);

        $document = $app->getDocument();

        // JS
        $document->addScript(Uri::root(true) . '/media/js/com_contentbuilder/jscolor/jscolor.js');

        // CSS (propre, évite echo <link>)
        // Variante 1: via Document
        $document->addStyleSheet(Uri::root(true) . '/media/com_contentbuilder/css/bluestork.fix.css');

        // (Optionnel) Ton CSS inline (ok, mais évite les chemins relatifs fragiles)
        $document->addStyleDeclaration(
            ".icon-48-logo_left { background-image: url(" .
            Uri::root(true) . "/administrator/components/com_contentbuilder/views/logo_left.png); }"
        );

        // Charge l'item (chez toi tu appelles ça $this->form)
        $this->form = $this->get('Item');

        // Chargement sécurisé des éléments
        $formId = (int) ($this->form->id ?? 0);

        if ($formId > 0) {
            try {
                $factory = Factory::getApplication()
                    ->bootComponent('com_contentbuilder')
                    ->getMVCFactory();

                $elementsModel = $factory->createModel('Elements', 'Administrator', ['ignore_request' => true]);

                if (!$elementsModel) {
                    throw new \RuntimeException('Modèle Elements introuvable');
                }

                // Récupération sécurisée des données
                $items = $elementsModel->getItems();
                $this->elements = is_array($items) ? $items : [];  // Force tableau

                $this->elementsPagination = $elementsModel->getPagination() ?? null;
                $this->pagination         = $elementsModel->getPagination() ?? null;

                $state = $elementsModel->getState();
                $this->state = ($state instanceof \Joomla\CMS\MVC\Model\ListModel) ? $state : null;
                // OU simplement :
                // $this->state = $elementsModel->getState() ?? new \stdClass();

            } catch (\Throwable $e) {
                // Message visible pour le dev/admin
                Factory::getApplication()->enqueueMessage(
                    'Erreur lors du chargement des éléments : ' . $e->getMessage(),
                    'warning'
                );

                // Valeurs de secours pour éviter tout plantage dans le template
                $this->elements           = [];
                $this->elementsPagination = null;
                $this->pagination         = null;
                $this->state              = null;
            }
        } else {
            // Nouveau formulaire → pas d'éléments
            $this->elements           = [];
            $this->elementsPagination = null;
            $this->pagination         = null;
            $this->state              = null;
        }


        $isNew = ($formId < 1);
        $text  = $isNew ? Text::_('COM_CONTENTBUILDER_NEW') : Text::_('COM_CONTENTBUILDER_EDIT');

        ToolbarHelper::title(
            'ContentBuilder :: ' . ($isNew ? Text::_('COM_CONTENTBUILDER_FORM') : ($this->form->name ?? '')) .
            ' : <small><small>[ ' . $text . ' ]</small></small>',
            'logo_left.png'
        );

        ToolbarHelper::apply('form.apply');
        ToolbarHelper::save('form.save');
        ToolbarHelper::custom('form.save2new', 'save', '', Text::_('COM_CONTENTBUILDER_SAVENEW'), false);

        ToolbarHelper::custom('form.list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_LIST_INCLUDE'), false);
        ToolbarHelper::custom('form.no_list_include', 'menu', '', Text::_('COM_CONTENTBUILDER_NO_LIST_INCLUDE'), false);

        ToolbarHelper::custom('form.editable', 'edit', '', Text::_('COM_CONTENTBUILDER_EDITABLE'), false);
        ToolbarHelper::custom('form.not_editable', 'edit', '', Text::_('COM_CONTENTBUILDER_NOT_EDITABLE'), false);

        ToolbarHelper::publish('form.publish', 'publish', '', Text::_('COM_CONTENTBUILDER_PUBLISH'), false);
        ToolbarHelper::unpublish('form.unpublish', 'unpublish', '', Text::_('COM_CONTENTBUILDER_UNPUBLISH'), false);

        ToolbarHelper::cancel('form.cancel', $isNew ? 'JTOOLBAR_CLOSE' : 'JTOOLBAR_CLOSE');

        // Compat template / listes
        $this->lists['order_Dir']   = $this->state ? $this->state->get('list.direction', 'asc') : 'asc';
        $this->lists['order']       = $this->state ? $this->state->get('list.ordering', 'ordering') : 'ordering';
        $this->lists['limitstart']  = $this->state ? $this->state->get('list.start', 0) : 0;
        $this->ordering = true;

        // Données additionnelles
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = "
            SELECT CONCAT(REPEAT('..', COUNT(parent.id) - 1), node.title) AS text, node.id AS value
            FROM #__usergroups AS node, #__usergroups AS parent
            WHERE node.lft BETWEEN parent.lft AND parent.rgt
            GROUP BY node.id
            ORDER BY node.lft
        ";
        $db->setQuery($query);
        $this->gmap = $db->loadObjectList() ?? [];

        // ✅ Décode config de manière robuste (très probable cause ids 9/11)
        $this->form->config = $this->decodeLegacyConfig($this->form->config ?? null);

        $this->list_states_action_plugins = $this->get('ListStatesActionPlugins') ?? [];
        $this->verification_plugins       = $this->get('VerificationPlugins') ?? [];
        $this->theme_plugins              = $this->get('ThemePlugins') ?? [];

        HTMLHelper::_('behavior.keepalive');

        parent::display($tpl);
    }

    /**
     * Décode l'ancien format base64 + serialize, de façon tolérante.
     * Retourne array|null sans faire planter l'édition si la donnée est corrompue.
     */
    private function decodeLegacyConfig($raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Base64 strict
        $decoded = base64_decode((string) $raw, true);
        if ($decoded === false) {
            // Donnée pas en base64 => on évite de casser l'édition
            return null;
        }

        // Unserialize sécurisé (pas d'objets)
        try {
            $data = @unserialize($decoded, ['allowed_classes' => false]);
        } catch (\Throwable $e) {
            return null;
        }

        // Unserialize peut retourner false si corrompu (sauf cas b:0;)
        if ($data === false && $decoded !== 'b:0;') {
            return null;
        }

        return is_array($data) ? $data : null;
    }
}
