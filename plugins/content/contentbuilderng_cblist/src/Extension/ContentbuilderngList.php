<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngList\Extension;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Service\EmbeddedListFieldFilterService;
use CB\Plugin\Content\ContentbuilderngList\Service\EmbedOptionsService;
use CB\Plugin\Content\ContentbuilderngList\Service\TagSyntaxService;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;

final class ContentbuilderngList extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    private static int $instance = 0;
    private static bool $assetRegistryLoaded = false;

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(EventInterface $event): void
    {
        $article = $event->getArgument('subject');

        if (
            !is_object($article)
            || !isset($article->text)
            || stripos((string) $article->text, '{CBList') === false
        ) {
            return;
        }

        $app = Factory::getApplication();
        if (
            !$app instanceof SiteApplication
            || $app->getInput()->getCmd('option') === 'com_contentbuilderng'
        ) {
            return;
        }

        $article->text = preg_replace_callback(
            TagSyntaxService::TAG_PATTERN,
            fn(array $match): string => $this->renderTag((string) ($match[1] ?? ''), $app),
            (string) $article->text
        );
    }

    private function renderTag(string $rawAttributes, SiteApplication $app): string
    {
        try {
            $options = EmbedOptionsService::resolve(TagSyntaxService::parseAttributes($rawAttributes));
        } catch (\InvalidArgumentException) {
            return '<div class="alert alert-warning" role="alert">'
                . htmlspecialchars(Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_INVALID_TAG'), ENT_QUOTES, 'UTF-8')
                . '</div>';
        }

        $query = [
            'option' => 'com_contentbuilderng',
            'task' => 'list.display',
            'id' => $options['id'],
            'tmpl' => 'component',
            'cblist_embed' => EmbeddedListFieldFilterService::REQUEST_CONTEXT,
        ];

        if ($options['layout'] !== '') {
            $query['layout'] = $options['layout'];
        }
        if ($options['fields'] !== []) {
            $query['cblist_fields'] = implode('|', $options['fields']);
        }
        if ($options['actions'] !== []) {
            $query['cblist_actions'] = implode('|', $options['actions']);
        }

        $url = Route::_('index.php?' . http_build_query($query), false);
        $title = $options['title'] !== ''
            ? $options['title']
            : Text::sprintf('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_IFRAME_TITLE', $options['id']);
        $openLabel = Text::_('PLG_CONTENT_CONTENTBUILDERNG_CBLIST_OPEN_LIST');
        $instanceId = 'cblist-frame-' . (++self::$instance);

        $this->loadAssets($app);

        return '<div class="cblist-embed">'
            . '<iframe'
            . ' id="' . $instanceId . '"'
            . ' class="cblist-embed__frame"'
            . ' src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
            . ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"'
            . ' loading="' . $options['loading'] . '"'
            . ' data-cblist-min-height="' . $options['height'] . '"'
            . ' style="height:' . $options['height'] . 'px"'
            . '></iframe>'
            . '<noscript><p><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($openLabel, ENT_QUOTES, 'UTF-8')
            . '</a></p></noscript>'
            . '</div>';
    }

    private function loadAssets(SiteApplication $app): void
    {
        $assets = $this->getWebAssetManager($app);
        $assets->usePreset('plg_content_contentbuilderng_cblist.embed');
    }

    private function getWebAssetManager(SiteApplication $app): WebAssetManager
    {
        $assets = $app->getDocument()->getWebAssetManager();

        if (!self::$assetRegistryLoaded) {
            $assets->getRegistry()->addRegistryFile(
                'media/plg_content_contentbuilderng_cblist/joomla.asset.json'
            );
            self::$assetRegistryLoaded = true;
        }

        return $assets;
    }
}
