<?php

/**
 * Service servant à créer la table décrite par Storage.
 * @package     ContentBuilder
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use CB\Component\Contentbuilder_ng\Administrator\Helper\Logger;

class DatatableService
{
    /** Valide un identifiant SQL simple (table/col) */
    private function assertSafeIdentifier(string $value, string $label): string
    {
        $value = strtolower(trim($value));

        if ($value === '' || !preg_match('/^[a-z0-9_]+$/', $value)) {
            throw new \RuntimeException("$label invalide: " . $value);
        }

        return $value;
    }

    /** Retourne l’object storage (ou throw) */
    private function loadStorage(int $storageId): object
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'name', 'bytable']))
            ->from($db->quoteName('#__contentbuilder_ng_storages'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $storageId, ParameterType::INTEGER);

        $db->setQuery($query);
        $storage = $db->loadObject();

        if (!$storage) {
            throw new \RuntimeException('Storage not found: ' . $storageId);
        }

        return $storage;
    }

    /** Test robuste d’existence de table (via getTableColumns) */
    private function tableExists(string $prefixedTableName): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        try {
            $columns = $db->getTableColumns($prefixedTableName, true);

            return is_array($columns) && !empty($columns);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function createForStorage(int $storageId): bool
    {
        Logger::info("Demande de création de la table dont l'ID STORAGE vaut $storageId.");
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $storage = $this->loadStorage($storageId);

        if ((int) $storage->bytable === 1) {
            throw new \RuntimeException('bytable=1 : pas de datatable à créer (table externe).');
        }

        $name = $this->assertSafeIdentifier((string) $storage->name, 'Nom de table storage');

        $prefixed = $db->getPrefix() . $name;

        // Idempotent : si existe, on ne fait rien
        if ($this->tableExists($prefixed)) {
            Logger::info("La table '$prefixed' existe déjà dont l'ID STORAGE vaut $storageId.");
            return false;
        }

        $now = Factory::getDate()->toSql();

        // Create
        $sql = "
            CREATE TABLE " . $db->quoteName('#__' . $name) . " (
                " . $db->quoteName('id') . " INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                " . $db->quoteName('storage_id') . " INT NOT NULL DEFAULT " . (int) $storageId . ",
                " . $db->quoteName('user_id') . " INT NOT NULL DEFAULT 0,
                " . $db->quoteName('created') . " DATETIME NOT NULL DEFAULT " . $db->quote($now) . ",
                " . $db->quoteName('created_by') . " VARCHAR(255) NOT NULL DEFAULT '',
                " . $db->quoteName('modified_user_id') . " INT NOT NULL DEFAULT 0,
                " . $db->quoteName('modified') . " DATETIME NULL DEFAULT NULL,
                " . $db->quoteName('modified_by') . " VARCHAR(255) NOT NULL DEFAULT ''
            )
        ";

        $db->setQuery($sql);
        $db->execute();

        // Indexes (idempotence: MySQL ignore si déjà là ? non -> on les ajoute juste après la création)
        $tableQN = $db->quoteName('#__' . $name);

        $db->setQuery("ALTER TABLE $tableQN ADD INDEX (" . $db->quoteName('storage_id') . ")");
        $db->execute();
        $db->setQuery("ALTER TABLE $tableQN ADD INDEX (" . $db->quoteName('user_id') . ")");
        $db->execute();
        $db->setQuery("ALTER TABLE $tableQN ADD INDEX (" . $db->quoteName('created') . ")");
        $db->execute();
        $db->setQuery("ALTER TABLE $tableQN ADD INDEX (" . $db->quoteName('modified_user_id') . ")");
        $db->execute();
        $db->setQuery("ALTER TABLE $tableQN ADD INDEX (" . $db->quoteName('modified') . ")");
        $db->execute();
        return true;
    }

    /**
     * Ajoute les colonnes manquantes dans la table data à partir de #__contentbuilder_ng_storage_fields
     * Idempotent : ajoute seulement ce qui manque.
     */
    public function syncColumnsFromFields(int $storageId): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $storage = $this->loadStorage($storageId);

        if ((int) $storage->bytable === 1) {
            throw new \RuntimeException('bytable=1 : sync colonnes non applicable ici (table externe).');
        }

        $tableName = $this->assertSafeIdentifier((string) $storage->name, 'Nom de table Storage');
        $prefixed  = $db->getPrefix() . $tableName;

        if (!$this->tableExists($prefixed)) {
            throw new \RuntimeException("La table data `#__{$tableName}` n'existe pas : créez-la d'abord.");
        }

        // ✅ Colonnes système à ignorer
        $systemColumns = [
            'id', 'storage_id', 'user_id',
            'created', 'created_by',
            'modified_user_id', 'modified', 'modified_by'
        ];

        // ✅ Query Joomla standard
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__contentbuilder_ng_storage_fields'))
            ->where($db->quoteName('storage_id') . ' = :sid')
            ->bind(':sid', $storageId, ParameterType::INTEGER);

        $db->setQuery($query);
        $fieldNames = $db->loadColumn() ?: [];

        if (!$fieldNames) {
            return;
        }

        // Colonnes existantes
        $rawCols = $db->getTableColumns($prefixed, true);
        $cols = [];

        foreach ($rawCols as $colName => $colDef) {
            $safeColName = $this->assertSafeIdentifier((string) $colName, 'Nom de champ');
            $cols[$safeColName] = $colDef;
        }

        $tableQN = $db->quoteName('#__' . $tableName);

        foreach ($fieldNames as $field) {
            $field = $this->assertSafeIdentifier((string) $field, 'Nom de champ');

            // ✅ Ignore les colonnes système
            if (in_array($field, $systemColumns, true)) {
                continue;
            }

            // Déjà existante
            if (isset($cols[$field])) {
                continue;
            }

            $db->setQuery("ALTER TABLE $tableQN ADD " . $db->quoteName($field) . " TEXT NULL");
            $db->execute();
        }
    }
}
