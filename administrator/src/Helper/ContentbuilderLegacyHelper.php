<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms-ng.vcmb.fr
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright   Copyright © 2026 by XDA+GIL
 */


namespace CB\Component\Contentbuilderng\Administrator\Helper;

// No direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use CB\Component\Contentbuilderng\Administrator\Helper\Logger;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\FormSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PathService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;

final class ContentbuilderLegacyHelper
{
    private static function cleanComponentCaches(array $groups, array $options = []): void
    {
        $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        foreach ($groups as $group) {
            $cacheOptions = $options;
            $cacheOptions['defaultgroup'] = $group;
            $cacheFactory->createCacheController('callback', $cacheOptions)->clean();
        }
    }

    private static function startsWithIgnoreCase(string $value, string $prefix): bool
    {
        return strncasecmp($value, $prefix, strlen($prefix)) === 0;
    }

    private static function normalizePathSeparators(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        return $path;
    }

    private static function helperClassName(): string
    {
        return __NAMESPACE__ . '\\ContentbuilderngHelper';
    }

    private static function callContentbuilderngHelper(string $method, ...$arguments)
    {
        $helperClass = self::helperClassName();

        if (!class_exists($helperClass) || !method_exists($helperClass, $method)) {
            throw new \RuntimeException('Missing ContentbuilderngHelper::' . $method);
        }

        return $helperClass::$method(...$arguments);
    }

    private static function containsIncompleteClass($value): bool
    {
        if ($value instanceof \__PHP_Incomplete_Class) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::containsIncompleteClass($item)) {
                    return true;
                }
            }
        } elseif (is_object($value)) {
            foreach ((array) $value as $item) {
                if (self::containsIncompleteClass($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Decode base64 packed payload.
     * New format: base64("j:" + json)
     * Legacy format: base64(serialize(...))
     */
    public static function decodePackedData($raw, $default = null, bool $assoc = false)
    {
        if (!class_exists(PackedDataHelper::class)) {
            require_once __DIR__ . '/PackedDataHelper.php';
        }

        return PackedDataHelper::decodePackedData($raw, $default, $assoc);
    }

    /**
     * Encode payload to base64 JSON (prefixed with j:).
     * Falls back to legacy serialize() if JSON encoding fails.
     */
    public static function encodePackedData($value): string
    {
        if (!class_exists(PackedDataHelper::class)) {
            require_once __DIR__ . '/PackedDataHelper.php';
        }

        return PackedDataHelper::encodePackedData($value);
    }

    /**
     * Resolve "hidden filter" values safely without eval().
     * Supports only known dynamic tokens.
     */
    public static function sanitizeHiddenFilterValue(string $value): string
    {
        $value = trim(str_replace(["\r", "\n"], '', $value));

        if ($value === '') {
            return '';
        }

        if (self::startsWithIgnoreCase($value, '$value') || self::startsWithIgnoreCase($value, '<?php')) {
            Log::add(
                'Blocked legacy PHP expression in hidden filter value.',
                Log::WARNING,
                'com_contentbuilderng'
            );
            return '';
        }

        $identity = Factory::getApplication()->getIdentity();
        $now = Factory::getDate();
        $identityId = is_object($identity) && method_exists($identity, 'get')
            ? (int) $identity->get('id', 0)
            : (int) ($identity->id ?? 0);
        $identityUsername = is_object($identity) && method_exists($identity, 'get')
            ? (string) $identity->get('username', 'anonymous')
            : (string) ($identity->username ?? 'anonymous');
        $identityName = is_object($identity) && method_exists($identity, 'get')
            ? (string) $identity->get('name', 'Anonymous')
            : (string) ($identity->name ?? 'Anonymous');
        $replacements = [
            '{userid}' => (string) $identityId,
            '{username}' => $identityUsername,
            '{name}' => $identityName,
            '{date}' => (string) $now->toSql(),
            '{time}' => (string) $now->format('H:i:s'),
            '{datetime}' => (string) $now->format('Y-m-d H:i:s'),
        ];

        return strtr($value, $replacements);
    }

    public static function makeSafeFolder($path)
    {
        if (!class_exists(PathService::class)) {
            require_once dirname(__DIR__) . '/Service/PathService.php';
        }

        if (method_exists(Factory::class, 'getContainer')) {
            try {
                return Factory::getContainer()->get(PathService::class)->makeSafeFolder($path);
            } catch (\Throwable $e) {
            }
        }

        return (new PathService())->makeSafeFolder($path);
    }

    public static function getPagination($limitstart, $limit, $total)
    {
        $pages_total = 0;
        $pages_current = 0;

        if ($limit > $total) {
            $limitstart = 0;
        }

        if ($limit < 1) {
            $limit = $total;
            $limitstart = 0;
        }

        if ($limitstart > $total - $limit) {
            $limitstart = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        if ($limit > 0) {
            $pages_total = ceil($total / $limit);
            $pages_current = ceil(($limitstart + 1) / $limit);
        }

        $url = Uri::getInstance()->toString();
        $query = Uri::getInstance()->getQuery(true);
        if (isset($query['start'])) {
            unset($query['start']);
        }
        if (count($expl_url = explode('?', $url)) > 1) {
            $impl = '';
            foreach ($query as $key => $value) {
                $impl .= $key . '=' . $value . '&';
            }
            $impl = trim($impl, '&');
            $url = $expl_url[0] . '?' . $impl;
        }

        $open = Route::_($url . (strstr($url, '?') !== false ? '&' : '?'));
        $end = '';
        $begin = '';
        $disp = $limit;

        if (!is_int($limit / 2)) {
            $disp = 10;
        }

        $start = $pages_current - ($disp / 2);
        if ($start < 1) {
            $start = 1;
        }

        $stop = $pages_total;

        if (($start + $disp) > $pages_total) {
            $stop = $pages_total;
            if ($pages_total < $disp) {
                $start = 1;
            } else {
                $start = $pages_total - $disp + 1;
                $begin = '<li><span class="pagenav">...</span></li>';
            }
        } else {
            if ($start > 1) {
                $begin = '<li><span class="pagenav">...</span></li>';
            }
            $stop = $start + $disp - 1;
            $end = '<li><span class="pagenav">...</span></li>';
        }

        $c = '';

        if ($pages_total > 1) {
            ob_start();
            ?>
            <div class="pagination">
                <ul>
                    <li class="pagination-start">
                        <?php echo $pages_current - 1 > 0 ? '<a title="' . Text::_('COM_CONTENTBUILDERNG_START') . '" href="' . $open . '" class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_START') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_START') . '</span>'; ?>
                    </li>
                    <li class="pagination-prev">
                        <?php echo $pages_current - 1 > 0 ? '<a title="' . Text::_('COM_CONTENTBUILDERNG_PREV') . '" href="' . $open . 'start=' . ($limitstart - $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_PREV') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_PREV') . '</span>'; ?>
                    </li>
                    <?php echo $begin; ?>
                    <?php
                    for ($i = $start; $i <= $stop; $i++) {
                        if ($i != $pages_current) {
                            ?>
                            <li><a title="<?php echo $i; ?>" href="<?php echo $open; ?>start=<?php echo ($i - 1) * $limit; ?>"
                                    class="pagenav">
                                    <?php echo $i; ?>
                                </a></li>
                            <?php
                        } else {
                            ?>
                            <li><span class="pagenav">
                                    <?php echo $i; ?>
                                </span></li>
                            <?php
                        }
                    }
                    ?>
                    <?php echo $end; ?>
                    <li class="pagination-next">
                        <?php echo $pages_current < $pages_total ? '<a title="' . Text::_('COM_CONTENTBUILDERNG_NEXT') . '" href="' . $open . 'start=' . ($pages_current * $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_NEXT') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_NEXT') . '</span>'; ?>
                    </li>
                    <li class="pagination-end">
                        <?php echo $pages_total > 1 && $pages_current < $pages_total ? '<a title="' . Text::_('COM_CONTENTBUILDERNG_END') . '" href="' . $open . 'start=' . (($pages_total - 1) * $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_END') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDERNG_END') . '</span>'; ?>
                    </li>
                </ul>
            </div>
            <?php
            $c = ob_get_contents();
            ob_end_clean();
        }

        return $c;
    }

    /**
     * @deprecated 6.1.1 Use RatingHelper::getRating() instead.
     */
    public static function getRating($form_id, $record_id, $colRating, $rating_slots, $lang, $rating_allowed, $rating_count, $rating_sum)
    {
        return RatingHelper::getRating(
            $form_id,
            $record_id,
            $colRating,
            $rating_slots,
            $lang,
            $rating_allowed,
            $rating_count,
            $rating_sum
        );
    }

    public static function execPhpValue($code)
    {
        return self::sanitizeHiddenFilterValue((string) $code);
    }

    public static function execPhp($result)
    {
        $value = $result;
        if (strpos(trim($result), '<?php') === 0) {

            $code = trim($result);

            if (function_exists('mb_strlen')) {
                $p1 = 0;
                $l = mb_strlen($code);
                $c = '';
                $n = 0;
                while ($p1 < $l) {
                    $p2 = mb_strpos($code, '<?php', $p1);
                    if ($p2 === false)
                        $p2 = $l;
                    $c .= mb_substr($code, $p1, $p2 - $p1);
                    $p1 = $p2;
                    if ($p1 < $l) {
                        $p1 += 5;
                        $p2 = mb_strpos($code, '?>', $p1);
                        if ($p2 === false)
                            $p2 = $l;
                        $n++;
                        $c .= eval(mb_substr($code, $p1, $p2 - $p1));
                        $p1 = $p2 + 2;
                    } // if
                } // while
            } else {
                $p1 = 0;
                $l = strlen($code);
                $c = '';
                $n = 0;
                while ($p1 < $l) {
                    $p2 = strpos($code, '<?php', $p1);
                    if ($p2 === false)
                        $p2 = $l;
                    $c .= substr($code, $p1, $p2 - $p1);
                    $p1 = $p2;
                    if ($p1 < $l) {
                        $p1 += 5;
                        $p2 = strpos($code, '?>', $p1);
                        if ($p2 === false)
                            $p2 = $l;
                        $n++;
                        $c .= eval(substr($code, $p1, $p2 - $p1));
                        $p1 = $p2 + 2;
                    } // if
                } // while
            }
        }

        return $value;
    }

    public static function createBackendMenuItem($contentbuilderng_form_id, $name, $update)
    {

        self::createBackendMenuItem3($contentbuilderng_form_id, $name, $update);
    }

    public static function getLanguageCodes()
    {
        return (new FormSupportService(new PathService()))->getLanguageCodes();
    }

    public static function applyItemWrappers($contentbuilderng_form_id, array $items, $form)
    {

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $article = new \Joomla\CMS\Table\Content($db);
        $registry = null;
        $onContentPrepare = '';

        $onContentPrepare = 'onContentPrepare';
        $registry = new Registry;
        $registry->loadString('{}');

        $db->setQuery("Select reference_id, item_wrapper, wordwrap, `label`, `options` From #__contentbuilderng_elements Where published = 1 And form_id = " . intval($contentbuilderng_form_id));
        $wrappers = $db->loadAssocList();
        foreach ($wrappers as $wrapper) {
            foreach ($items as $item) {
                foreach ($item as $key => $value) {
                    if ($key == 'col' . $wrapper['reference_id']) {
                        $new_value = '';

                        if (strpos(trim($wrapper['item_wrapper'] ?? ''), '$') === 0) {

                            $article->id = 0;

                            $w = explode('$', $wrapper['item_wrapper'], 2);
                            if (count($w) != 2) {
                                break;
                            }

                            $w = explode('$', implode('$', $w));

                            if (count($w) == 2) {
                                PluginHelper::importPlugin('content');
                            } else {
                                $size = count($w) - 1;
                                for ($j = 0; $j < $size; $j++) {
                                    \Joomla\CMS\Plugin\PluginHelper::importPlugin('content', $w[$j]);
                                }
                            }

                            $article->text = trim($w[count($w) - 1]) ? trim($w[count($w) - 1]) : $value;
                            $article->text = str_replace('{value_inline}', $value, $article->text);
                            $recc = new \stdClass();
                            $recc->recName = $wrapper['label'];
                            $recc->recValue = $value;
                            $recc->recElementId = $wrapper['reference_id'];
                            $recc->colRecord = $item->colRecord;

                            $dispatcher = Factory::getApplication()->getDispatcher();
                            $dispatcher->dispatch($onContentPrepare, new ContentPrepareEvent($onContentPrepare, array('com_content.article', &$article, &$registry, 0, true, $form, $recc)));
                            $dispatcher->clearListeners($onContentPrepare);

                            if ($article->text != $w[count($w) - 1]) {
                                $item->$key = $article->text;
                                break;
                            } else {
                                $item->$key = '';
                                break;
                            }
                        }

                        $allow_html = false;
                        $options = self::decodePackedData($wrapper['options'], null, false);

                        if ($options instanceof \stdClass) {
                            if (isset($options->allow_html) && $options->allow_html) {
                                $allow_html = true;
                            }
                        }

                        if ($wrapper['wordwrap'] && !$allow_html) {
                            $new_value = self::allhtmlentities(self::callContentbuilderngHelper('contentbuilderng_wordwrap', self::callContentbuilderngHelper('cbinternal', $value), $wrapper['wordwrap'], "\n", true));
                        } else {
                            $new_value = $allow_html ? self::cleanString(self::callContentbuilderngHelper('cbinternal', $value)) : self::allhtmlentities(self::callContentbuilderngHelper('cbinternal', $value));
                        }

                        if (strpos(trim($wrapper['item_wrapper'] ?? ''), '<?php') === 0) {
                            $value = $new_value;
                            $code = trim($wrapper['item_wrapper'] ?? '');
                            if (function_exists('mb_strlen')) {
                                $p1 = 0;
                                $l = mb_strlen($code);
                                $c = '';
                                $n = 0;
                                while ($p1 < $l) {
                                    $p2 = mb_strpos($code, '<?php', $p1);
                                    if ($p2 === false)
                                        $p2 = $l;
                                    $c .= mb_substr($code, $p1, $p2 - $p1);
                                    $p1 = $p2;
                                    if ($p1 < $l) {
                                        $p1 += 5;
                                        $p2 = mb_strpos($code, '?>', $p1);
                                        if ($p2 === false)
                                            $p2 = $l;
                                        $n++;
                                        $c .= eval(mb_substr($code, $p1, $p2 - $p1));
                                        $p1 = $p2 + 2;
                                    } // if
                                } // while
                            } else {
                                $p1 = 0;
                                $l = strlen($code);
                                $c = '';
                                $n = 0;
                                while ($p1 < $l) {
                                    $p2 = strpos($code, '<?php', $p1);
                                    if ($p2 === false)
                                        $p2 = $l;
                                    $c .= substr($code, $p1, $p2 - $p1);
                                    $p1 = $p2;
                                    if ($p1 < $l) {
                                        $p1 += 5;
                                        $p2 = strpos($code, '?>', $p1);
                                        if ($p2 === false)
                                            $p2 = $l;
                                        $n++;
                                        $c .= eval(substr($code, $p1, $p2 - $p1));
                                        $p1 = $p2 + 2;
                                    } // if
                                } // while
                            }
                            $item->$key = $value;
                        } else if (trim($wrapper['item_wrapper'] ?? '') != '') {
                            $item->$key = str_replace('{value}', $new_value, trim($wrapper['item_wrapper'] ?? ''));
                            $item->$key = str_replace('{webpath}', str_replace(array('{CBSite}', '{cbsite}', JPATH_SITE), Uri::getInstance()->getScheme() . '://' . Uri::getInstance()->getHost() . (Uri::getInstance()->getPort() == 80 ? '' : ':' . Uri::getInstance()->getPort()) . Uri::root(true), $value ?? ''), $item->$key);
                        } else {
                            $item->$key = $new_value;
                        }
                        break;
                    }
                }
            }
        }
        return $items;
    }

    public static function createBackendMenuItem15($contentbuilderng_form_id, $name, $update)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $parent_id = 0;
        $db->setQuery("Select id From #__components Where `option`='' And admin_menu_link='option=com_contentbuilderng&viewcontainer=true'");
        $res = $db->loadResult();
        if ($res) {
            $parent_id = $res;
        } else {
            $db->setQuery(
                "Insert Into #__components
                 (
                    `name`,
                    `admin_menu_link`,
                    `admin_menu_alt`,
                    `option`,
                    `admin_menu_img`,
                    `iscore`
                 )
                 Values
                 (
                    'ContentBuilder NG Views',
                    'option=com_contentbuilderng&viewcontainer=true',
                    'contentbuilderng',
                    '',
                    'media/com_contentbuilderng/images/logo_icon_cb.png',
                    1
                 )
           "
            );
            $db->execute();
            $parent_id = $db->insertid();
        }
        $db->setQuery("Select id From #__components Where admin_menu_link = 'option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'");
        $menuitem = $db->loadResult();
        if (!$update)
            return;
        $db->setQuery("Select count(published) From #__contentbuilderng_elements Where form_id = " . intval($contentbuilderng_form_id));
        if ($db->loadResult()) {
            if (!$menuitem) {
                $db->setQuery(
                    "Insert Into #__components
                     (
                        `name`,
                        `admin_menu_link`,
                        `admin_menu_alt`,
                        `option`,
                        `admin_menu_img`,
                        `iscore`,
                        `parent`
                     )
                     Values
                     (
                        " . $db->quote($name) . ",
                        'option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "',
                        " . $db->quote($name) . ",
                        'com_contentbuilderng',
                        'media/com_contentbuilderng/images/logo_icon_cb.png',
                        1,
                        '$parent_id'
                     )
               "
                );
            } else {
                $db->setQuery(
                    "Update #__components
                     Set
                     `name` = " . $db->quote($name) . ",
                     `admin_menu_alt` = " . $db->quote($name) . ",
                     `parent` = $parent_id
                     Where id = $menuitem
               "
                );
            }
            $db->execute();
        }
    }

    public static function createBackendMenuItem16($contentbuilderng_form_id, $name, $update)
    {
        if (trim($name)) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilderng' And parent_id = 1");
            $result = $db->loadResult();

            $db->setQuery("Select id From #__menu Where `link`='index.php?option=com_contentbuilderng&viewcontainer=true' And parent_id = 1");
            $old_id = $db->loadResult();
            $parent_id = $old_id;

            if (!$old_id) {
                $db->setQuery(
                    "insert into #__menu (" .
                    "`title`, alias, menutype, parent_id, " .
                    "link," .
                    "ordering, level, component_id, client_id, img, lft,rgt" .
                    ") " .
                    "values (" .
                    "'ContentBuilder NG Views', 'ContentBuilder NG Views', 'main', 1, " .
                    "'index.php?option=com_contentbuilderng&viewcontainer=true'," .
                    "'0', 1, " . intval($result) . ", 1, 'media/com_contentbuilderng/images/logo_icon_cb.png',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )" .
                    ")"
                );
                $db->execute();
                $parent_id = $db->insertid();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();
            }

            $db->setQuery("Select id From #__menu Where link = 'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'");
            $menuitem = $db->loadResult();

            if (!$update)
                return;
            if (!$result)
                die("ContentBuilder main menu item not found!");

            $db->setQuery("Select id From #__menu Where alias = " . $db->quote($name) . " And link Like 'index.php?option=com_contentbuilderng&task=list.display&id=%' And link <> 'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'");
            $name_exists = $db->loadResult();

            if ($name_exists) {
                $name .= '_';
            }

            if (!$menuitem) {

                $db->setQuery(
                    "insert into #__menu (" .
                    "`title`, alias, menutype, parent_id, " .
                    "link," .
                    "ordering, level, component_id, client_id, img" .
                    ",lft,rgt) " .
                    "values (" .
                    "" . $db->quote($name) . ", " . $db->quote($name) . ", 'main', '$parent_id', " .
                    "'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'," .
                    "'0', 1, " . intval($result) . ", 1, 'media/com_contentbuilderng/images/logo_icon_cb.png'" .
                    ",( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone), ( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet))"
                );
                $db->execute();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();

            } else {

                $db->setQuery(
                    "Update #__menu Set `title` = " . $db->quote($name) . ", alias = " . $db->quote($name) . ", `parent_id` = '$parent_id' Where id = $menuitem"
                );
                $db->execute();

            }
        }
    }

    public static function createBackendMenuItem3($contentbuilderng_form_id, $name, $update)
    {

        if (trim($name)) {

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilderng' And parent_id = 1");
            $result = $db->loadResult();

            $db->setQuery("Select id From #__menu Where `link`='index.php?option=com_contentbuilderng&viewcontainer=true' And parent_id = 1");
            $old_id = $db->loadResult();
            $parent_id = $old_id;

            if (!$old_id) {

                $db->setQuery(
                    "insert into #__menu (" .
                    "`title`, alias, menutype, type, parent_id, " .
                    "link," .
                    "level, component_id, client_id, img, lft,rgt" .
                    ") " .
                    "values (" .
                    "'ContentBuilder NG Views', 'ContentBuilder NG Views', 'main', 'component', 1, " .
                    "'index.php?option=com_contentbuilderng&viewcontainer=true'," .
                    "1, " . intval($result) . ", 1, 'media/com_contentbuilderng/images/logo_icon_cb.png',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )" .
                    ")"
                );
                $db->execute();
                $parent_id = $db->insertid();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();
            }

            $db->setQuery("Select id From #__menu Where link = 'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'");
            $menuitem = $db->loadResult();

            if (!$update)
                return;
            if (!$result)
                die("ContentBuilder NG main menu item not found!");

            $db->setQuery("Select id From #__menu Where alias = " . $db->quote($name) . " And link Like 'index.php?option=com_contentbuilderng&task=list.display&id=%' And link <> 'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'");
            $name_exists = $db->loadResult();

            if ($name_exists) {
                $name .= '_';
            }

            if (!$menuitem) {

                $db->setQuery(
                    "insert into #__menu (" .
                    "params,`path`,`title`, alias, menutype, type, parent_id, " .
                    "link," .
                    "level, component_id, client_id, img" .
                    ",lft,rgt) " .
                    "values (" .
                    "''," . "''," . $db->quote($name) . ", " . $db->quote($name) . ", 'main', 'component', '$parent_id', " .
                    "'index.php?option=com_contentbuilderng&task=list.display&id=" . intval($contentbuilderng_form_id) . "'," .
                    "1, " . intval($result) . ", 1, 'media/com_contentbuilderng/images/logo_icon_cb.png'" .
                    ",( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone), ( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet))"
                );
                $db->execute();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();

            } else {

                $db->setQuery(
                    "Update #__menu Set `title` = " . $db->quote($name) . ", alias = " . $db->quote($name) . ", `parent_id` = '$parent_id' Where id = $menuitem"
                );
                $db->execute();

            }
        }
    }

    public static function createDetailsSample($contentbuilderng_form_id, $form, $plugin)
    {
        return (new FormSupportService(new PathService()))->createDetailsSample($contentbuilderng_form_id, $form, $plugin);
    }

    public static function createEmailSample($contentbuilderng_form_id, $form, $html = false)
    {
        return (new FormSupportService(new PathService()))->createEmailSample($contentbuilderng_form_id, $form, $html);
    }

    public static function createEditableSample($contentbuilderng_form_id, $form, $plugin)
    {
        return (new FormSupportService(new PathService()))->createEditableSample($contentbuilderng_form_id, $form, $plugin);
    }

    public static function synchElements($contentbuilderng_form_id, $form): array
    {
        return (new FormSupportService(new PathService()))->synchElements($contentbuilderng_form_id, $form);
    }

    public static function getTypes()
    {
        return (new FormSupportService(new PathService()))->getTypes();
    }

    private static function isBreezingFormsAvailable(): bool
    {
        $manifestCandidates = array(
            JPATH_ROOT . '/administrator/components/com_breezingforms/breezingforms.xml',
            JPATH_ROOT . '/administrator/components/com_breezingforms/com_breezingforms.xml',
            JPATH_ROOT . '/administrator/components/com_breezingforms_ng/com_breezingforms_ng.xml',
            JPATH_ROOT . '/administrator/components/com_breezingformsng/com_breezingformsng.xml',
            JPATH_ROOT . '/administrator/components/com_breezingformsng/breezingformsng.xml',
        );

        foreach ($manifestCandidates as $manifest) {
            if (file_exists($manifest)) {
                return true;
            }
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('COUNT(1)')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' IN (' . $db->quote('com_breezingforms') . ',' . $db->quote('com_breezingforms_ng') . ')');
            $db->setQuery($query);
            if ((int) $db->loadResult() > 0) {
                return true;
            }
        } catch (\Throwable $e) {
            // ignore and continue with table-based detection
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $tables = array_map('strtolower', (array) $db->getTableList());
            $required = array(
                strtolower($db->replacePrefix('#__facileforms_forms')),
                strtolower($db->replacePrefix('#__facileforms_records')),
            );
            return !array_diff($required, $tables);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getForms($type)
    {
        return (new FormSupportService(new PathService()))->getForms($type);
    }

    public static function getForm($type, $reference_id)
    {
        static $forms;

        Logger::info('Instanciation Legacy', [
            'type' => $type,
            'reference_id'   => $reference_id,
        ]);
        
        
        $type = trim((string) $type);
        $namespace = 'CB\\Component\\Contentbuilderng\\Administrator\\types\\';
        $adminTypeCandidates = array($type);
        if ($type === 'com_contentbuilderng') {
            $adminTypeCandidates[] = 'com_contentbuilder';
        } else if ($type === 'com_contentbuilder') {
            $adminTypeCandidates[] = 'com_contentbuilderng';
        }

        foreach ($adminTypeCandidates as $adminType) {
            $candidate = JPATH_ADMINISTRATOR . '/components/com_contentbuilderng/src/types/' . $adminType . '.php';
            if (file_exists($candidate)) {
                require_once($candidate);
            }
        }

        $app = Factory::getApplication();
        $isAdminPreview = $app->input->getBool('cb_preview_ok', false);
        $isAdministrator = $app->isClient('administrator');
        $allowUnpublishedSource = $isAdminPreview || $isAdministrator;

        if (isset($forms[$type][$reference_id])) {
            return $forms[$type][$reference_id];
        }

        $classCandidates = array($namespace . 'contentbuilderng_' . $type);
        if ($type === 'com_contentbuilderng') {
            $classCandidates[] = $namespace . 'contentbuilderng_com_contentbuilder';
        } else if ($type === 'com_contentbuilder') {
            $classCandidates[] = $namespace . 'contentbuilderng_com_contentbuilderng';
        }

        foreach ($classCandidates as $class) {
            if (!class_exists($class)) {
                continue;
            }

            try {
                $form = new $class($reference_id);
                $exists = !property_exists($form, 'exists') || (bool) ($form->exists ?? false);

                // In admin context (or signed preview), allow loading unpublished
                // source forms/storages by retrying constructors that support
                // a "$published" argument.
                if ($allowUnpublishedSource && !$exists) {
                    try {
                        $previewForm = new $class($reference_id, false);
                        if (is_object($previewForm)) {
                            $form = $previewForm;
                            $exists = !property_exists($form, 'exists') || (bool) ($form->exists ?? false);
                        }
                    } catch (\ArgumentCountError|\TypeError $e) {
                        // Class signature does not support a publish flag; keep initial instance.
                    }
                }
            } catch (\Throwable $e) {
                Logger::exception($e);
                throw $e;
            }

            if (!is_array($forms)) {
                $forms = array();
            }
            $forms[$type][$reference_id] = $form;
            return $form;
        }

        if (file_exists(JPATH_SITE . '/media/contentbuilderng/types/' . $type . '.php')) {
            require_once(JPATH_SITE . '/media/contentbuilderng/types/' . $type . '.php');
            if (isset($forms[$type][$reference_id])) {
                return $forms[$type][$reference_id];
            }
            $namespace = '';
            $class     = $namespace . 'contentbuilderng_' . $type;
            if (class_exists($class)) {
                try {
                    $form = new $class($reference_id);
                    $exists = !property_exists($form, 'exists') || (bool) ($form->exists ?? false);

                    if ($allowUnpublishedSource && !$exists) {
                        try {
                            $previewForm = new $class($reference_id, false);
                            if (is_object($previewForm)) {
                                $form = $previewForm;
                            }
                        } catch (\ArgumentCountError|\TypeError $e) {
                            // Class signature does not support a publish flag; keep initial instance.
                        }
                    }
                } catch (\Throwable $e) {
                    Logger::exception($e);
                    throw $e;
                }
                if (!is_array($forms)) {
                    $forms = array();
                }
                $forms[$type][$reference_id] = $form;
                return $form;
            }
        }
        return null;
    }

    public static function getListSearchableElements($contentbuilderng_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilderng_elements Where search_include = 1 And published = 1 And form_id = " . intval($contentbuilderng_form_id));
        return $db->loadColumn();
    }

    public static function getListLinkableElements($contentbuilderng_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilderng_elements Where linkable = 1 And published = 1 And form_id = " . intval($contentbuilderng_form_id));
        return $db->loadColumn();
    }

    public static function getListEditableElements($contentbuilderng_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilderng_elements Where editable = 1 And published = 1 And form_id = " . intval($contentbuilderng_form_id));
        return $db->loadColumn();
    }

    public static function getListNonEditableElements($contentbuilderng_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilderng_elements Where ( editable = 0 Or published = 0 ) And form_id = " . intval($contentbuilderng_form_id));
        return $db->loadColumn();
    }

    public static function getTemplate($contentbuilderng_form_id, $record_id, array $record, array $elements_allowed, $quiet_skip = false)
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $input = $app->input;

        // Never evaluate details templates while rendering list or edit/new displays.
        // (New uses edit display with record_id=0 in this component.)
        if (
            $app->isClient('site')
            && (
                $input->getCmd('view', '') === 'list'
                || $input->getCmd('view', '') === 'edit'
                || str_starts_with($input->getCmd('task', ''), 'list.')
                || $input->getCmd('task', '') === 'edit.display'
            )
        ) {
            return '';
        }

        static $_template;

        $hash = md5($contentbuilderng_form_id . $record_id . implode(',', $elements_allowed));

        if (is_array($_template) && isset($_template[$hash])) {
            return $_template[$hash];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `type`,reference_id,details_template, details_prepare, edit_by_type, act_as_registration, registration_name_field, registration_username_field, registration_email_field, registration_email_repeat_field, registration_password_field, registration_password_repeat_field From #__contentbuilderng_forms Where id = " . intval($contentbuilderng_form_id));
        $result = $db->loadAssoc();
        if (is_array($result) && $result['details_template']) {

            $user = null;
            if ($result['act_as_registration']) {
                $form = self::getForm($result['type'], $result['reference_id']);
                $meta = $form->getRecordMetadata($record_id);
                $db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                $user = $db->loadObject();
            }

            $_template = array();
            $labels = array();
            $allow_html = array();

            $db->setQuery("Select `label`,`reference_id`,`options` From #__contentbuilderng_elements Where form_id = " . intval($contentbuilderng_form_id));
            $labels_ = $db->loadAssocList();

            foreach ($labels_ as $label_) {
                $labels[$label_['reference_id']] = $label_['label'];
                $opts = self::decodePackedData($label_['options'], null, false);
                if (is_object($opts) && ((isset($opts->allow_html) && $opts->allow_html) || (isset($opts->allow_raw) && $opts->allow_raw))) {
                    $allow_html[$label_['reference_id']] = $opts;
                }
            }

            $template = self::normalizeTemplateMarkers($result['details_template']);
            $items = array();

            $hasLabels = count($labels);

            foreach ($record as $item) {
                if (in_array($item->recElementId, $elements_allowed)) {
                    $items[$item->recName] = array();
                    $items[$item->recName]['label'] = $hasLabels ? $labels[$item->recElementId] : $item->recTitle;
                    if ($result['act_as_registration'] && $user !== null) {
                        if ($result['registration_name_field'] == $item->recElementId) {
                            $item->recValue = $user->name;
                        } else
                            if ($result['registration_username_field'] == $item->recElementId) {
                                $item->recValue = $user->username;
                            } else
                                if ($result['registration_email_field'] == $item->recElementId) {
                                    $item->recValue = $user->email;
                                } else
                                    if ($result['registration_email_repeat_field'] == $item->recElementId) {
                                        $item->recValue = '';
                                    } else
                                        if ($result['registration_password_field'] == $item->recElementId) {
                                            $item->recValue = '';
                                        } else
                                            if ($result['registration_password_repeat_field'] == $item->recElementId) {
                                                $item->recValue = '';
                                            }
                    }

                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
                    $items[$item->recName]['id'] = $item->recElementId;
                    $regex = "/([\{]hide-if-empty " . $item->recName . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    $regex2 = "/([\{]hide-if-matches " . $item->recName . " (.*)[\}])(.*)([\{][\/]hide-if-matches[\}])/isU";
                    $matches = array();
                    preg_match_all($regex2, $template, $matches);
                    if (isset($matches[2]) && in_array($item->recValue, $matches[2])) {

                        $regex3 = "/([\{]hide-if-matches " . $item->recName . " " . trim($item->recValue) . "[\}])(.*)([\{][\/]hide-if-matches[\}])/isU";
                        $template = preg_replace($regex3, "", $template);

                    }

                    if ($item->recValue == '') {
                        $template = preg_replace($regex, "", $template);
                    } else {
                        $template = preg_replace($regex, '$2', $template);
                    }
                }
            }

            $regex3 = "/([\{]hide-if-matches (.*) (.*)[\}])(.*)([\{][\/]hide-if-matches[\}])/isU";
            $template = preg_replace($regex3, '$4', $template);

            $item = null;
            $raw_items = $items;
            foreach ($items as $key => $item) {
                if (!isset($item['label']) || !isset($item['id']))
                    continue;
                $items[$key]['label'] = htmlentities($item['label'], ENT_QUOTES, 'UTF-8');
                $items[$key]['value'] = isset($allow_html[$item['id']]) ? self::cleanString($item['value']) : nl2br(self::allhtmlentities(self::callContentbuilderngHelper('cbinternal', $item['value'])));
            }
            $detailsPrepare = $result['details_prepare'] ?? '';
            TemplatePrepareHelper::execute(
                $detailsPrepare,
                'details_prepare',
                function (string $prepareCode) use (&$items, &$template, &$raw_items, &$item, $record, $result, $record_id, $elements_allowed, $contentbuilderng_form_id): void {
                    eval($prepareCode);
                }
            );
            foreach ($items as $key => $item) {
                if (!isset($item['label']) || !isset($item['id']))
                    continue;
                $template = str_replace('{' . $key . ':label}', $item['label'], $template);
                $template = str_replace('{' . $key . ':value}', $item['value'], $template);
                $template = str_replace('{webpath ' . $key . '}', str_replace(array('{CBSite}', '{cbsite}', JPATH_SITE), Uri::getInstance()->getScheme() . '://' . Uri::getInstance()->getHost() . (Uri::getInstance()->getPort() == 80 ? '' : ':' . Uri::getInstance()->getPort()) . Uri::root(true), $raw_items[$key]['value']), $template);
            }

            $_template[$hash] = $template;
            return $template;
        } else {
            if ($quiet_skip)
                return '';
            throw new \Exception(Text::_('COM_CONTENTBUILDERNG_TEMPLATE_NOT_FOUND'), 404);
        }
        return '';
    }

    public static function allhtmlentities($string)
    {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        return self::cleanString($string);
    }

    public static function cleanString($string)
    {
        return str_replace(array('[', ']', '{', '}', '(', ')', '|'), array('&#91;', '&#93;', '&#123;', '&#125;', '&#40;', '&#41;', '&#124;'), $string);
    }

    public static function getFormElementsPlugins()
    {
        return (new FormSupportService(new PathService()))->getFormElementsPlugins();
    }

    private static function normalizeTemplateMarkers(string $template): string
    {
        if (stripos($template, '{hide-if-empty') === false && stripos($template, '{/hide}') === false) {
            return $template;
        }

        $replacements = array(
            '/<li>\s*{hide-if-empty\s+([^}]+)}\s*<\/li>/i' => '{hide-if-empty $1}',
            '/<li>\s*{\/hide}\s*{hide-if-empty\s+([^}]+)}\s*<\/li>/i' => "{/hide}\n{hide-if-empty $1}",
            '/<li>\s*{\/hide}\s*<\/li>/i' => '{/hide}',
        );

        return preg_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function getEmailTemplate($contentbuilderng_form_id, $record_id, array $record, array $elements_allowed, $isAdmin)
    {

        static $_template;

        $hash = md5(($isAdmin ? 'admin' : 'user') . $contentbuilderng_form_id . $record_id . implode(',', $elements_allowed));

        if (is_array($_template) && isset($_template[$hash])) {
            return $_template[$hash];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `name`,`type`,reference_id,email_template, email_admin_template, email_html, email_admin_html, act_as_registration, registration_name_field, registration_username_field, registration_email_field  From #__contentbuilderng_forms Where id = " . intval($contentbuilderng_form_id));
        $result = $db->loadAssoc();
        if (is_array($result)) {

            $user = null;
            if ($result['act_as_registration']) {
                $form = self::getForm($result['type'], $result['reference_id']);
                $meta = $form->getRecordMetadata($record_id);
                $db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                $user = $db->loadObject();
            }

            $_template = array();
            $labels = array();
            $allow_html = array();

            $db->setQuery("Select `label`,`reference_id`,`options` From #__contentbuilderng_elements Where form_id = " . intval($contentbuilderng_form_id));
            $labels_ = $db->loadAssocList();

            foreach ($labels_ as $label_) {
                $labels[$label_['reference_id']] = $label_['label'];
                $opts = self::decodePackedData($label_['options'], null, false);
                if (is_object($opts) && isset($opts->allow_html) && $opts->allow_html) {
                    $allow_html[$label_['reference_id']] = $opts;
                }
            }

            $template = $isAdmin ? $result['email_admin_template'] : $result['email_template'];
            $html = $isAdmin ? $result['email_admin_html'] : $result['email_html'];

            $items = array();

            $hasLabels = count($labels);

            foreach ($record as $item) {

                if (in_array($item->recElementId, $elements_allowed)) {

                    $items[$item->recName] = array();
                    $items[$item->recName]['label'] = $hasLabels ? $labels[$item->recElementId] : $item->recTitle;
                    if ($result['act_as_registration'] && $user !== null) {
                        if ($result['registration_name_field'] == $item->recElementId) {
                            $item->recValue = $user->name;
                        } else
                            if ($result['registration_username_field'] == $item->recElementId) {
                                $item->recValue = $user->username;
                            } else
                                if ($result['registration_email_field'] == $item->recElementId) {
                                    $item->recValue = $user->email;
                                }
                    }
                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
                    $items[$item->recName]['id'] = $item->recElementId;
                    $regex = "/([\{]hide-if-empty " . $item->recName . "[\}])(.*)([\{][\/]hide[\}])/isU";
                    $regex2 = "/([\{]hide-if-empty " . $item->recName . " " . $item->recValue . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDERNG_NOT_AVAILABLE'));
                    $items[$item->recName]['id'] = $item->recElementId;
                    $regex = "/([\{]hide-if-empty " . $item->recName . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    $regex2 = "/([\{]hide-if-matches " . $item->recName . " (.*)[\}])(.*)([\{][\/]-if-matches[\}])/isU";
                    $matches = array();
                    preg_match_all($regex2, $template, $matches);
                    if (isset($matches[2]) && in_array($item->recValue, $matches[2])) {

                        $regex3 = "/([\{]hide-if-matches " . $item->recName . " " . trim($item->recValue) . "[\}])(.*)([\{][\/]-if-matches[\}])/isU";
                        $template = preg_replace($regex3, "", $template);

                    }

                    if ($item->recValue == '') {
                        $template = preg_replace($regex, "", $template);
                    } else {
                        $template = preg_replace($regex, '$2', $template);
                        $template = preg_replace($regex2, '$2', $template);
                    }
                }
            }

            $regex3 = "/([\{]hide-if-matches (.*) (.*)[\}])(.*)([\{][\/]-if-matches[\}])/isU";
            $template = preg_replace($regex3, '$4', $template);

            $item = null;

            $template = str_replace(array('{RECORD_ID}', '{record_id}'), $record_id, $template);
            $template = str_replace(array('{USER_ID}', '{user_id}'), Factory::getApplication()->getIdentity()->id, $template);
            $template = str_replace(array('{USERNAME}', '{username}'), Factory::getApplication()->getIdentity()->username, $template);
            $template = str_replace(array('{USER_FULL_NAME}', '{user_full_name}'), Factory::getApplication()->getIdentity()->name, $template);
            $template = str_replace(array('{VIEW_NAME}', '{view_name}'), $result['name'], $template);
            $template = str_replace(array('{VIEW_ID}', '{view_id}'), $contentbuilderng_form_id, $template);
            $template = str_replace(array('{IP}', '{ip}'), $_SERVER['REMOTE_ADDR'], $template);

            foreach ($items as $key => $item) {
                $template = str_replace('{' . $key . ':label}', $html ? htmlentities($item['label'], ENT_QUOTES, 'UTF-8') : $item['label'], $template);
                $template = str_replace('{' . $key . ':value}', isset($allow_html[$item['id']]) && $html ? (self::callContentbuilderngHelper('is_internal_path', $item['value']) ? basename($item['value']) : $item['value']) : nl2br(strip_tags((self::callContentbuilderngHelper('is_internal_path', $item['value']) ? basename($item['value']) : $item['value']))), $template);
                $template = str_replace('{webpath ' . $key . '}', str_replace(array('{CBSite}', '{cbsite}', JPATH_SITE), Uri::getInstance()->getScheme() . '://' . Uri::getInstance()->getHost() . (Uri::getInstance()->getPort() == 80 ? '' : ':' . Uri::getInstance()->getPort()) . Uri::root(true), $item['value']), $template);
            }

            $_template[$hash] = $template;
            return $template;
        } else {
            '';
        }
        return '';
    }

    public static function getEditableTemplate($contentbuilderng_form_id, $record_id, array $record, array $elements_allowed, $execPrepare = true)
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $session = $app->getSession();

        $failed_values = $session->get('cb_failed_values', null, 'com_contentbuilderng.' . $contentbuilderng_form_id);

        if ($failed_values !== null) {
            $session->clear('cb_failed_values', 'com_contentbuilderng.' . $contentbuilderng_form_id);
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `type`, reference_id, editable_template, editable_prepare, edit_by_type, act_as_registration, registration_name_field, registration_username_field, registration_email_field, registration_email_repeat_field, registration_password_field, registration_password_repeat_field From #__contentbuilderng_forms Where id = " . intval($contentbuilderng_form_id));
        $result = $db->loadAssoc();

        if (is_array($result) && trim((string) ($result['editable_template'] ?? '')) !== '') {

            $user = null;
            if ($result['act_as_registration']) {
                if ($record_id) {
                    $form = self::getForm($result['type'], $result['reference_id']);
                    $meta = $form->getRecordMetadata($record_id);
                    $db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                    $user = $db->loadObject();
                } else if ((int) (Factory::getApplication()->getIdentity()->id ?? 0)) {
                    $db->setQuery("Select * From #__users Where id = " . (int) (Factory::getApplication()->getIdentity()->id ?? 0));
                    $user = $db->loadObject();
                }
            }

            $labels = array();
            $validations = array();

            if (!$result['edit_by_type']) {
                $db->setQuery("Select `label`,`reference_id`,`validations` From #__contentbuilderng_elements Where form_id = " . intval($contentbuilderng_form_id));
                $labels_ = $db->loadAssocList();
                foreach ($labels_ as $label_) {
                    $labels[$label_['reference_id']] = $label_['label'];
                    $validations[$label_['reference_id']] = $label_['validations'];
                }
            }

            $hasLabels = count($labels);
            $form_type = $result['type'];
            $form_reference_id = $result['reference_id'];
            $form = self::getForm($form_type, $form_reference_id);
            $template = $result['editable_template'];
            $items = array();
            foreach ($record as $item) {
                if (in_array($item->recElementId, $elements_allowed)) {
                    $items[$item->recName] = array();
                    $items[$item->recName]['id'] = $item->recElementId;
                    $items[$item->recName]['label'] = $hasLabels ? $labels[$item->recElementId] : $item->recTitle;
                    if ($result['act_as_registration'] && $user !== null) {
                        if ($result['registration_name_field'] == $item->recElementId) {
                            $item->recValue = $user->name;
                        } else
                            if ($result['registration_username_field'] == $item->recElementId) {
                                $item->recValue = $user->username;
                            } else
                                if ($result['registration_email_field'] == $item->recElementId) {
                                    $item->recValue = $user->email;
                                } else
                                    if ($result['registration_email_repeat_field'] == $item->recElementId) {
                                        $item->recValue = $user->email;
                                    }
                    }
                    $items[$item->recName]['value'] = ($item->recValue ? $item->recValue : '');
                }
            }

            // in case if there is no record given, provide the element data but an empty value
            $hasRecords = true;
            if (!count($record)) {
                $hasRecords = false;
                $names = $form->getElementNames();
                if (!count($labels)) {
                    $labels = $form->getElementLabels();
                }
                foreach ($names as $elementId => $name) {
                    if (!isset($items[$name])) {
                        $items[$name] = array();
                    }
                    $items[$name]['id'] = $elementId;
                    $items[$name]['label'] = $labels[$elementId];
                    $items[$name]['value'] = '';
                }
            }
            $item = null;
            if ($execPrepare) {
                $editablePrepare = $result['editable_prepare'] ?? '';
                TemplatePrepareHelper::execute(
                    $editablePrepare,
                    'editable_prepare',
                    function (string $prepareCode) use (&$items, &$template, &$item, $record, $result, $record_id, $elements_allowed, $contentbuilderng_form_id): void {
                        eval($prepareCode);
                    }
                );
            }

            $the_init_scripts = "\n" . '<script type="text/javascript">' . "\n" . '<!--' . "\n";

            foreach ($items as $key => $item) {
                $db->setQuery(
                    "Select * From #__contentbuilderng_elements"
                    . " Where published = 1"
                    . " And reference_id = " . $db->quote($item['id'])
                    . " And form_id = " . intval($contentbuilderng_form_id)
                    . " Order By ordering"
                );
                $element = $db->loadAssoc();

                // If a placeholder exists in the editable template for a non-editable/missing
                // element, avoid leaking raw tokens like {Field:item}. Render a safe text fallback.
                if (!is_array($element) || !$element) {
                    $rawValue = ($failed_values !== null && isset($failed_values[$item['id']]))
                        ? $failed_values[$item['id']]
                        : ($hasRecords ? ($item['value'] ?? '') : '');

                    if (is_array($rawValue)) {
                        $rawValue = array_values(
                            array_filter(
                                $rawValue,
                                static fn($v) => $v !== null && $v !== '' && $v !== 'cbGroupMark'
                            )
                        );
                        $rawValue = implode(', ', $rawValue);
                    }

                    $fallbackLabel = htmlentities((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $fallbackValue = htmlentities((string) $rawValue, ENT_QUOTES, 'UTF-8');

                    $template = str_replace('{' . $key . ':label}', '<label>' . $fallbackLabel . '</label>', $template);
                    $template = str_replace('{' . $key . ':item}', $fallbackValue, $template);
                    continue;
                }

                $autocomplete = '';

                if ($result['act_as_registration']) {

                    if ($result['registration_name_field'] == $element['reference_id']) {
                        $element['default_value'] = $user !== null ? $user->name : '';
                        $autocomplete = 'autocomplete="off" ';
                    } else
                        if ($result['registration_username_field'] == $element['reference_id']) {
                            $element['default_value'] = $user !== null ? $user->username : '';
                            $autocomplete = 'autocomplete="off" ';
                        } else
                            if ($result['registration_email_field'] == $element['reference_id']) {
                                $element['default_value'] = $user !== null ? $user->email : '';
                                $autocomplete = 'autocomplete="off" ';
                            } else
                                if ($result['registration_email_repeat_field'] == $element['reference_id']) {
                                    $element['default_value'] = $user !== null ? $user->email : '';
                                    $autocomplete = 'autocomplete="off" ';
                                } else
                                    if ($result['registration_password_field'] == $element['reference_id']) {
                                        $element['force_password'] = true;
                                        $autocomplete = 'autocomplete="off" ';
                                    } else
                                        if ($result['registration_password_repeat_field'] == $element['reference_id']) {
                                            $element['force_password'] = true;
                                            $autocomplete = 'autocomplete="off" ';
                                        }
                }

                if (!isset($element['default_value']) && !$hasRecords) {
                    $element['default_value'] = $item['value'];
                } else if (isset($element['default_value']) && !$hasRecords) {
                    $element['default_value'] = self::execPhp($element['default_value']);
                }

                $asterisk = '';

                if (is_array($element)) {
                    $elementType = $element['type'] ?? '';
                    $elementOptions = $element['options'] ?? '';
                    $elementReferenceId = $element['reference_id'] ?? '';
                    $elementCustomInit = $element['custom_init_script'] ?? '';
                    $elementHint = $element['hint'] ?? '';
                    $isEditable = (int) ($element['editable'] ?? 1) === 1;

                    if ($elementType == 'captcha' || trim($element['validations'] ?? '') != '' || trim($element['custom_validation_script'] ?? '') != '') {
                        $asterisk = ' <span class="cbRequired" style="color:red;">*</span>';
                    }

                    $options = self::decodePackedData($elementOptions, null, false);
                    if (!is_object($options)) {
                        $options = new \stdClass();
                    }

                    $the_item = '';

                    switch ($elementType) {
                        case in_array($elementType, self::getFormElementsPlugins()):


                            \Joomla\CMS\Plugin\PluginHelper::importPlugin('contentbuilderng_form_elements', $elementType);

                            $dispatcher = Factory::getApplication()->getDispatcher();
                            $eventResult = $dispatcher->dispatch('onRenderElement', new \Joomla\CMS\Event\GenericEvent('onRenderElement', array($item, $element, $options, $failed_values, $result, $hasRecords)));
                            $results = $eventResult->getArgument('result') ?: [];
                            $dispatcher->clearListeners('onRenderElement');

                            if (count($results)) {
                                $results = $results[0];
                            }

                            $the_item = $results;
                            break;
                        case '':
                        case 'text':
                            if (!isset($options->length)) {
                                $options->length = '';
                            }
                            if (!isset($options->maxlength)) {
                                $options->maxlength = '';
                            }
                            if (!isset($options->password)) {
                                $options->password = '';
                            }
                            if (!isset($options->readonly)) {
                                $options->readonly = '';
                            }

                            $the_item = '<div class="cbFormField cbTextField"><input class="form-control form-control-sm" ' . $autocomplete . '' . ($options->readonly ? 'readonly="readonly" ' : '') . 'style="' . ($options->length ? 'width:' . $options->length . ';' : '') . '" ' . ($options->maxlength ? 'maxlength="' . intval($options->maxlength) . '" ' : '') . 'type="' . (isset($element['force_password']) || $options->password ? 'password' : 'text') . '" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '" value="' . htmlentities($failed_values !== null && isset($failed_values[$element['reference_id']]) ? $failed_values[$element['reference_id']] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8') . '"/></div>';
                            break;
                        case 'textarea':

                            if (!isset($options->width)) {
                                $options->width = '';
                            }
                            if (!isset($options->height)) {
                                $options->height = '';
                            }
                            if (!isset($options->maxlength)) {
                                $options->maxlength = '';
                            }
                            if (!isset($options->readonly)) {
                                $options->readonly = '';
                            }
                            if (!isset($options->allow_html)) {
                                $options->allow_html = false;
                            }
                            if (!isset($options->allow_raw)) {
                                $options->allow_raw = false;
                            }
                            if ($options->allow_html || $options->allow_raw) {
                                $editor = Editor::getInstance(Factory::getApplication()->get('editor'));
                                $the_item = '<div class="cbFormField cbTextArea">' . $editor->display('cb_' . $item['id'], htmlentities($failed_values !== null && isset($failed_values[$element['reference_id']]) ? $failed_values[$element['reference_id']] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8'), $options->width ? $options->width : '100%', $options->height ? $options->height : '550', '75', '20') . '</div>';
                            } else {
                                $the_item = '<div class="cbFormField cbTextArea form-control form-control-sm"><textarea class="form-control form-control-sm" ' . ($options->readonly ? 'readonly="readonly" ' : '') . 'style="' . ($options->width || $options->height ? ($options->width ? 'width:' . $options->width . ';' : '') . ($options->height ? 'height:' . $options->height . ';' : '') : '') . '" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '">' . htmlentities($failed_values !== null && isset($failed_values[$element['reference_id']]) ? $failed_values[$element['reference_id']] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8') . '</textarea></div>';
                            }
                            break;
                        case 'checkboxgroup':
                        case 'radiogroup':
                            //if(!isset($options->seperator)){
                            //    $options->seperator = ',';
                            //}

                            $options->seperator = ',';

                            if (!isset($options->horizontal)) {
                                $options->horizontal = false;
                            }
                            if (!isset($options->horizontal_length)) {
                                $options->horizontal_length = '';
                            }
                            if ($form->isGroup($item['id'])) {
                                $groupdef = $form->getGroupDefinition($item['id']);
                                $i = 0;
                                $sep = $options->seperator;
                                $group = explode($sep, $failed_values !== null && isset($failed_values[$element['reference_id']]) && is_array($failed_values[$element['reference_id']]) ? implode($sep, $failed_values[$element['reference_id']]) : ($hasRecords ? $item['value'] : $element['default_value']));
                                $groupSize = count($groupdef);
                                $groupSize = !$groupSize ? 1 : $groupSize;
                                $the_item = '<input name="cb_' . $item['id'] . '[]" type="hidden" value="cbGroupMark"/>';
                                foreach ($groupdef as $value => $label) {
                                    $checked = '';
                                    $for = '';
                                    if ($i != 0) {
                                        $for = '_' . $i;
                                    }
                                    foreach ($group as $selected_value) {
                                        if (trim($value) == trim($selected_value)) {
                                            $checked = ' checked="checked"';
                                            break;
                                        }
                                    }
                                    $the_item .= '<div style="' . ($options->horizontal ? 'float: left;' . ($options->horizontal_length ? 'width: ' . $options->horizontal_length . ';' : '') . 'display: inline; margin-right: 2px;' : '') . '" class="cbFormField cbGroupField"><input class="form-check-input" id="cb_' . $item['id'] . $for . '" name="cb_' . $item['id'] . '[]" type="' . ($element['type'] == 'checkboxgroup' ? 'checkbox' : 'radio') . '" value="' . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '"' . $checked . '/> <label for="cb_' . $item['id'] . $for . '">' . htmlentities(trim($label), ENT_QUOTES, 'UTF-8') . '</label> </div>';
                                    $i++;
                                }
                                if ($options->horizontal) {
                                    $the_item .= '<div style="clear:both;"></div>';
                                }

                            } else {
                                $the_item .= '<span style="color:red">ELEMENT IS NOT A GROUP</span>';
                            }
                            break;
                        case 'select':
                            //if(!isset($options->seperator)){
                            //    $options->seperator = ',';
                            //}
                            $options->seperator = ',';

                            if (!isset($options->multiple)) {
                                $options->multiple = 0;
                            }
                            if (!isset($options->length)) {
                                $options->length = '';
                            }

                            if ($form->isGroup($item['id'])) {
                                $groupdef = $form->getGroupDefinition($item['id']);
                                $i = 0;
                                $sep = $options->seperator;
                                $multi = $options->multiple;
                                $group = explode($sep, $failed_values !== null && isset($failed_values[$element['reference_id']]) && is_array($failed_values[$element['reference_id']]) ? implode($sep, $failed_values[$element['reference_id']]) : ($hasRecords ? $item['value'] : $element['default_value']));
                                $the_item = '<input name="cb_' . $item['id'] . '[]" type="hidden" value="cbGroupMark"/>';
                                $the_item .= '<div class="cbFormField cbSelectField"><select class="form-select form-select-sm" id="cb_' . $item['id'] . '" ' . ($options->length ? 'style="width:' . $options->length . ';" ' : '') . 'name="cb_' . $item['id'] . '[]"' . ($multi ? ' multiple="multiple"' : '') . '>';
                                foreach ($groupdef as $value => $label) {
                                    $checked = '';
                                    foreach ($group as $selected_value) {
                                        if (trim($value) == trim($selected_value)) {
                                            $checked = ' selected="selected"';
                                            break;
                                        }
                                    }
                                    $the_item .= '<option value="' . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '"' . $checked . '>' . htmlentities(trim($label), ENT_QUOTES, 'UTF-8') . '</option>';
                                    $i++;
                                }
                                $the_item .= '</select></div>';

                            } else {
                                $the_item .= '<span style="color:red">ELEMENT IS NOT A GROUP</span>';
                            }
                            break;
                        case 'upload':

                            $deletable = false;
                            if (isset($validations[$item['id']]) && $validations[$item['id']] == '') {
                                $deletable = true;
                            }
                            $the_item = '<div class="cbFormField cbUploadField">';
                            $the_item .= '<input type="file" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '"/>';
                            if (trim($item['value']) != '') {
                                $the_item .= '<div>' . ($deletable ? '<label for="cb_delete_' . $item['id'] . '">' . Text::_('COM_CONTENTBUILDERNG_DELETE') . '</label> <input type="checkbox" id="cb_delete_' . $item['id'] . '" name="cb_delete_' . $item['id'] . '" value="1"/> ' : '') . htmlentities(basename($item['value']), ENT_QUOTES, 'UTF-8') . '</div><div style="clear:both;"></div>';
                            }
                            $the_item .= '</div>';
                            break;

                        case 'captcha':

                            $the_item = '<div class="cbFormField cbCaptchaField">';

                            if (Factory::getApplication()->isClient('site')) {
                                $captcha_url = Uri::root(true) . '/components/com_contentbuilderng/images/securimage/securimage_show.php';
                            } else {
                                $captcha_url = Uri::root(true) . '/administrator/components/com_contentbuilderng/assets/images/securimage_show.php';
                            }

                            $the_item .= '<img width="250" height="80" id="cbCaptcha" alt="captcha" src="' . $captcha_url . '?rand=' . rand(0, getrandmax()) . '"/>';
                            $the_item .= '<div>';
                            $the_item .= '<input class="form-control form-control-sm mt-1" autocomplete="off" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '" type="text" maxlength="12" />';
                            $the_item .= '<img style="cursor: pointer; padding-left: 7px;" onclick="document.getElementById(\'cbCaptcha\').src = \'' . $captcha_url . '?\' + Math.random(); blur(); return false" border="0" alt="refresh" src="' . Uri::root(true) . '/components/com_contentbuilderng/images/securimage/refresh-captcha.png"/>';
                            $the_item .= '</div>';
                            $the_item .= '</div>';
                            break;
                        case 'calendar':

                            $the_item = '<div class="cbFormField cbCalendarField">' . "\n";
                            $the_item .= '<div id="field-calendar_cb_' . $item['id'] . '">' . "\n";
                            $the_item .= '<div class="input-group">' . "\n";

                            if (!isset($options->length)) {
                                $options->length = '';
                            }
                            if (!isset($options->maxlength)) {
                                $options->maxlength = '';
                            }
                            if (!isset($options->readonly)) {
                                $options->readonly = '';
                            }
                            if (!isset($options->format)) {
                                $options->format = '%Y-%m-%d';
                            }
                            if (!isset($options->transfer_format)) {
                                $options->transfer_format = 'YYYY-mm-dd';
                            }

                            $calval = htmlentities($failed_values !== null && isset($failed_values[$element['reference_id']]) ? $failed_values[$element['reference_id']] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8');
                            $calval = self::callContentbuilderngHelper('convertDate', $calval, $options->transfer_format, $options->format);

                            $calAttr = [
                                'class' => 'cb_' . $item['id'],
                                'showTime' => true,
                                'timeFormat' => '24',
                                'singleHeader' => false,
                                'todayBtn' => true,
                                'weekNumbers' => true,
                                'minYear' => '',
                                'maxYear' => '',
                                'firstDay' => '1',
                            ];

                            $the_item .= HTMLHelper::_('calendar', $calval, 'cb_' . $item['id'], 'cb_' . $item['id'], $options->format, $calAttr);

                            $the_item .= '</div>
								</div>
							</div>';

                            break;
                        case 'hidden':
                            $the_item = '<input type="hidden" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '" value="' . htmlentities($failed_values !== null && $elementReferenceId !== '' && isset($failed_values[$elementReferenceId]) ? $failed_values[$elementReferenceId] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8') . '"/>';
                            break;
                    }

                    if (!$isEditable && $elementType !== 'hidden' && $the_item !== '') {
                        $disableControl = static function (string $tag, bool $addReadonly): string {
                            if (preg_match('/\btype\s*=\s*([\'"])hidden\1/i', $tag)) {
                                return $tag;
                            }
                            if (stripos($tag, ' disabled=') === false) {
                                $tag = rtrim($tag, '>') . ' disabled="disabled" aria-disabled="true">';
                            }
                            if ($addReadonly && stripos($tag, ' readonly=') === false) {
                                $tag = rtrim($tag, '>') . ' readonly="readonly">';
                            }
                            return $tag;
                        };

                        $the_item = preg_replace_callback(
                            '/<input\b[^>]*>/i',
                            static fn($m) => $disableControl($m[0], true),
                            $the_item
                        );
                        $the_item = preg_replace_callback(
                            '/<textarea\b[^>]*>/i',
                            static fn($m) => $disableControl($m[0], true),
                            $the_item
                        );
                        $the_item = preg_replace_callback(
                            '/<select\b[^>]*>/i',
                            static fn($m) => $disableControl($m[0], false),
                            $the_item
                        );
                        $the_item = preg_replace_callback(
                            '/<button\b[^>]*>/i',
                            static fn($m) => $disableControl($m[0], false),
                            $the_item
                        );

                        // Avoid interactive captcha refresh when field is non-editable.
                        $the_item = preg_replace('/\s+onclick="[^"]*"/i', '', $the_item);
                    }

                    if ($elementCustomInit) {
                        $the_init_scripts .= $elementCustomInit . "\n";
                    }

                    $replaceTokens = false;
                    if ($the_item === '' || $the_item === null) {
                        $rawValue = ($failed_values !== null && isset($failed_values[$element['reference_id']]))
                            ? $failed_values[$element['reference_id']]
                            : ($hasRecords ? ($item['value'] ?? '') : ($element['default_value'] ?? ''));

                        if (is_array($rawValue)) {
                            $rawValue = array_values(
                                array_filter(
                                    $rawValue,
                                    static fn($v) => $v !== null && $v !== '' && $v !== 'cbGroupMark'
                                )
                            );
                            $rawValue = implode(', ', $rawValue);
                        }

                        $the_item = htmlentities((string) $rawValue, ENT_QUOTES, 'UTF-8');
                        $replaceTokens = true;
                    }

                    if ($the_item !== '' || $replaceTokens) {
                        $tip = 'hasTip';
                        $tip_prefix = htmlentities($item['label'], ENT_QUOTES, 'UTF-8') . '::';
                        $template = str_replace('{' . $key . ':label}', '<label ' . ($elementHint ? 'class="editlinktip ' . $tip . '" title="' . $tip_prefix . $elementHint . '" ' : '') . 'for="cb_' . $item['id'] . '">' . $item['label'] . $asterisk . ($elementHint ? ' <img style="cursor: pointer;" src="' . Uri::root(true) . '/components/com_contentbuilderng/images/icon_info.png" border="0"/>' : '') . '</label>', $template);
                        $template = str_replace('{' . $key . ':item}', $the_item, $template);
                    }
                }
            }

            return $template . $the_init_scripts . "\n" . '//-->' . '</script>' . "\n";

        } else {
            if (!is_array($result)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDERNG_FORM_NOT_FOUND'), 'error');
            } else {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDERNG_EDITABLE_TEMPLATE_NOT_SET'), 'error');
            }
        }

        return '';
    }

    public static function createArticle($contentbuilderng_form_id, $record_id, array $record, array $elements_allowed, $title_field = '', $metadata = null, $config = array(), $full = false, $limited_options = true, $menu_cat_id = null)
    {
        return (new ArticleService())->createArticle($contentbuilderng_form_id, $record_id, $record, $elements_allowed, $title_field, $metadata, $config, $full, $limited_options, $menu_cat_id);
    }

    public static function setPermissions($form_id, $record_id = 0, $suffix = '')
    {
        (new PermissionService())->setPermissions($form_id, $record_id, (string) $suffix);
    }

    private static function isSignedAdminPreviewRequest(int $formId): bool
    {
        $app = Factory::getApplication();
        $input = $app->input;

        if ($formId < 1 || !$input->getBool('cb_preview', false)) {
            return false;
        }

        $until = (int) $input->getInt('cb_preview_until', 0);
        $sig = trim((string) $input->getString('cb_preview_sig', ''));

        if ($until < time() || $sig === '') {
            return false;
        }

        $secret = (string) $app->get('secret');
        if ($secret === '') {
            return false;
        }

        $actorId = (int) $input->getInt('cb_preview_actor_id', 0);
        $actorName = trim((string) $input->getString('cb_preview_actor_name', ''));

        $payload = $formId . '|' . $until;
        $expected = hash_hmac('sha256', $payload, $secret);

        $actorPayload = $payload . '|' . $actorId . '|' . $actorName;
        $actorExpected = hash_hmac('sha256', $actorPayload, $secret);

        if (($actorId > 0 || $actorName !== '') && hash_equals($actorExpected, $sig)) {
            return true;
        }

        return hash_equals($expected, $sig);
    }

    public static function stringURLUnicodeSlug($string)
    {
        // Replace double byte whitespaces by single byte (East Asian languages)
        $str = preg_replace('/\xE3\x80\x80/', ' ', $string);


        // Remove any '-' from the string as they will be used as concatenator.
        // Would be great to let the spaces in but only Firefox is friendly with this

        $str = str_replace('-', ' ', $str);

        // Replace forbidden characters by whitespaces
        $str = preg_replace('#[:\#\*"@+=;!&\.%()\]\/\'\\\\|\[]#', "\x20", $str);

        // Delete all '?'
        $str = str_replace('?', '', $str);

        // Trim white spaces at beginning and end of alias and make lowercase
        $str = trim(strtolower($str));

        // Remove any duplicate whitespace and replace whitespaces by hyphens
        $str = preg_replace('#\x20+#', '-', $str);

        return $str;
    }

    public static function checkPermissions($action, $error_msg, $suffix = '', $auth = false)
    {
        return (new PermissionService())->checkPermissions($action, (string) $error_msg, (string) $suffix, (bool) $auth);
    }

    public static function authorize($action)
    {
        return (new PermissionService())->authorize($action);
    }

    public static function authorizeFe($action)
    {
        return (new PermissionService())->authorizeFe($action);
    }

    public static function getListStates($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select * From #__contentbuilderng_list_states where form_id = " . intval($id) . " And published = 1 Order By id");
        $list_states = $db->loadAssocList();
        return $list_states;
    }

    public static function getStateColors($items, $id)
    {
        $out = array();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $imp = '';
        $itemcnt = count($items);
        $i = 0;
        foreach ($items as $item) {
            $imp .= $db->quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
            $i++;
        }
        if ($imp) {
            $db->setQuery("Select states.color, records.record_id From #__contentbuilderng_list_states As states, #__contentbuilderng_list_records As records Where states.published = 1 And states.id = records.state_id And records.record_id In (" . $imp . ") And records.form_id = " . intval($id) . " And states.form_id = " . intval($id));
            $colors = $db->loadAssocList();
            foreach ($colors as $color) {
                $out[$color['record_id']] = $color['color'];
            }
        }
        return $out;
    }

    public static function getStateTitles($items, $id)
    {
        $out = array();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $imp = '';
        $itemcnt = count($items);
        $i = 0;
        foreach ($items as $item) {
            $imp .= $db->quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
            $i++;
        }
        if ($imp) {
            $db->setQuery("Select states.title, records.record_id From #__contentbuilderng_list_states As states, #__contentbuilderng_list_records As records Where states.published = 1 And states.id = records.state_id And records.record_id In (" . $imp . ") And records.form_id = " . intval($id) . " And states.form_id = " . intval($id));
            $colors = $db->loadAssocList();
            foreach ($colors as $color) {
                $out[$color['record_id']] = $color['title'];
            }
        }
        return $out;
    }

    public static function getRecordsPublishInfo($items, $type, $reference_id)
    {
        $out = array();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        if ($reference_id) {

            $imp = '';
            $itemcnt = count($items);
            $i = 0;

            foreach ($items as $item) {
                $imp .= $db->quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
                $i++;
            }

            if ($imp) {
                $db->setQuery("Select records.published, records.record_id From #__contentbuilderng_records As records Where `type` = " . $db->quote($type) . " And reference_id = " . $db->quote($reference_id) . " And records.record_id In (" . $imp . ")");
                $published = $db->loadAssocList();
                foreach ($published as $publish) {
                    $out[$publish['record_id']] = $publish['published'];
                }
            }
        }
        return $out;
    }

    public static function getRecordsLanguage($items, $type, $reference_id)
    {
        $out = array();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        if ($reference_id) {

            $imp = '';
            $itemcnt = count($items);
            $i = 0;

            foreach ($items as $item) {
                $imp .= $db->quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
                $i++;
            }

            if ($imp) {
                $db->setQuery("Select records.lang_code, records.record_id From #__contentbuilderng_records As records Where reference_id = " . $db->quote($reference_id) . " And records.record_id In (" . $imp . ")");
                $codes = $db->loadAssocList();
                foreach ($codes as $code) {
                    $out[$code['record_id']] = $code['lang_code'];
                }
            }
        }
        return $out;
    }
}
