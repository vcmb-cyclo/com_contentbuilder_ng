<?php

/**
 * @package     ContentBuilder
 * @author      XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Filesystem\File;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Log\Log;

class com_contentbuilderInstallerScript extends InstallerScript
{
  protected $minimumPhp = '8.1';
  protected $minimumJoomla = '5.0';

  public function __construct()
  {
    // Logger personnalisé
    Log::addLogger(
      [
        'text_file' => 'contentbuilder_install.log',
        'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}',
        'text_file_path'     => JPATH_ADMINISTRATOR . '/logs'
      ],
      Log::ALL,
      ['com_contentbuilder.install']
    );


    // Starting logs.
    Log::add('---------------------------------------------------------', Log::INFO, 'com_contentbuilder.install');
    Log::add('[OK] ContentBuilder installation/update started.', Log::INFO, 'com_contentbuilder.install');
    Log::add('* PHP Version: ' . PHP_VERSION . '.', Log::INFO, 'com_contentbuilder.install');
    Log::add('* Joomla Version : ' . JVERSION . '.', Log::INFO, 'com_contentbuilder.install');
    Log::add('* User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI') . '.', Log::INFO, 'com_contentbuilder.install');
  }

  function getPlugins()
  {
    $plugins = array();
    $plugins['contentbuilder_verify'] = array();
    $plugins['contentbuilder_verify'][] = 'paypal';
    $plugins['contentbuilder_verify'][] = 'passthrough';
    $plugins['contentbuilder_validation'] = array();
    $plugins['contentbuilder_validation'][] = 'notempty';
    $plugins['contentbuilder_validation'][] = 'equal';
    $plugins['contentbuilder_validation'][] = 'email';
    $plugins['contentbuilder_validation'][] = 'date_not_before';
    $plugins['contentbuilder_validation'][] = 'date_is_valid';
    $plugins['contentbuilder_themes'] = array();
    $plugins['contentbuilder_themes'][] = 'khepri';
    $plugins['contentbuilder_themes'][] = 'blank';
    $plugins['contentbuilder_themes'][] = 'joomla3';
    $plugins['contentbuilder_themes'][] = 'joomla6';
    $plugins['system'] = array();
    $plugins['system'][] = 'contentbuilder_system';
    $plugins['contentbuilder_submit'] = array();
    $plugins['contentbuilder_submit'][] = 'submit_sample';
    $plugins['contentbuilder_listaction'] = array();
    $plugins['contentbuilder_listaction'][] = 'trash';
    $plugins['contentbuilder_listaction'][] = 'untrash';
    $plugins['content'] = array();
    $plugins['content'][] = 'contentbuilder_verify';
    $plugins['content'][] = 'contentbuilder_permission_observer';
    $plugins['content'][] = 'contentbuilder_image_scale';
    $plugins['content'][] = 'contentbuilder_download';
    $plugins['content'][] = 'contentbuilder_rating';
    return $plugins;
  }

  private function log(string $message, int $priority = Log::INFO): void
  {
    Log::add($message, $priority, 'com_contentbuilder.install');
  }

  private function getCurrentInstalledVersion(): string
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $query = $db->getQuery(true)
      ->select($db->quoteName('manifest_cache'))
      ->from($db->quoteName('#__extensions'))
      ->where($db->quoteName('element') . ' = ' . $db->quote('com_contentbuilder'));

    $db->setQuery($query);
    $manifest = $db->loadResult();

    if ($manifest) {
      $manifest = json_decode($manifest, true);
      $version = $manifest['version'] ?? '0.0.0';
    } else {
      $version = '0.0.0';
    }

    return $version;
  }


  function installAndUpdate(): bool
  {
/*    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $plugins = $this->getPlugins();
    $base_path = JPATH_SITE . '/administrator/components/com_contentbuilder/plugins';
    $folders = Folder::folders($base_path);

    foreach ($folders as $folder) {
      $installer = new Installer();
      $installer->setDatabase(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver'));

      Factory::getApplication()->enqueueMessage('Installing plugin <b>' . $folder . '</b>', 'message');
      $success = $installer->install($base_path . '/' . $folder);
      if (!$success) {
        Factory::getApplication()->enqueueMessage('Install failed for plugin <b>' . $folder . '</b>', 'error');
      }
    }*/

    // Publication des plugins.
    /*
    foreach ($plugins as $folder => $subplugs) {
      foreach ($subplugs as $plugin) {
        $query = 'UPDATE #__extensions SET `enabled` = 1 WHERE `type` = "plugin" AND `element` = ' . $db->quote($plugin) . ' AND `folder` = ' . $db->quote($folder);
        $db->setQuery($query);
        $db->execute();
        $this->log("Plugin {$plugin} in folder {$folder} enabled.");
        Factory::getApplication()->enqueueMessage('Published plugin <b>' . $plugin . '</b>', 'message');
      }
    }*/

    return true;
  }

  /**
   * method to install the component
   *
   * @return bool
   */
  public function install(InstallerAdapter $parent): bool
  {
    if (!version_compare(PHP_VERSION, '8.1', '>=')) {
      Factory::getApplication()->enqueueMessage('"WARNING: YOU ARE RUNNING PHP VERSION "' . PHP_VERSION . '". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 8.1, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!"', 'error');
    }


    return $this->installAndUpdate();
  }

  /**
   * method to update the component
   *
   * @return bool
   */
  public function update(InstallerAdapter $parent): bool
  {
    if (!version_compare(PHP_VERSION, '8.1', '>=')) {
      Factory::getApplication()->enqueueMessage('"WARNING: YOU ARE RUNNING PHP VERSION "' . PHP_VERSION . '". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 8.1, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!"', 'error');
    }

    return $this->installAndUpdate();
  }

  /**
   * method to uninstall the component
   *
   * @return bool
   */
  public function uninstall(InstallerAdapter $parent): bool
  {
    $this->log('Uninstall of ContentBuilder.');

    $db = Factory::getContainer()->get(DatabaseInterface::class);

    $db->setQuery("DELETE FROM #__menu WHERE `link` LIKE 'index.php?option=com_contentbuilder%'");
    $db->execute();

    $plugins = $this->getPlugins();
    $installer = new Installer();
    $installer->setDatabase(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver'));

    foreach ($plugins as $folder => $subplugs) {
      foreach ($subplugs as $plugin) {
        $query = 'SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = ' . $db->quote($plugin) . ' AND `folder` = ' . $db->quote($folder);
        $db->setQuery($query);
        $id = $db->loadResult();

        if ($id) {
          $installer->uninstall('plugin', $id, 1);
        }
      }
    }

    $db->setQuery("SELECT id FROM `#__menu` WHERE `alias` = 'root'");
    if (!$db->loadResult()) {
      $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, (SELECT MAX(mlft.rgt)+1 FROM #__menu AS mlft), 0, '*', 0)");
      $db->execute();
    }

    return true;
  }

  /**
   * method to run before an install/update/uninstall method
   *
   * @return bool
   */
  public function preflight($type, $parent): bool
  {

    if (!parent::preflight($type, $parent)) {
      return false;
    }

    $db = Factory::getContainer()->get(DatabaseInterface::class);

    // === LOG POUR DÉBOGAGE ===
    $this->log('Preflight installation method call, parameter : ' . $type . '.');
    $this->log('[OK] Detected current version in manifest_cache : ' . $this->getCurrentInstalledVersion() . '.');

    $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
    if (!$db->loadResult()) {
      $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
      $db->execute();
    }

    return true;
  }


  /**
   * method to remove old librairies and files.
   *
   * @return void
   */
  private function removeOldLibraries(): void
  {
    $paths = [
      JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/PHPExcel',
      JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/PHPExcel.php',
      JPATH_ADMINISTRATOR . '/components/com_contentbuilder/librairies/PhpSpreadsheet',
    ];

    $app = Factory::getApplication();

    foreach ($paths as $path) {
      if (Folder::exists($path)) {
        if (Folder::delete($path)) {
          $this->log("[OK] Old {$path} folder successfully deleted.");
          $app->enqueueMessage("[OK] Old {$path} folder successfully deleted.", 'message');
        } else {
          $this->log("[ERROR] Failed to delete {$path} folder.", Log::ERROR);
          $app->enqueueMessage("[ERROR] Failed to delete {$path} folder.", 'warning');
        }
      } elseif (File::exists($path)) {
        if (File::delete($path)) {
          $this->log("[OK] Old {$path} file successfully deleted.");
          $app->enqueueMessage("[OK] Old {$path} file successfully deleted.", 'message');
        } else {
          $this->log("[ERROR] Failed to delete {$path} file.", Log::ERROR);
          $app->enqueueMessage("[ERROR] Failed to delete {$path} file.", 'warning');
        }
      } else {
        $this->log("[OK] No previous {$path} found.");
      }
    }
  }

  private function removeObsoleteFiles(): void
  {
    $paths = [
      JPATH_ADMINISTRATOR . '/components/com_contentbuilder/src/Model/EditModel.php',
    ];

    $app = Factory::getApplication();

    foreach ($paths as $path) {
      if (File::exists($path)) {
        if (File::delete($path)) {
          $this->log("[OK] Removed obsolete file {$path}.");
          $app->enqueueMessage("[OK] Removed obsolete file {$path}.", 'message');
        } else {
          $this->log("[ERROR] Failed to remove obsolete file {$path}.", Log::ERROR);
          $app->enqueueMessage("[ERROR] Failed to remove obsolete file {$path}.", 'warning');
        }
      } else {
        $this->log("[OK] Obsolete file {$path} not found.");
      }
    }
  }

  /**
   * method to change the DATE default value for strict MySQL databases.
   *
   * @return void
   */

  private function updateDateColumns(): void
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $alterQueries = [
      // Table #__contentbuilder_forms
      "ALTER TABLE `#__contentbuilder_forms` MODIFY `created` DATETIME NULL DEFAULT CURRENT_TIMESTAMP",
      "UPDATE `#__contentbuilder_forms` SET `created` = NULL WHERE `created` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `modified` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `modified` = NULL WHERE `modified` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_forms` MODIFY `rand_date_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_forms` SET `rand_date_update` = NULL WHERE `rand_date_update` = '0000-00-00'",

      // Table #__contentbuilder_records
      "ALTER TABLE `#__contentbuilder_records` MODIFY `publish_up` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `publish_down` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_records` MODIFY `rand_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_records` SET `rand_date` = NULL WHERE `rand_date` = '0000-00-00'",

      // Table #__contentbuilder_articles (si présent)
      "ALTER TABLE `#__contentbuilder_articles` MODIFY `last_update` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_articles` SET `last_update` = NULL WHERE `last_update` = '0000-00-00'",

      // Table #__contentbuilder_users (dates de vérification)
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_view` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_view` = NULL WHERE `verification_date_view` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_new` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_new` = NULL WHERE `verification_date_new` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_users` MODIFY `verification_date_edit` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_users` SET `verification_date_edit` = NULL WHERE `verification_date_edit` = '0000-00-00'",

      "ALTER TABLE `#__contentbuilder_rating_cache` MODIFY COLUMN `date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_rating_cache` SET `date` = NULL WHERE `date` = '0000-00-00'",

      // Table #__contentbuilder_verifications
      "ALTER TABLE `#__contentbuilder_verifications` MODIFY `start_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_verifications` SET `start_date` = NULL WHERE `start_date` = '0000-00-00'",
      "ALTER TABLE `#__contentbuilder_verifications` MODIFY `verification_date` DATETIME NULL DEFAULT NULL",
      "UPDATE `#__contentbuilder_verifications` SET `verification_date` = NULL WHERE `verification_date` = '0000-00-00'"
    ];

    foreach ($alterQueries as $query) {
      try {
        $db->setQuery($query)->execute();
      } catch (\Exception $e) {
        // Silencieux si la colonne est déjà correcte ou table inexistante
        $msg = '[WARNING] Could not alter date column: ' . $e->getMessage() . '.';
        $this->log($msg, Log::WARNING);
        Factory::getApplication()->enqueueMessage($msg, 'warning');
      }
    }

    $msg = '[OK] Date fields updated to support NULL correctly, if necessary.';
    $this->log($msg);
    Factory::getApplication()->enqueueMessage($msg, 'message');
  }


  private function activatePlugins(): void
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    
    // Active les plugins fournis par le package (Joomla 4/5/6)
    $plugins = $this->getPlugins();

    foreach ($plugins as $folder => $elements) {
        foreach ($elements as $element) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));

            try {
                $db->setQuery($query)->execute();
                $this->log("[OK] Plugin enabled: {$folder}/{$element}");
                Factory::getApplication()->enqueueMessage("[OK] Plugin enabled: {$folder}/{$element}", 'info');
            } catch (\Throwable $e) {
                $this->log("[ERROR] Failed enabling {$folder}/{$element}: " . $e->getMessage(), Log::ERROR);
                Factory::getApplication()->enqueueMessage("[ERROR] Failed enabling {$folder}/{$element}: " . $e->getMessage(), 'error');
            }
        }
    }
  }

  private function ensurePluginsInstalled(?string $source = null): void
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $installer = new Installer();
    $installer->setDatabase(Factory::getContainer()->get('DatabaseDriver'));

    $plugins = $this->getPlugins();

    foreach ($plugins as $folder => $elements) {
      foreach ($elements as $element) {
        $query = $db->getQuery(true)
          ->select($db->quoteName('extension_id'))
          ->from($db->quoteName('#__extensions'))
          ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
          ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
          ->where($db->quoteName('element') . ' = ' . $db->quote($element));

        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
          continue;
        }

        $path = null;
        if ($source) {
          $candidate = rtrim($source, '/') . '/plugins/' . $folder . '/' . $element;
          if (is_dir($candidate)) {
            $path = $candidate;
          }
        }
        if (!$path) {
          $path = JPATH_ROOT . '/plugins/' . $folder . '/' . $element;
        }
        if (!is_dir($path)) {
          $this->log("[WARNING] Plugin folder not found: {$path}", Log::WARNING);
          Factory::getApplication()->enqueueMessage("[WARNING] Plugin folder not found: {$path}", 'warning');
          continue;
        }

        $ok = $installer->install($path);
        if ($ok) {
          $this->log("[OK] Plugin installed: {$folder}/{$element}");
          Factory::getApplication()->enqueueMessage("[OK] Plugin installed: {$folder}/{$element}", 'message');
        } else {
          $this->log("[ERROR] Plugin install failed: {$folder}/{$element}", Log::ERROR);
          Factory::getApplication()->enqueueMessage("[ERROR] Plugin install failed: {$folder}/{$element}", 'error');
        }
      }
    }
  }

  /**
   * Method to run after an install/update/uninstall method
   *
   * @return void
   */
  function postflight($type, $parent)
  {
    $db = Factory::getContainer()->get(DatabaseInterface::class);

    // === LOG POUR DÉBOGAGE ===
    $this->log('Postflight installation method call, parameter : ' . $type . '.');

    /*
             $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
             if(!$db->loadResult()){
                 $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, NULL, 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                 $db->execute();
             }*/

    $db->setQuery("Update #__menu Set `title` = 'COM_CONTENTBUILDER' Where `alias`='contentbuilder'");
    $db->execute();

    $this->removeOldLibraries();
    $this->removeObsoleteFiles();
    $this->updateDateColumns();
    $source = null;
    if (is_object($parent) && method_exists($parent, 'getParent')) {
      $parentInstaller = $parent->getParent();
      if ($parentInstaller && method_exists($parentInstaller, 'getPath')) {
        $source = $parentInstaller->getPath('source');
      }
    }
    $this->ensurePluginsInstalled($source);
    $this->activatePlugins();


    // On ne fait ça que sur update (et éventuellement discover_install si tu veux)
    if ($type !== 'update') {
      return;
    }

    $table = $db->quoteName('#__contentbuilder_storages');

    // Vérifie l’existence de la table
    try {
      $tables = $db->getTableList();
      $expected = $db->getPrefix() . 'contentbuilder_storages';

      if (!in_array($expected, $tables, true)) {
        return; // table pas présente => rien à faire
      }
    } catch (\Throwable $e) {
      // Si getTableList foire sur un driver, on tente quand même.
    }

    // Y a-t-il des ordering à 0 ?
    $db->setQuery("SELECT COUNT(*) FROM $table WHERE ordering = 0");
    $needFix = (int) $db->loadResult();

    if ($needFix === 0) {
      return;
    }

    // Max ordering existant (si tout est à 0, max = 0)
    $db->setQuery("SELECT COALESCE(MAX(ordering), 0) FROM $table");
    $max = (int) $db->loadResult();

    // IDs à réparer (ordering = 0)
    $db->setQuery("SELECT id FROM $table WHERE ordering = 0 ORDER BY id");
    $ids = $db->loadColumn() ?: [];

    // Mise à jour séquentielle
    $order = $max;
    foreach ($ids as $id) {
      $order++;

      $db->setQuery(
        "UPDATE $table SET ordering = " . (int) $order . " WHERE id = " . (int) $id
      );
      $db->execute();
    }
    // try to restore the main menu items if they got lost
    /*
    $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilder' And parent_id = 1");
    $result = $db->loadResult();

    if(!$result) {
        
        $db->setQuery("Select extension_id From #__extensions Where `type` = 'component' And `element` = 'com_contentbuilder'");
        $comp_id = $db->loadResult();
        
        if($comp_id){
            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER', 'contentbuilder', '', 'contentbuilder', 'index.php?option=com_contentbuilder', 'component', 0, 1, 1, ".$comp_id.", 0, NULL, 0, 1, 'media/com_contentbuilder/images/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();
            $parent_id = $db->insertid();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER_STORAGES', 'comcontentbuilderstorages', '', 'contentbuilder/comcontentbuilderstorages', 'index.php?option=com_contentbuilder&task=storages.display', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'media/com_contentbuilder/images/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_LIST', 'comcontentbuilderlist', '', 'contentbuilder/comcontentbuilderlist', 'index.php?option=com_contentbuilder&task=forms.display', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'media/com_contentbuilder/images/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'Try BreezingForms!', 'try-breezingforms', '', 'contentbuilder/try-breezingforms', 'index.php?option=com_contentbuilder&view=contentbuilder&market=true', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_ABOUT', 'comcontentbuilderabout', '', 'contentbuilder/comcontentbuilderabout', 'index.php?option=com_contentbuilder&view=contentbuilder', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, NULL, 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
            $db->execute();

            $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
            $rgt = $db->loadResult();

            $db->setQuery("Update `#__menu` Set rgt = ".$rgt." Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
            $db->execute();
        }
    }*/

    $this->log('[OK] Contentbuilder installation finished.');
  }
}
