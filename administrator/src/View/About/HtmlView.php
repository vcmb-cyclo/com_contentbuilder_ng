<?php
/**
 * @package     ContentBuilder NG
 * @author      Xavier DANO
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
*/

namespace CB\Component\Contentbuilder_ng\Administrator\View\About;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class HtmlView extends BaseHtmlView
{
    protected string $componentVersion = '';
    protected string $componentCreationDate = '';
    protected string $componentAuthor = '';
    protected array $phpLibraries = [];

    public function display($tpl = null)
    {
        if ($this->getLayout() === 'help') {
            parent::display($tpl);
            return;
        }

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
            '.icon-logo_left{
                background-image:url(' . Uri::root(true) . '/media/com_contentbuilder_ng/images/logo_left.png);
                background-size:contain;
                background-repeat:no-repeat;
                background-position:center;
                display:inline-block;
                width:48px;
                height:48px;
                vertical-align:middle;
            }'
        );

        ToolbarHelper::title(
            Text::_('COM_CONTENTBUILDER_NG') .' :: ' . Text::_('COM_CONTENTBUILDER_NG_ABOUT'),
            'logo_left'
        );
        ToolbarHelper::custom(
            'about.migratePackedData',
            'refresh',
            '',
            Text::_('COM_CONTENTBUILDER_NG_ABOUT_MIGRATE_PACKED_DATA'),
            false
        );
        ToolbarHelper::preferences('com_contentbuilder_ng');
        ToolbarHelper::help(
            'COM_CONTENTBUILDER_NG_HELP_ABOUT_TITLE',
            false,
            Uri::base() . 'index.php?option=com_contentbuilder_ng&view=about&layout=help&tmpl=component'
        );

        $versionInformation = $this->getVersionInformation();
        $this->componentVersion = (string) ($versionInformation['version'] ?? '');
        $this->componentCreationDate = (string) ($versionInformation['creationDate'] ?? '');
        $this->componentAuthor = (string) ($versionInformation['author'] ?? '');
        $this->phpLibraries = $this->getInstalledPhpLibraries();

        // 3️⃣ Affichage du layout
        parent::display($tpl);
    }

    private function getVersionInformation(): array
    {
        $versionInformation = [
            'version' => '',
            'creationDate' => '',
            'author' => '',
        ];

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_contentbuilder_ng'));

            $db->setQuery($query);
            $manifestCache = (string) $db->loadResult();

            if ($manifestCache !== '') {
                $manifestData = json_decode($manifestCache, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($manifestData)) {
                    $versionInformation['version'] = (string) ($manifestData['version'] ?? '');
                    $versionInformation['creationDate'] = (string) ($manifestData['creationDate'] ?? '');
                    $versionInformation['author'] = (string) ($manifestData['author'] ?? '');
                }
            }
        } catch (\Throwable $e) {
            // Ignore and fallback to manifest XML.
        }

        if ($versionInformation['version'] !== '') {
            return $versionInformation;
        }

        $manifestPath = JPATH_ADMINISTRATOR . '/components/com_contentbuilder_ng/com_contentbuilder_ng.xml';

        if (!is_file($manifestPath)) {
            return $versionInformation;
        }

        $manifest = simplexml_load_file($manifestPath);

        if ($manifest instanceof \SimpleXMLElement) {
            $versionInformation['version'] = (string) ($manifest->version ?? '');
            $versionInformation['creationDate'] = (string) ($manifest->creationDate ?? '');
            $versionInformation['author'] = (string) ($manifest->author ?? '');
        }

        return $versionInformation;
    }

    private function getInstalledPhpLibraries(): array
    {
        $componentRoot = JPATH_COMPONENT;
        $libraries = $this->readInstalledLibrariesFromVendor($componentRoot);

        if ($libraries === []) {
            $libraries = $this->readInstalledLibrariesFromComposerLock($componentRoot);
        }

        $this->mergeDirectRequirements($libraries, $componentRoot);

        usort(
            $libraries,
            static fn(array $a, array $b): int => strcmp($a['name'], $b['name'])
        );

        return $libraries;
    }

    private function readInstalledLibrariesFromVendor(string $componentRoot): array
    {
        $libraries = [];
        $installedPhp = $componentRoot . '/vendor/composer/installed.php';

        if (is_file($installedPhp)) {
            $installedData = include $installedPhp;

            if (is_array($installedData)) {
                $rootPackageName = (string) ($installedData['root']['name'] ?? '');
                $versions = $installedData['versions'] ?? [];

                if (is_array($versions)) {
                    foreach ($versions as $packageName => $packageData) {
                        if (!is_array($packageData)) {
                            continue;
                        }

                        if ($packageName === '__root__' || $packageName === $rootPackageName) {
                            continue;
                        }

                        $libraries[] = [
                            'name' => (string) $packageName,
                            'version' => (string) ($packageData['pretty_version'] ?? $packageData['version'] ?? ''),
                            'is_dev' => (bool) ($packageData['dev_requirement'] ?? false),
                        ];
                    }
                }
            }
        }

        $installedJson = $componentRoot . '/vendor/composer/installed.json';

        if ($libraries === [] && is_file($installedJson)) {
            $jsonData = file_get_contents($installedJson);

            if (is_string($jsonData) && $jsonData !== '') {
                $installed = json_decode($jsonData, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($installed)) {
                    $packages = $installed['packages'] ?? $installed;

                    if (is_array($packages)) {
                        foreach ($packages as $package) {
                            if (!is_array($package)) {
                                continue;
                            }

                            $packageName = (string) ($package['name'] ?? '');

                            if ($packageName === '') {
                                continue;
                            }

                            $libraries[] = [
                                'name' => $packageName,
                                'version' => (string) ($package['version'] ?? ''),
                                'is_dev' => false,
                            ];
                        }
                    }
                }
            }
        }

        return $libraries;
    }

    private function readInstalledLibrariesFromComposerLock(string $componentRoot): array
    {
        $composerLock = $componentRoot . '/composer.lock';

        if (!is_file($composerLock)) {
            return [];
        }

        $jsonData = file_get_contents($composerLock);

        if (!is_string($jsonData) || $jsonData === '') {
            return [];
        }

        $lockData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($lockData)) {
            return [];
        }

        $libraries = [];
        $runtimePackages = $lockData['packages'] ?? [];
        $devPackages = $lockData['packages-dev'] ?? [];

        if (is_array($runtimePackages)) {
            foreach ($runtimePackages as $package) {
                if (!is_array($package)) {
                    continue;
                }

                $packageName = (string) ($package['name'] ?? '');

                if ($packageName === '') {
                    continue;
                }

                $libraries[] = [
                    'name' => $packageName,
                    'version' => (string) ($package['version'] ?? ''),
                    'is_dev' => false,
                ];
            }
        }

        if (is_array($devPackages)) {
            foreach ($devPackages as $package) {
                if (!is_array($package)) {
                    continue;
                }

                $packageName = (string) ($package['name'] ?? '');

                if ($packageName === '') {
                    continue;
                }

                $libraries[] = [
                    'name' => $packageName,
                    'version' => (string) ($package['version'] ?? ''),
                    'is_dev' => true,
                ];
            }
        }

        return $libraries;
    }

    private function mergeDirectRequirements(array &$libraries, string $componentRoot): void
    {
        $composerJson = $componentRoot . '/composer.json';

        if (!is_file($composerJson)) {
            return;
        }

        $jsonData = file_get_contents($composerJson);

        if (!is_string($jsonData) || $jsonData === '') {
            return;
        }

        $composerData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($composerData)) {
            return;
        }

        $indexed = [];

        foreach ($libraries as $index => $library) {
            $name = (string) ($library['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $indexed[$name] = $index;
        }

        $this->mergeRequirementSet($libraries, $indexed, $composerData['require'] ?? [], false);
        $this->mergeRequirementSet($libraries, $indexed, $composerData['require-dev'] ?? [], true);
    }

    private function mergeRequirementSet(array &$libraries, array &$indexed, mixed $requirements, bool $isDev): void
    {
        if (!is_array($requirements)) {
            return;
        }

        foreach ($requirements as $packageName => $constraint) {
            $name = (string) $packageName;

            // Skip platform requirements like "php" and "ext-*".
            if ($name === '' || !str_contains($name, '/')) {
                continue;
            }

            $constraintValue = (string) $constraint;

            if (isset($indexed[$name])) {
                $existingIndex = $indexed[$name];

                if (($libraries[$existingIndex]['version'] ?? '') === '' && $constraintValue !== '') {
                    $libraries[$existingIndex]['version'] = $constraintValue;
                }

                continue;
            }

            $libraries[] = [
                'name' => $name,
                'version' => $constraintValue,
                'is_dev' => $isDev,
            ];
            $indexed[$name] = \count($libraries) - 1;
        }
    }
}
