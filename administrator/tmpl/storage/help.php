<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input = Factory::getApplication()->input;
$storageId = (int) $input->getInt('id', 0);
$backToEdit = Route::_('index.php?option=com_contentbuilder_ng&view=storage&layout=edit&id=' . $storageId);
$backToList = Route::_('index.php?option=com_contentbuilder_ng&view=storages');
?>
<div class="container-fluid p-3">
    <h1 class="h3 mb-3"><?php echo Text::_('COM_CONTENTBUILDER_NG_HELP_STORAGES_TITLE'); ?></h1>
    <p class="text-muted mb-4">
        Cet ecran permet de configurer une source de donnees (Storage), ses champs, et la synchronisation avec la table SQL.
        Utilisez cette aide comme check-list rapide pour eviter les erreurs de structure.
    </p>

    <div class="alert alert-info mb-4">
        <strong>Resume:</strong> un Storage decrit la table, les colonnes exposees et les operations de maintenance (creation / synchro / import CSV).
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">1) Parametres principaux</h2>
                    <ul class="mb-0">
                        <li><strong>Name / table:</strong> nom technique de la table SQL.</li>
                        <li><strong>Title:</strong> libelle fonctionnel affiche dans l'administration.</li>
                        <li><strong>Mode interne/externe:</strong> interne = table geree par CB NG, externe = table existante.</li>
                        <li><strong>Published:</strong> active ou desactive le storage.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">2) Barre d'outils</h2>
                    <ul class="mb-0">
                        <li><strong>Save / Save &amp; New:</strong> enregistre la configuration.</li>
                        <li><strong>Save:</strong> cree/renomme automatiquement la table SQL en mode interne.</li>
                        <li><strong>Datatable Sync:</strong> aligne la structure SQL sur les champs declares.</li>
                        <li><strong>Delete fields:</strong> supprime les champs selectionnes dans la liste.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">3) Gestion des champs</h2>
                    <ul class="mb-0">
                        <li>Ajoutez un champ avec un <strong>nom SQL stable</strong> (evitez les changements frequents).</li>
                        <li>Definissez un <strong>label clair</strong> pour l'administration.</li>
                        <li>Utilisez les options de groupe uniquement si necessaire.</li>
                        <li>Avant production, validez que les types de champs correspondent aux donnees reelles.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">4) Import CSV</h2>
                    <ul class="mb-0">
                        <li>Controlez le separateur et l'encodage avant import.</li>
                        <li><strong>Drop records</strong> vide les donnees existantes avant import.</li>
                        <li><strong>Published</strong> applique l'etat publie/non publie aux lignes importees.</li>
                        <li>Testez d'abord avec un petit echantillon.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-warning mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Erreurs frequentes et prevention</h2>
            <ul class="mb-0">
                <li><strong>Renommage de table:</strong> faire une sauvegarde SQL avant modification.</li>
                <li><strong>Incoherence champs/table:</strong> executer Datatable Sync apres tout changement de structure.</li>
                <li><strong>Import incomplet:</strong> verifier delimitateur CSV, encodage et colonnes attendues.</li>
                <li><strong>Conflits de nom:</strong> ne pas reutiliser un nom de colonne deja present avec un autre type.</li>
            </ul>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <?php if ($storageId > 0): ?>
            <a class="btn btn-success btn-sm" href="<?php echo $backToEdit; ?>">
                Retour au storage courant
            </a>
        <?php endif; ?>
        <a class="btn btn-primary btn-sm" href="<?php echo $backToList; ?>">
            <?php echo Text::_('COM_CONTENTBUILDER_NG_HELP_BACK_TO_STORAGES'); ?>
        </a>
    </div>
</div>
