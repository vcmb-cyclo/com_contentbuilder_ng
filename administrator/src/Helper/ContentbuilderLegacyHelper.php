<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA + GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 * @copyright   Copyright (C) 2026 by XDA+GIL
 */


namespace CB\Component\Contentbuilder\Administrator\Helper;

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Access\Access;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;

final class ContentbuilderLegacyHelper
{

    public static function makeSafeFolder($path)
    {
        $regex = array('#[^A-Za-z0-9\.:_\\\/-]#');
        return preg_replace($regex, '_', $path);
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
                        <?php echo $pages_current - 1 > 0 ? '<a title="' . Text::_('COM_CONTENTBUILDER_START') . '" href="' . $open . '" class="pagenav">' . Text::_('COM_CONTENTBUILDER_START') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDER_START') . '</span>'; ?>
                    </li>
                    <li class="pagination-prev">
                        <?php echo $pages_current - 1 > 0 ? '<a title="' . Text::_('COM_CONTENTBUILDER_PREV') . '" href="' . $open . 'start=' . ($limitstart - $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDER_PREV') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDER_PREV') . '</span>'; ?>
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
                        <?php echo $pages_current < $pages_total ? '<a title="' . Text::_('COM_CONTENTBUILDER_NEXT') . '" href="' . $open . 'start=' . ($pages_current * $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDER_NEXT') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDER_NEXT') . '</span>'; ?>
                    </li>
                    <li class="pagination-end">
                        <?php echo $pages_total > 1 && $pages_current < $pages_total ? '<a title="' . Text::_('COM_CONTENTBUILDER_END') . '" href="' . $open . 'start=' . (($pages_total - 1) * $limit) . '" class="pagenav">' . Text::_('COM_CONTENTBUILDER_END') . '</a>' : '<span class="pagenav">' . Text::_('COM_CONTENTBUILDER_END') . '</span>'; ?>
                    </li>
                </ul>
            </div>
            <?php
            $c = ob_get_contents();
            ob_end_clean();
        }

        return $c;
    }

    public static function getRating($form_id, $record_id, $colRating, $rating_slots, $lang, $rating_allowed, $rating_count, $rating_sum)
    {

        static $cssLoaded;

        if (!$cssLoaded) {

            Factory::getApplication()->getDocument()->addStyleDeclaration('.cbVotingDisplay, .cbVotingStarButtonWrapper {
	height: 20px;
	width: 100px;
}

.cbVotingStarButtonWrapper {
	position: absolute;
	z-index: 100;	
}

.cbVotingDisplay {
	background-image: url(' . Uri::root(true) . '/components/com_contentbuilder/assets/images/bg_votingStarOff.png);
	background-repeat: repeat-x;
        height: auto;
}

.cbVotingStars {
	position: relative;
	float: left;
	height: 20px;
	overflow: hidden;
	background-image: url(' . Uri::root(true) . '/components/com_contentbuilder/assets/images/bg_votingStarOn.png);
	background-repeat: repeat-x;
}

.cbVotingStarButton {
	display: inline;
	height: 20px;
	width: 20px;
	float: left;
	cursor: pointer;
}

.cbRating{ width: 30px; }
.cbRatingUpDown{text-align: center; width: 90px; }
.cbRatingImage {margin: auto; display: block;}
.cbRatingCount {text-align: center; font-size: 11px;}
.cbRatingVotes {text-align: center; font-size: 11px;} 

.cbRatingImage2{
    width: 30px;
    height: 30px;
    background-image: url(' . Uri::root(true) . '/components/com_contentbuilder/assets/images/thumbs_down.png);
    background-repeat: no-repeat;
}

.cbRatingImage{ 
    width: 30px;
    height: 30px;
    background-image: url(' . Uri::root(true) . '/components/com_contentbuilder/assets/images/thumbs_up.png);
    background-repeat: no-repeat;
}');

            $cssLoaded = true;
        }

        ob_start();
        if ($rating_count) {
            $percentage2 = round(($colRating / 5) * 100, 2);
            $percentage3 = 100 - $percentage2;
        } else {
            $percentage2 = 0;
            $percentage3 = 0;
        }
        $percentage = round(($colRating / $rating_slots) * ($rating_slots * 20));
        if ($rating_slots > 2) {
            ?>
            <div class="cbVotingDisplay" style="width: <?php echo ($rating_slots * 20); ?>px;">
                <div class="cbVotingStarButtonWrapper">
                    <?php
        }
        $rating_link = '';
        if ($rating_allowed) {
            if (Factory::getApplication()->isClient('site')) {
                $rating_link = Uri::root(true) . (Factory::getApplication()->isClient('administrator') ? '/administrator' : (CBRequest::getCmd('lang', '') && 
                Factory::getConfig()->get('sef') && Factory::getConfig()->get('sef_rewrite') ? '/' . CBRequest::getCmd('lang', '') : '')) . '/?option=com_contentbuilder&lang=' . $lang . '&view=ajax&format=raw&subject=rating&id=' . $form_id . '&record_id=' . $record_id;
            } else {
                $rating_link = 'index.php?option=com_contentbuilder&lang=' . $lang . '&view=ajax&format=raw&subject=rating&id=' . $form_id . '&record_id=' . $record_id;
            }
        }
        for ($x = 1; $x <= $rating_slots; $x++) {
            if ($rating_link) {
                if ($rating_slots > 2) {
                    ?>
                            <div onmouseout="document.getElementById('cbVotingStars<?php echo $record_id; ?>').style.width=<?php echo $percentage; ?>+'px';"
                                onmouseover="document.getElementById('cbVotingStars<?php echo $record_id; ?>').style.width=(<?php echo $x; ?>*20)+'px';"
                                class="cbVotingStarButton" id="cbVotingStarButton_<?php echo $x; ?>"
                                onclick="cbRate('<?php echo $rating_link . '&rate=' . $x; ?>','cbRatingMsg<?php echo $record_id; ?>');">
                            </div>
                            <?php
                } else if ($rating_slots == 2) {
                    ?>
                                <div class="cbRatingUpDown">
                                    <div style="float: left;">
                                        <div class="cbRatingImage" style="cursor:pointer;"
                                            onclick="cbRate('<?php echo $rating_link . '&rate=5'; ?>','cbRatingMsg<?php echo $record_id; ?>');">
                                        </div>
                                        <div align="center" class="cbRatingCount">
                                        <?php echo $percentage2 ? $percentage2 . '%' : ''; ?>
                                        </div>
                                    </div>
                                    <div style="float: right;">
                                        <div class="cbRatingImage2" style="cursor:pointer;"
                                            onclick="cbRate('<?php echo $rating_link . '&rate=1'; ?>','cbRatingMsg<?php echo $record_id; ?>');">
                                        </div>
                                        <div align="center" class="cbRatingCount">
                                        <?php echo $percentage3 ? $percentage3 . '%' : ''; ?>
                                        </div>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div align="center" class="cbRatingVotes">
                                    <?php echo $rating_count == 1 ? $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_SINGULAR') : $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_PLURAL'); ?>
                                    </div>
                                </div>
                                <?php
                                break;
                } else {
                    ?>
                                <div class="cbRating">
                                    <div class="cbRatingImage" style="cursor:pointer;"
                                        onclick="cbRate('<?php echo $rating_link . '&rate=' . $x; ?>','cbRatingMsg<?php echo $record_id; ?>');">
                                    </div>
                                    <div align="center" id="cbRatingMsg<?php echo $record_id; ?>Counter" class="cbRatingCount">
                                    <?php echo $rating_count; ?>
                                    </div>
                                    <div align="center" class="cbRatingVotes">
                                    <?php echo $rating_count == 1 ? Text::_('COM_CONTENTBUILDER_VOTES_SINGULAR') : Text::_('COM_CONTENTBUILDER_VOTES_PLURAL'); ?>
                                    </div>
                                </div>
                            <?php
                }
            } else {
                if ($rating_slots > 2) {
                    ?>
                            <div class="cbVotingStarButton" style="cursor:default;" id="cbVotingStarButton_<?php echo $x; ?>"></div>
                            <?php
                } else if ($rating_slots == 2) {
                    ?>
                                <div class="cbRatingUpDown">
                                    <div style="float: left;">
                                        <div class="cbRatingImage" style="cursor:default;"></div>
                                        <div align="center" class="cbRatingCount">
                                        <?php echo $percentage2 ? $percentage2 . '%' : ''; ?>
                                        </div>
                                    </div>
                                    <div style="float: right;">
                                        <div class="cbRatingImage2" style="cursor:default;"></div>
                                        <div align="center" class="cbRatingCount">
                                        <?php echo $percentage3 ? $percentage3 . '%' : ''; ?>
                                        </div>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div align="center" class="cbRatingVotes">
                                    <?php echo $rating_count == 1 ? $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_SINGULAR') : $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_PLURAL'); ?>
                                    </div>
                                </div>
                                <?php
                                break;
                } else {
                    ?>
                                <div class="cbRating">
                                    <div class="cbRatingImage" style="cursor:default;"></div>
                                    <div align="center" class="cbRatingCount">
                                    <?php echo $rating_count; ?>
                                    </div>
                                    <div align="center" class="cbRatingVotes">
                                    <?php echo $rating_count == 1 ? Text::_('COM_CONTENTBUILDER_VOTES_SINGULAR') : Text::_('COM_CONTENTBUILDER_VOTES_PLURAL'); ?>
                                    </div>
                                </div>
                            <?php
                }
            }
        }
        if ($rating_slots > 2) {
            ?>
                </div>
                <div class="cbVotingStars" id="cbVotingStars<?php echo $record_id; ?>" style="width: <?php echo $percentage; ?>px;">
                </div>
                <div style="clear: left;"></div>
                <div align="center" class="cbRatingVotes">
                    <?php echo $rating_count == 1 ? $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_SINGULAR') : $rating_count . ' ' . Text::_('COM_CONTENTBUILDER_VOTES_PLURAL'); ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div style="display:none;" class="cbRatingMsg" id="cbRatingMsg<?php echo $record_id; ?>"></div>
        <?php
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }

    public static function execPhpValue($code)
    {
        if (strpos(strtolower(trim($code)), '$value') === 0) {
            eval($code);
            return $value;
        }
        return $code;
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

    public static function createBackendMenuItem($contentbuilder_form_id, $name, $update)
    {

        self::createBackendMenuItem3($contentbuilder_form_id, $name, $update);
    }

    public static function getLanguageCodes()
    {

        static $langs;

        if (is_array($langs)) {
            return $langs;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select lang_code From #__languages Where published = 1 Order By ordering");
        $langs = Factory::getContainer()->get(DatabaseInterface::class)->loadColumn();
        return $langs;
    }

    public static function applyItemWrappers($contentbuilder_form_id, array $items, $form)
    {

        $article = Table::getInstance('content');
        $registry = null;
        $onContentPrepare = '';

        $onContentPrepare = 'onContentPrepare';
        $registry = new Registry;
        $registry->loadString('{}');

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id, item_wrapper, wordwrap, `label`, `options` From #__contentbuilder_elements Where published = 1 And form_id = " . intval($contentbuilder_form_id));
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
                                    \Joomla\CMS\Plugin\PluginHelper4::importPlugin('content', $w[$j]);
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
                        $options = unserialize(base64_decode($wrapper['options']));

                        if ($options instanceof stdClass) {
                            if (isset($options->allow_html) && $options->allow_html) {
                                $allow_html = true;
                            }
                        }

                        if ($wrapper['wordwrap'] && !$allow_html) {
                            $new_value = self::allhtmlentities(contentbuilder_wordwrap(ContentbuilderHelper::cbinternal($value), $wrapper['wordwrap'], "\n", true));
                        } else {
                            $new_value = $allow_html ? self::cleanString(ContentbuilderHelper::cbinternal($value)) : self::allhtmlentities(ContentbuilderHelper::cbinternal($value));
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

    public static function createBackendMenuItem15($contentbuilder_form_id, $name, $update)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $parent_id = 0;
        $db->setQuery("Select id From #__components Where `option`='' And admin_menu_link='option=com_contentbuilder&viewcontainer=true'");
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
                    'ContentBuilder Views',
                    'option=com_contentbuilder&viewcontainer=true',
                    'ContentBuilder',
                    '',
                    'components/com_contentbuilder/views/logo_icon_cb.png',
                    1
                 )
           "
            );
            $db->execute();
            $parent_id = $db->insertid();
        }
        $db->setQuery("Select id From #__components Where admin_menu_link = 'option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'");
        $menuitem = $db->loadResult();
        if (!$update)
            return;
        $db->setQuery("Select count(published) From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id));
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
                        " . $db->Quote($name) . ",
                        'option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "',
                        " . $db->Quote($name) . ",
                        'com_contentbuilder',
                        'components/com_contentbuilder/views/logo_icon_cb.png',
                        1,
                        '$parent_id'
                     )
               "
                );
            } else {
                $db->setQuery(
                    "Update #__components
                     Set
                     `name` = " . $db->Quote($name) . ",
                     `admin_menu_alt` = " . $db->Quote($name) . ",
                     `parent` = $parent_id
                     Where id = $menuitem
               "
                );
            }
            $db->execute();
        }
    }

    public static function createBackendMenuItem16($contentbuilder_form_id, $name, $update)
    {
        if (trim($name)) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilder' And parent_id = 1");
            $result = $db->loadResult();

            $db->setQuery("Select id From #__menu Where `link`='index.php?option=com_contentbuilder&viewcontainer=true' And parent_id = 1");
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
                    "'ContentBuilder Views', 'ContentBuilder Views', 'main', 1, " .
                    "'index.php?option=com_contentbuilder&viewcontainer=true'," .
                    "'0', 1, " . intval($result) . ", 1, 'components/com_contentbuilder/views/logo_icon_cb.png',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )" .
                    ")"
                );
                $db->execute();
                $parent_id = $db->insertid();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();
            }

            $db->setQuery("Select id From #__menu Where link = 'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'");
            $menuitem = $db->loadResult();

            if (!$update)
                return;
            if (!$result)
                die("ContentBuilder main menu item not found!");

            $db->setQuery("Select id From #__menu Where alias = " . $db->Quote($name) . " And link Like 'index.php?option=com_contentbuilder&view=list&id=%' And link <> 'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'");
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
                    "" . $db->Quote($name) . ", " . $db->Quote($name) . ", 'main', '$parent_id', " .
                    "'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'," .
                    "'0', 1, " . intval($result) . ", 1, 'components/com_contentbuilder/views/logo_icon_cb.png'" .
                    ",( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone), ( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet))"
                );
                $db->execute();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();

            } else {

                $db->setQuery(
                    "Update #__menu Set `title` = " . $db->Quote($name) . ", alias = " . $db->Quote($name) . ", `parent_id` = '$parent_id' Where id = $menuitem"
                );
                $db->execute();

            }
        }
    }

    public static function createBackendMenuItem3($contentbuilder_form_id, $name, $update)
    {

        if (trim($name)) {

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilder' And parent_id = 1");
            $result = $db->loadResult();

            $db->setQuery("Select id From #__menu Where `link`='index.php?option=com_contentbuilder&viewcontainer=true' And parent_id = 1");
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
                    "'ContentBuilder Views', 'ContentBuilder Views', 'main', 'component', 1, " .
                    "'index.php?option=com_contentbuilder&viewcontainer=true'," .
                    "1, " . intval($result) . ", 1, 'components/com_contentbuilder/views/logo_icon_cb.png',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )" .
                    ")"
                );
                $db->execute();
                $parent_id = $db->insertid();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();
            }

            $db->setQuery("Select id From #__menu Where link = 'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'");
            $menuitem = $db->loadResult();

            if (!$update)
                return;
            if (!$result)
                die("ContentBuilder main menu item not found!");

            $db->setQuery("Select id From #__menu Where alias = " . $db->Quote($name) . " And link Like 'index.php?option=com_contentbuilder&view=list&id=%' And link <> 'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'");
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
                    "''," . "''," . $db->Quote($name) . ", " . $db->Quote($name) . ", 'main', 'component', '$parent_id', " .
                    "'index.php?option=com_contentbuilder&view=list&id=" . intval($contentbuilder_form_id) . "'," .
                    "1, " . intval($result) . ", 1, 'components/com_contentbuilder/views/logo_icon_cb.png'" .
                    ",( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone), ( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet))"
                );
                $db->execute();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__menu` Set rgt = " . $rgt . " Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                $db->execute();

            } else {

                $db->setQuery(
                    "Update #__menu Set `title` = " . $db->Quote($name) . ", alias = " . $db->Quote($name) . ", `parent_id` = '$parent_id' Where id = $menuitem"
                );
                $db->execute();

            }
        }
    }

    public static function createDetailsSample($contentbuilder_form_id, $form, $plugin)
    {
        if (!$contentbuilder_form_id || !is_object($form)) {
            return;
        }

        PluginHelper::importPlugin('contentbuilder_themes', $plugin);

        $dispatcher = Factory::getApplication()->getDispatcher();
        $eventResult = $dispatcher->dispatch('onContentTemplateSample', new Joomla\Event\Event('onContentTemplateSample', array($contentbuilder_form_id, $form)));
        $results = $eventResult->getArgument('result') ?: [];
        return implode('', $results);
    }

    public static function createEmailSample($contentbuilder_form_id, $form, $html = false)
    {
        if (!$contentbuilder_form_id || !is_object($form)) {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $out = '';
        if ($html) {
            $out = '<table border="0" width="100%"><tbody>' . "\n";
        }
        $names = $form->getElementNames();
        foreach ($names as $reference_id => $name) {
            $db->setQuery("Select id, `type` From #__contentbuilder_elements Where published = 1 And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $result = $db->loadAssoc();
            if (is_array($result)) {
                if ($result['type'] != 'hidden') {
                    $out .= '{hide-if-empty ' . $name . '}';
                    if ($html) {
                        $out .= '<tr><td width="20%" valign="top"><label>{' . $name . ':label}</label></td><td>{' . $name . ':value}</td></tr>' . "\r\n";
                    } else {
                        $out .= '{' . $name . ':label}: {' . $name . ':value}';
                    }
                    $out .= "\r\n" . '{/hide}';
                }
            }
        }
        if ($html) {
            $out .= '</tbody></table>' . "\n";
        }
        return $out;
    }

    public static function createEditableSample($contentbuilder_form_id, $form, $plugin)
    {

        if (!$contentbuilder_form_id || !is_object($form)) {
            return;
        }

        PluginHelper::importPlugin('contentbuilder_themes', $plugin);

        $dispatcher = Factory::getApplication()->getDispatcher();
        $eventResult = $dispatcher->dispatch('onEditableTemplateSample', new Joomla\Event\Event('onEditableTemplateSample', array($contentbuilder_form_id, $form)));
        $results = $eventResult->getArgument('result') ?: [];
        return implode('', $results);
    }

    public static function synchElements($contentbuilder_form_id, $form)
    {
        if (!$contentbuilder_form_id || !is_object($form)) {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $ids = array();
        $elements = $form->getElementLabels();

        foreach ($elements as $reference_id => $title) {
            // TODO: auto-type-recognition
            $options = new \stdClass();
            $options->length = '';
            $options->maxlength = '';
            $options->password = 0;
            $options->readonly = 0;
            $options->seperator = ',';
            $ids[] = $db->Quote($reference_id);
            $db->setQuery("Select id, `type`, `options` From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($reference_id));
            $assoc = $db->loadAssoc();

            if (!is_array($assoc)) {

                $db->setQuery("Select Max(ordering) + 1 From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id));
                $ordering = $db->loadResult();

                $db->setQuery("Insert Into #__contentbuilder_elements (`label`,`form_id`,`reference_id`,`type`,`options`, `ordering`) Values (" . $db->Quote($title) . "," . $db->Quote($contentbuilder_form_id) . "," . $db->Quote($reference_id) . ",'text','" . base64_decode(serialize($options)) . "', " . ($ordering ? $ordering : 0) . ")");
                $db->execute();
            }
        }
        // delete missing elements
        if (count($ids)) {
            $db->setQuery("Delete From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id) . " And reference_id Not In (" . implode(',', $ids) . ")");
            $db->execute();
        }
    }

    public static function getTypes()
    {

        $types = array();

        // built-in types
        if (file_exists(JPATH_SITE . '/administrator/components/com_breezingforms/breezingforms.xml')) {
            $types[] = 'com_breezingforms';
        }

        $types[] = 'com_contentbuilder';

        // Custom types
        if (!is_dir(JPATH_SITE . '/media/contentbuilder')) {
            Folder::create(JPATH_SITE . '/media/contentbuilder');
        }

        $def = '';

        if (!file_exists(JPATH_SITE . '/media/contentbuilder/index.html'))
            File::write(JPATH_SITE . '/media/contentbuilder/index.html', $def);
        if (!is_dir(JPATH_SITE . '/media/contentbuilder/types')) {
            Folder::create(JPATH_SITE . '/media/contentbuilder/types');
        }

        if (!file_exists(JPATH_SITE . '/media/contentbuilder/types/index.html'))
            File::write(JPATH_SITE . '/media/contentbuilder/types/index.html', $def);

        $sourcePath = JPATH_SITE . '/media/contentbuilder/types/';
        if (is_dir($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
            while (false !== ($file = @readdir($handle))) {
                if ($file != "." && $file != ".." && strtolower($file) != 'index.html' && strtolower($file) != '.cvs' && strtolower($file) != '.svn') {
                    $exploded = explode('.', $file);
                    unset($exploded[count($exploded) - 1]);
                    $types[] = implode('.', $exploded);
                }
            }
            @closedir($handle);
        }
        return $types;
    }

    public static function getForms($type)
    {
        if (file_exists(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php')) {
            require_once(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php');
            $class = 'contentbuilder_' . $type;
            if (class_exists($class)) {
                return call_user_func(array($class, "getFormsList"));
            }
        } else if (file_exists(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php')) {
            require_once(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php');
            $class = 'contentbuilder_' . $type;
            if (class_exists($class)) {
                return call_user_func(array($class, "getFormsList"));
            }
        }

        return array();
    }

    public static function getForm($type, $reference_id)
    {

        static $forms;

        if (file_exists(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php')) {
            require_once(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php');
            if (isset($forms[$type][$reference_id])) {
                return $forms[$type][$reference_id];
            }
            $class = 'contentbuilder_' . $type;
            if (class_exists($class)) {
                $form = new $class($reference_id);
                $forms = array();
                $forms[$type][$reference_id] = $form;
                return $form;
            }
        } else if (file_exists(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php')) {
            require_once(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php');
            if (isset($forms[$type][$reference_id])) {
                return $forms[$type][$reference_id];
            }
            $class = 'contentbuilder_' . $type;
            if (class_exists($class)) {
                $form = new $class($reference_id);
                $forms = array();
                $forms[$type][$reference_id] = $form;
                return $form;
            }
        }
        return null;
    }

    public static function getListSearchableElements($contentbuilder_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilder_elements Where search_include = 1 And published = 1 And form_id = " . intval($contentbuilder_form_id));
        return $db->loadColumn();
    }

    public static function getListLinkableElements($contentbuilder_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilder_elements Where linkable = 1 And published = 1 And form_id = " . intval($contentbuilder_form_id));
        return $db->loadColumn();
    }

    public static function getListEditableElements($contentbuilder_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilder_elements Where editable = 1 And published = 1 And form_id = " . intval($contentbuilder_form_id));
        return $db->loadColumn();
    }

    public static function getListNonEditableElements($contentbuilder_form_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select reference_id From #__contentbuilder_elements Where ( editable = 0 Or published = 0 ) And form_id = " . intval($contentbuilder_form_id));
        return $db->loadColumn();
    }

    public static function getTemplate($contentbuilder_form_id, $record_id, array $record, array $elements_allowed, $quiet_skip = false)
    {

        static $_template;

        $hash = md5($contentbuilder_form_id . $record_id . implode(',', $elements_allowed));

        if (is_array($_template) && isset($_template[$hash])) {
            return $_template[$hash];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `type`,reference_id,details_template, details_prepare, edit_by_type, act_as_registration, registration_name_field, registration_username_field, registration_email_field, registration_email_repeat_field, registration_password_field, registration_password_repeat_field From #__contentbuilder_forms Where id = " . intval($contentbuilder_form_id));
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

            $db->setQuery("Select `label`,`reference_id`,`options` From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id));
            $labels_ = $db->loadAssocList();

            foreach ($labels_ as $label_) {
                $labels[$label_['reference_id']] = $label_['label'];
                $opts = unserialize(base64_decode($label_['options']));
                if ($opts && ((isset($opts->allow_html) && $opts->allow_html) || (isset($opts->allow_raw) && $opts->allow_raw))) {
                    $allow_html[$label_['reference_id']] = $opts;
                }
            }

            $template = $result['details_template'];
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

                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDER_NOT_AVAILABLE'));
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
                $items[$key]['value'] = isset($allow_html[$item['id']]) ? self::cleanString($item['value']) : nl2br(self::allhtmlentities(ContentbuilderHelper::cbinternal($item['value'])));
            }
            @eval($result['details_prepare']);
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
            throw new Exception(Text::_('COM_CONTENTBUILDER_TEMPLATE_NOT_FOUND'), 404);
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
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_form_elements' And `enabled` = 1");
        $res = $db->loadColumn();
        return $res;
    }

    public static function getEmailTemplate($contentbuilder_form_id, $record_id, array $record, array $elements_allowed, $isAdmin)
    {

        static $_template;

        $hash = md5(($isAdmin ? 'admin' : 'user') . $contentbuilder_form_id . $record_id . implode(',', $elements_allowed));

        if (is_array($_template) && isset($_template[$hash])) {
            return $_template[$hash];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `name`,`type`,reference_id,email_template, email_admin_template, email_html, email_admin_html, act_as_registration, registration_name_field, registration_username_field, registration_email_field  From #__contentbuilder_forms Where id = " . intval($contentbuilder_form_id));
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

            $db->setQuery("Select `label`,`reference_id`,`options` From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id));
            $labels_ = $db->loadAssocList();

            foreach ($labels_ as $label_) {
                $labels[$label_['reference_id']] = $label_['label'];
                $opts = unserialize(base64_decode($label_['options']));
                if ($opts && isset($opts->allow_html) && $opts->allow_html) {
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
                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDER_NOT_AVAILABLE'));
                    $items[$item->recName]['id'] = $item->recElementId;
                    $regex = "/([\{]hide-if-empty " . $item->recName . "[\}])(.*)([\{][\/]hide[\}])/isU";
                    $regex2 = "/([\{]hide-if-empty " . $item->recName . " " . $item->recValue . "[\}])(.*)([\{][\/]hide[\}])/isU";

                    $items[$item->recName]['value'] = ($item->recValue != '' ? $item->recValue : Text::_('COM_CONTENTBUILDER_NOT_AVAILABLE'));
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
            $template = str_replace(array('{USER_ID}', '{user_id}'), Factory::getApplication()->getIdentity()->get('id'), $template);
            $template = str_replace(array('{USERNAME}', '{username}'), Factory::getApplication()->getIdentity()->get('username'), $template);
            $template = str_replace(array('{USER_FULL_NAME}', '{user_full_name}'), Factory::getApplication()->getIdentity()->get('name'), $template);
            $template = str_replace(array('{VIEW_NAME}', '{view_name}'), $result['name'], $template);
            $template = str_replace(array('{VIEW_ID}', '{view_id}'), $contentbuilder_form_id, $template);
            $template = str_replace(array('{IP}', '{ip}'), $_SERVER['REMOTE_ADDR'], $template);

            foreach ($items as $key => $item) {
                $template = str_replace('{' . $key . ':label}', $html ? htmlentities($item['label'], ENT_QUOTES, 'UTF-8') : $item['label'], $template);
                $template = str_replace('{' . $key . ':value}', isset($allow_html[$item['id']]) && $html ? (ontentbuilderHelper::is_internal_path($item['value']) ? basename($item['value']) : $item['value']) : nl2br(strip_tags((ontentbuilderHelper::is_internal_path($item['value']) ? basename($item['value']) : $item['value']))), $template);
                $template = str_replace('{webpath ' . $key . '}', str_replace(array('{CBSite}', '{cbsite}', JPATH_SITE), Uri::getInstance()->getScheme() . '://' . Uri::getInstance()->getHost() . (Uri::getInstance()->getPort() == 80 ? '' : ':' . Uri::getInstance()->getPort()) . Uri::root(true), $item['value']), $template);
            }

            $_template[$hash] = $template;
            return $template;
        } else {
            '';
        }
        return '';
    }

    public static function getEditableTemplate($contentbuilder_form_id, $record_id, array $record, array $elements_allowed, $execPrepare = true)
    {

        $failed_values = Factory::getApplication()->getSession()->get('cb_failed_values', null, 'com_contentbuilder.' . $contentbuilder_form_id);

        if ($failed_values !== null) {
            Factory::getApplication()->getSession()->clear('cb_failed_values', 'com_contentbuilder.' . $contentbuilder_form_id);
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select `type`, reference_id, editable_template, editable_prepare, edit_by_type, act_as_registration, registration_name_field, registration_username_field, registration_email_field, registration_email_repeat_field, registration_password_field, registration_password_repeat_field From #__contentbuilder_forms Where id = " . intval($contentbuilder_form_id));
        $result = $db->loadAssoc();

        if (is_array($result) && $result['editable_template']) {

            $user = null;
            if ($result['act_as_registration']) {
                if ($record_id) {
                    $form = self::getForm($result['type'], $result['reference_id']);
                    $meta = $form->getRecordMetadata($record_id);
                    $db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                    $user = $db->loadObject();
                } else if (Factory::getApplication()->getIdentity()->get('id', 0)) {
                    $db->setQuery("Select * From #__users Where id = " . Factory::getApplication()->getIdentity()->get('id', 0));
                    $user = $db->loadObject();
                }
            }

            $labels = array();
            $validations = array();

            if (!$result['edit_by_type']) {
                $db->setQuery("Select `label`,`reference_id`,`validations` From #__contentbuilder_elements Where form_id = " . intval($contentbuilder_form_id));
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
                eval($result['editable_prepare']);
            }

            $the_init_scripts = "\n" . '<script type="text/javascript">' . "\n" . '<!--' . "\n";

            foreach ($items as $key => $item) {
                $db->setQuery("Select * From #__contentbuilder_elements Where published = 1 And editable = 1 And reference_id = " . $db->Quote($item['id']) . " And form_id = " . intval($contentbuilder_form_id) . " Order By ordering");
                $element = $db->loadAssoc();

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

                    if ($element['type'] == 'captcha' || trim($element['validations'] ?? '') != '' || trim($element['custom_validation_script'] ?? '') != '') {
                        $asterisk = ' <span class="cbRequired" style="color:red;">*</span>';
                    }

                    $options = unserialize(base64_decode($element['options']));

                    $the_item = '';

                    switch ($element['type']) {
                        case in_array($element['type'], self::getFormElementsPlugins()):


                            \Joomla\CMS\Plugin\PluginHelper4::importPlugin('contentbuilder_form_elements', $element['type']);

                            $dispatcher = Factory::getApplication()->getDispatcher();
                            $eventResult = $dispatcher->dispatch('onRenderElement', new Joomla\Event\Event('onRenderElement', array($item, $element, $options, $failed_values, $result, $hasRecords)));
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
                                $the_item .= '<div>' . ($deletable ? '<label for="cb_delete_' . $item['id'] . '">' . Text::_('COM_CONTENTBUILDER_DELETE') . '</label> <input type="checkbox" id="cb_delete_' . $item['id'] . '" name="cb_delete_' . $item['id'] . '" value="1"/> ' : '') . htmlentities(basename($item['value']), ENT_QUOTES, 'UTF-8') . '</div><div style="clear:both;"></div>';
                            }
                            $the_item .= '</div>';
                            break;

                        case 'captcha':

                            $the_item = '<div class="cbFormField cbCaptchaField">';

                            if (Factory::getApplication()->isClient('site')) {
                                $captcha_url = Uri::root(true) . '/components/com_contentbuilder/images/securimage/securimage_show.php';
                            } else {
                                $captcha_url = Uri::root(true) . '/administrator/components/com_contentbuilder/assets/images/securimage_show.php';
                            }

                            $the_item .= '<img width="250" height="80" id="cbCaptcha" alt="captcha" src="' . $captcha_url . '?rand=' . rand(0, getrandmax()) . '"/>';
                            $the_item .= '<div>';
                            $the_item .= '<input class="form-control form-control-sm mt-1" autocomplete="off" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '" type="text" maxlength="12" />';
                            $the_item .= '<img style="cursor: pointer; padding-left: 7px;" onclick="document.getElementById(\'cbCaptcha\').src = \'' . $captcha_url . '?\' + Math.random(); blur(); return false" border="0" alt="refresh" src="' . Uri::root(true) . '/components/com_contentbuilder/images/securimage/refresh-captcha.png"/>';
                            $the_item .= '</div>';
                            $the_item .= '</div>';
                            break;
                        case 'calendar':

                            $the_item = '<div class="cbFormField cbCalendarField">' . "\n";
                            $the_item .= '<div id="field-calendar_cb_' . $item['id'] . '">' . "\n";
                            $the_item .= '<div class="input-append">' . "\n";

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
                            $calval = ContentbuilderHelper::convertDate($calval, $options->transfer_format, $options->format);

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
                            $the_item = '<input type="hidden" id="cb_' . $item['id'] . '" name="cb_' . $item['id'] . '" value="' . htmlentities($failed_values !== null && isset($failed_values[$element['reference_id']]) ? $failed_values[$element['reference_id']] : ($hasRecords ? $item['value'] : $element['default_value']), ENT_QUOTES, 'UTF-8') . '"/>';
                            break;
                    }

                    if ($element['custom_init_script']) {
                        $the_init_scripts .= $element['custom_init_script'] . "\n";
                    }

                    if ($the_item) {
                        $tip = 'hasTip';
                        $tip_prefix = htmlentities($item['label'], ENT_QUOTES, 'UTF-8') . '::';
                        $template = str_replace('{' . $key . ':label}', '<label ' . ($element['hint'] ? 'class="editlinktip ' . $tip . '" title="' . $tip_prefix . $element['hint'] . '" ' : '') . 'for="cb_' . $item['id'] . '">' . $item['label'] . $asterisk . ($element['hint'] ? ' <img style="cursor: pointer;" src="' . Uri::root(true) . '/components/com_contentbuilder/images/icon_info.png" border="0"/>' : '') . '</label>', $template);
                        $template = str_replace('{' . $key . ':item}', $the_item, $template);
                    }
                }
            }

            return $template . $the_init_scripts . "\n" . '//-->' . '</script>' . "\n";

        } else {
            // JError::raiseError(404, Text::_('COM_CONTENTBUILDER_TEMPLATE_NOT_FOUND'));
            // throw new Exception(Text::_('COM_CONTENTBUILDER_TEMPLATE_NOT_FOUND'), 404);
            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_TEMPLATE_NOT_FOUND'), 'warning');
        }

        return '';
    }

    public static function createArticle($contentbuilder_form_id, $record_id, array $record, array $elements_allowed, $title_field = '', $metadata = null, $config = array(), $full = false, $limited_options = true, $menu_cat_id = null)
    {

        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));
        $tz = new DateTimeZone(Factory::getApplication()->get('offset'));

        if (isset($config['publish_up']) && $config['publish_up']) {
            $config['publish_up'] = Factory::getDate($config['publish_up'], $tz);
            $config['publish_up'] = $config['publish_up']->format('Y-m-d H:i:s');
        } else {
            $config['publish_up'] = null;
        }


        if (isset($config['created']) && $config['created']) {
            $config['created'] = Factory::getDate($config['created'], $tz);
            $config['created'] = $config['created']->format('Y-m-d H:i:s');
        } else {
            $config['created'] = null;
        }

        if (isset($config['publish_down']) && $config['publish_down']) {
            $config['publish_down'] = Factory::getDate($config['publish_down'], $tz);
            $config['publish_down'] = $config['publish_down']->format('Y-m-d H:i:s');
        } else {
            $config['publish_down'] = null;
        }

        $is15 = false;

        $tpl = self::getTemplate($contentbuilder_form_id, $record_id, $record, $elements_allowed, true);
        if (!$tpl)
            return 0;
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select * From #__contentbuilder_forms Where id = " . intval($contentbuilder_form_id) . " And published = 1");
        $form = $db->loadAssoc();

        if (!$form) {
            return 0;
        }

        if ($is15 && $menu_cat_id !== null) {
            if (intval($menu_cat_id) > -2) {
                $menu_cat_id = explode(':', $menu_cat_id);
                if (count($menu_cat_id) == 2) {
                    $form['default_category'] = $menu_cat_id[1];
                    $form['default_section'] = $menu_cat_id[0];
                }
            }
        } else if ($menu_cat_id !== null && intval($menu_cat_id) > -2) {
            $form['default_category'] = $menu_cat_id;
        }

        $user = null;
        if ($form['act_as_registration']) {
            if ($record_id) {
                $form_ = self::getForm($form['type'], $form['reference_id']);
                $meta = $form_->getRecordMetadata($record_id);
                $db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                $user = $db->loadObject();
            } else if (Factory::getApplication()->getIdentity()->get('id', 0)) {
                $db->setQuery("Select * From #__users Where id = " . Factory::getApplication()->getIdentity()->get('id', 0));
                $user = $db->loadObject();
            }
        }

        $label = '';
        foreach ($record as $rec) {
            if ($rec->recElementId == $title_field) {

                if ($form['act_as_registration'] && $user !== null) {
                    if ($form['registration_name_field'] == $rec->recElementId) {
                        $rec->recValue = $user->name;

                    } else
                        if ($form['registration_username_field'] == $rec->recElementId) {
                            $rec->recValue = $user->username;
                        } else
                            if ($form['registration_email_field'] == $rec->recElementId) {
                                $rec->recValue = $user->email;
                            } else
                                if ($form['registration_email_repeat_field'] == $rec->recElementId) {
                                    $rec->recValue = $user->email;
                                }
                }

                $label = ContentbuilderHelper::cbinternal($rec->recValue);
                break;
            }
        }

        // trying first element if no title field given
        if (!$label && !count($record)) {
            $label = 'Unnamed';
        } else if (!$label && count($record)) {
            $label = ContentbuilderHelper::cbinternal($record[0]->recValue);
        }

        // Clean text for xhtml transitional compliance
        $introtext = '';
        $fulltext = '';
        $tpl = str_replace('<br>', '<br />', $tpl);

        // Search for the {readmore} tag and split the text up accordingly.
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        $tagPos = preg_match($pattern, $tpl);

        if ($tagPos == 0) {
            $introtext = $tpl;
        } else {
            list($introtext, $fulltext) = preg_split($pattern, $tpl, 2);
        }

        // retrieve the publish state from the list view
        $state = 1;
        $db->setQuery("Select published, is_future, publish_up, publish_down From #__contentbuilder_records Where `type` = " . $db->Quote($form['type']) . " And reference_id = " . $db->Quote($form['reference_id']) . " And record_id = " . $db->Quote($record_id));
        $state = $db->loadAssoc();
        $publish_up_record = $state['publish_up'];
        $publish_down_record = $state['publish_down'];
        $state = $state['is_future'] ? 1 : $state['published'];

        // save/update articles
        $alias = '';

        $db->setQuery("Select articles.`article_id`, content.`alias` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($contentbuilder_form_id) . " And articles.record_id = " . $db->Quote($record_id));
        $article = $db->loadAssoc();
        if (is_array($article)) {
            $alias = $article['alias'];
            $article = $article['article_id'];
        }

        // params
        $attribs = '';
        $meta = '';
        $rules = '';
        $metakey = '';
        $metadesc = '';

        $created_by = 0;
        $created_by_alias = '';
        $created_article = null;

        $_now = Factory::getDate();

        $created_up = $publish_up_record;
        $created_down = $publish_down_record;

        if (is_array($article) && isset($article['article_id']) && intval($form['default_publish_up_days']) != 0) {
            // this will cause errors on 64bit systems, as strtotime's behavior is different for null dates
            // $date = Factory::getDate(strtotime('now +'.intval($form['default_publish_up_days']).' days'));
            // fix as of forum post http://crosstec.de/forums/37-contentbuilder-general-forum-english/62084-64-bit-strtotime-bug.html#62084
            // thanks to user Fremmedkar
            $date = Factory::getDate(strtotime(((($created_up !== null)) ? $created_up : $_now) . ' +' . intval($form['default_publish_down_days']) . ' days'));
            $created_up = $date->toSql();
        }

        $publish_up = $created_up;

        if (is_array($article) && isset($article['article_id']) && intval($form['default_publish_down_days']) != 0) {
            //$date = Factory::getDate(strtotime( ($created_up !== null ? $created_up : $_now).' +'.intval($form['default_publish_down_days']).' days'));
            $date = Factory::getDate(strtotime(($created_up !== null ? $created_up : $_now) . ' +' . intval($form['default_publish_down_days']) . ' days'));
            $created_down = $date->toSql();
        }

        $publish_down = $created_down;

        $featured = $form['default_featured'];

        $ignore_lang_code = '*';
        if ($form['default_lang_code_ignore']) {
            $db->setQuery("Select lang_code From #__languages Where published = 1 And sef = " . $db->Quote(CBRequest::getCmd('lang', '')));
            $ignore_lang_code = $db->loadResult();
            if (!$ignore_lang_code) {
                $ignore_lang_code = '*';
            }
        }

        $language = $form['default_lang_code_ignore'] ? $ignore_lang_code : $form['default_lang_code'];

        $access = $form['default_access'];

        $ordering = 0;

        if ($full) {

            // limited
            $alias = isset($config['alias']) ? $config['alias'] : $alias;

            // limited
            $form['default_category'] = isset($config['catid']) ? $config['catid'] : $form['default_category'];

            // limited
            $access = isset($config['access']) ? $config['access'] : $access;

            // limited
            $featured = isset($config['featured']) ? $config['featured'] : 0;

            // limited
            $language = isset($config['language']) ? $config['language'] : $language;

            if ($form['article_record_impact_language'] && isset($config['language'])) {
                $db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $db->Quote($config['language']));
                $sef = $db->loadResult();

                if ($sef === null) {
                    $sef = '';
                }

                $db->setQuery("Update #__contentbuilder_records Set sef = " . $db->Quote($sef) . ", lang_code = " . $db->Quote($config['language']) . " Where `type` = " . $db->Quote($form['type']) . " And reference_id = " . $db->Quote($form['reference_id']) . " And record_id = " . $db->Quote($record_id));
                $db->execute();
            }

            // limited
            $created_by_alias = isset($config['created_by_alias']) ? $config['created_by_alias'] : '';

            if ($form['article_record_impact_publish'] && isset($config['publish_up']) && $config['publish_up'] != $publish_up) {
                // check in strtotime due to php's different behavior on 64bit machines
                $___now = $_now->toSql();

                $publishUp = $config['publish_up'] ?? null;

                $setPart = '';
                if ($publishUp && strtotime($publishUp) >= strtotime($___now)) {
                    $setPart = 'published = 0, is_future = 1, ';
                }

                $db->setQuery(
                    "UPDATE #__contentbuilder_records 
                    SET " . $setPart . " publish_up = " . ($publishUp ? $db->Quote($publishUp) : 'NULL') . " 
                    WHERE `type` = " . $db->Quote($form['type']) . " 
                    AND reference_id = " . $db->Quote($form['reference_id']) . " 
                    AND record_id = " . $db->Quote($record_id)
                );
                $db->execute();
            }

            // limited
            $publish_up = isset($config['publish_up']) ? $config['publish_up'] : $publish_up;

            if ($form['article_record_impact_publish'] && isset($config['publish_down']) && $config['publish_down'] != $publish_down) {
                $___now = $_now->toSql();

                $publishDown = $config['publish_down'] ?? null;

                $setPart = '';
                if ($publishDown && strtotime($publishDown) <= strtotime($___now)) {
                    $setPart = 'published = 0, ';
                }

                $db->setQuery(
                    "UPDATE #__contentbuilder_records 
                    SET " . $setPart . " publish_down = " . ($publishDown ? $db->Quote($publishDown) : 'NULL') . " 
                    WHERE `type` = " . $db->Quote($form['type']) . " 
                    AND reference_id = " . $db->Quote($form['reference_id']) . " 
                    AND record_id = " . $db->Quote($record_id)
                );

                $db->execute();
            }

            // limited
            $publish_down = isset($config['publish_down']) ? $config['publish_down'] : $publish_down;

            // limited
            $metakey = isset($config['metakey']) ? $config['metakey'] : '';

            // limited
            $metadesc = isset($config['metadesc']) ? $config['metadesc'] : '';

            $robots = '';
            $author = '';
            $rights = '';
            $xreference = '';

            if (!$limited_options) {

                // FULL
                $created_article = isset($config['created']) ? $config['created'] : null;

                // FULL
                if (Factory::getApplication()->isClient('administrator')) {
                    $created_by = isset($config['created_by']) ? $config['created_by'] : 0;
                }

                // FULL
                if (isset($config['attribs']) && is_array($config['attribs'])) {
                    $registry = new Registry();
                    $registry->loadArray($config['attribs']);
                    $attribs = (string) $registry;
                }

                // FULL
                if (isset($config['metadata']) && is_array($config['metadata'])) {

                    if (isset($config['metadata']['robots'])) {
                        $robots = $config['metadata']['robots'];
                    }

                    if (isset($config['metadata']['author'])) {
                        $author = $config['metadata']['author'];
                    }

                    if (isset($config['metadata']['rights'])) {
                        $rights = $config['metadata']['rights'];
                    }

                    if (isset($config['metadata']['xreference'])) {
                        $xreference = $config['metadata']['xreference'];
                    }

                    $registry = new Registry();
                    $registry->loadArray($config['metadata']);
                    $meta = (string) $registry;
                }
            }

            $db->setQuery("Update #__contentbuilder_records Set robots = " . $db->Quote($robots) . ", author = " . $db->Quote($author) . ", rights = " . $db->Quote($rights) . ", xreference = " . $db->Quote($xreference) . ", metakey = " . $db->Quote($metakey) . ", metadesc = " . $db->Quote($metadesc) . " Where `type` = " . $db->Quote($form['type']) . " And reference_id = " . $db->Quote($form['reference_id']) . " And record_id = " . $db->Quote($record_id));
            $db->execute();

            // Trigger the onContentBeforeSave event.
            $isNew = true;
            $table = Table::getInstance('content');

            if ($article > 0) {
                $table->load($article);
                $isNew = false;
            }

            $dispatcher = Factory::getApplication()->getDispatcher();
            $dispatcher->dispatch('onContentBeforeSave', new Joomla\Event\Event('onContentBeforeSave', array('com_content.article', &$table, $isNew)));
        }

        $created_by = $created_by ? $created_by : $metadata->created_id;
        $created = Factory::getDate()->toSql();

        $created = $created_article ? $created_article : ($metadata->created ? $metadata->created : $created);

        if ($created && strlen(trim($created)) <= 10) {
            $created .= ' 00:00:00';
        }

        if (!$publish_up) {
            $publish_up = $created;
        }

        if (!$publish_down && !$article) {
            $publish_down = NULL;
        }

        $alias = $alias ? self::stringURLUnicodeSlug($alias) : self::stringURLUnicodeSlug($label);
        if (trim(str_replace('-', '', $alias)) == '') {
            $datenow = Factory::getDate();
            $alias = $datenow->format("%Y-%m-%d-%H-%M-%S");
        }



        // not existing, create
        if (!$article) {

            $db->setQuery("Insert Into 
                    #__content 
                        (
                         `images`,
                         `urls`,
                         `title`,
                         `alias`,
                         `introtext`,
                         `fulltext`,
                         `state`,
                         `catid`,
                         `created`,
                         `created_by`,
                         `modified`,
                         `modified_by`,
                         `checked_out`,
                         `checked_out_time`,
                         `publish_up`,
                         `publish_down`,
                         `attribs`,
                         `version`,
                         `metakey`,
                         `metadesc`,
                         `metadata`,
                         `access`,
                         `created_by_alias`,
                         `ordering`,
                          featured,
                          language
                        ) 
                    Values 
                        (
                          '{\"image_intro\":\"\",\"image_intro_alt\":\"\",\"float_intro\":\"\",\"image_intro_caption\":\"\",\"image_fulltext\":\"\",\"image_fulltext_alt\":\"\",\"float_fulltext\":\"\",\"image_fulltext_caption\":\"\"}',
                          '{\"urla\":\"\",\"urlatext\":\"\",\"targeta\":\"\",\"urlb\":\"\",\"urlbtext\":\"\",\"targetb\":\"\",\"urlc\":\"\",\"urlctext\":\"\",\"targetc\":\"\"}',
                          " . $db->Quote($label) . ",
                          " . $db->Quote($alias) . ",
                          " . $db->Quote($introtext) . ",
                          " . $db->Quote($fulltext) . ",
                          " . $db->Quote($state) . ",
                          " . intval($form['default_category']) . ",
                          " . $db->Quote($created) . ",
                          " . $db->Quote($created_by ? $created_by : Factory::getApplication()->getIdentity()->get('id', 0)) . ",
                          " . $db->Quote($created) . ",
                          " . $db->Quote($created_by ? $created_by : Factory::getApplication()->getIdentity()->get('id', 0)) . ",
                          NULL,
                          NULL,
                          " . ($publish_up ? $db->Quote($publish_up) : 'NULL') . ",
                          " . ($publish_down ? $db->Quote($publish_down) : 'NULL') . ",
                          " . $db->Quote($attribs != '' ? $attribs : '{"article_layout":"","show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_page_title":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}') . ",
                          '1',
                          " . $db->Quote($metakey) . ",
                          " . $db->Quote($metadesc) . ",
                          " . $db->Quote($meta != '' ? $meta : '{"robots":"","author":"","rights":""}') . ",
                          " . $db->Quote($access) . ",
                          " . $db->Quote($created_by_alias) . ",
                          " . $db->Quote($ordering) . ",
                          " . $db->Quote($featured) . ",
                          " . $db->Quote($language) . "
                        )
            ");
            $db->execute();

            $article = $db->insertid();
            $datenow = Factory::getDate();
            $___datenow = $datenow->toSql();

            $db->setQuery("Insert Into #__contentbuilder_articles (`type`,`reference_id`,`last_update`,`article_id`,`record_id`,`form_id`) Values (" . $db->Quote($form['type']) . "," . $db->Quote($form['reference_id']) . "," . $db->Quote($___datenow) . ",$article," . $db->Quote($record_id) . "," . intval($contentbuilder_form_id) . ")");
            $db->execute();
            $db->setQuery("Update #__content Set introtext = concat('<div style=\'display:none;\'><!--(cbArticleId:$article)--></div>', introtext) Where id = $article");
            $db->execute();

            $db->setQuery("Select * From #__assets Where `name` = " . $db->quote('com_content.category.' . intval($form['default_category'])));
            $parent_asset = $db->loadAssoc();

            if ($parent_asset) {
                $parent_id = $parent_asset['id'];

                $db->setQuery("Insert Into #__assets (`rules`,`name`,title,parent_id, level, lft, rgt) Values ('{}'," . $db->quote('com_content.article.' . $article) . ", " . $db->quote($label) . "," . $db->quote($parent_id) . ",3,( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__assets As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__assets As mrgt) As filet ))");
                $db->execute();

                $asset_id = $db->insertid();

                $db->setQuery("Select max(mrgt.rgt)+1 From #__assets As mrgt");
                $rgt = $db->loadResult();

                $db->setQuery("Update `#__assets` Set rgt = " . $rgt . " Where `name` = 'root.1' And level = 0");
                $db->execute();

                $db->setQuery("Update `#__content` Set asset_id = " . $db->quote($asset_id) . " Where `id` = " . $db->quote($article));
                $db->execute();

                $db->setQuery("Insert Into #__workflow_associations (item_id, stage_id, extension) Values (" . $db->quote($article) . " , 1, 'com_content.article')");
                $db->execute();
            }

            // existing, update
        } else {
            $___datenow = Factory::getDate()->toSql();
            $modified = $metadata->modified ? $metadata->modified : $___datenow;
            $modified_by = $metadata->modified_id ? $metadata->modified_id : Factory::getApplication()->getIdentity()->get('id', 0);

            if ($full) {

                $db->setQuery("Update 
                        #__content 
                            Set
                             `title` = " . $db->Quote($label) . ",
                             `alias` = " . $db->Quote($alias) . ",
                             `introtext` = " . $db->Quote('<div style=\'display:none;\'><!--(cbArticleId:' . $article . ')--></div>' . $introtext) . ",
                             `fulltext` = " . $db->Quote($fulltext . '<div style=\'display:none;\'><!--(cbArticleId:' . $article . ')--></div>') . ",
                             `state` = " . $db->Quote($state) . ",
                             `catid` = " . intval($form['default_category']) . ",
                             `modified` = " . $db->Quote($modified) . ",
                             `modified_by` = " . $db->Quote($modified_by ? $modified_by : Factory::getApplication()->getIdentity()->get('id', 0)) . ",
                             `attribs` = " . $db->Quote($attribs != '' ? $attribs : '{"article_layout":"","show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_page_title":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}') . ",
                             `metakey` = " . $db->Quote($metakey) . ",
                             `metadesc` = " . $db->Quote($metadesc) . ",
                             `metadata` = " . $db->Quote($meta != '' ? $meta : '{"robots":"","author":"","rights":""}') . ",
                             `version` = `version`+1,
                             `created` = " . $db->Quote($created) . ",
                             `created_by` = " . $db->Quote($created_by) . ",
                             `created_by_alias` = " . $db->Quote($created_by_alias) . ",
                             `publish_up` = " . ($publish_up != '' ? $db->Quote($publish_up) : 'NULL') . ",
                             `publish_down` = " . ($publish_down != '' ? $db->Quote($publish_down) : 'NULL') . ",
                             `access` = " . $db->Quote($access) . ",
                             `ordering` = " . $db->Quote($ordering) . ",
                             featured = " . $db->Quote($featured) . ",
                             language = " . $db->Quote($language) . "
                        Where id = $article
                ");

                $db->execute();

                $db->setQuery("Select * From #__assets Where `name` = " . $db->quote('com_content.category.' . intval($form['default_category'])));
                $parent_asset = $db->loadAssoc();

                if ($parent_asset) {
                    $db->setQuery("Delete From `#__assets` Where `name` = " . $db->quote('com_content.article.' . $article));
                    $db->execute();

                    $parent_id = $parent_asset['id'];

                    $db->setQuery("Insert Into #__assets (`rules`,`name`,title,parent_id, level, lft, rgt) Values ('{}'," . $db->quote('com_content.article.' . $article) . ", " . $db->quote($label) . "," . $db->quote($parent_id) . ",3,( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__assets As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__assets As mrgt) As filet ))");
                    $db->execute();

                    $asset_id = $db->insertid();

                    $db->setQuery("Select max(mrgt.rgt)+1 From #__assets As mrgt");
                    $rgt = $db->loadResult();

                    $db->setQuery("Update `#__assets` Set rgt = " . $rgt . " Where `name` = 'root.1' And level = 0");
                    $db->execute();

                    $db->setQuery("Update `#__content` Set asset_id = " . $db->quote($asset_id) . " Where `id` = " . $db->quote($article));
                    $db->execute();

                }

            } else {
                $db->setQuery("Update 
                        #__content 
                            Set
                             `title` = " . $db->Quote($label) . ",
                             `alias` = " . $db->Quote($alias) . ",
                             `introtext` = " . $db->Quote('<div style=\'display:none;\'><!--(cbArticleId:' . $article . ')--></div>' . $introtext) . ",
                             `fulltext` = " . $db->Quote($fulltext . '<div style=\'display:none;\'><!--(cbArticleId:' . $article . ')--></div>') . ",
                             `state` = " . $db->Quote($state) . ",
                             `modified` = " . $db->Quote($modified) . ",
                             `modified_by` = " . $db->Quote($modified_by ? $modified_by : Factory::getApplication()->getIdentity()->get('id', 0)) . ",
                             `version` = `version`+1,
                             language=" . $db->Quote($language) . "
                        Where id = $article
                ");
            }
            $db->execute();

            $___datenow = $datenow->toSql();
            $db->setQuery("Update #__contentbuilder_articles Set `last_update` = " . $db->Quote($___datenow) . " Where `type` = " . $db->Quote($form['type']) . " And form_id = " . intval($contentbuilder_form_id) . " And reference_id = " . $db->Quote($form['reference_id']) . " And record_id = " . $db->Quote($record_id));
            $db->execute();
        }

        if ($article) {
            $row = Table::getInstance('content');
            if ($row->load($article)) {
                $row->reorder('catid = ' . (int) $form['default_category'] . ' AND state >= 0');
            }
        }

        // cleaning cache
        // Trigger the onContentCleanCache event.
        $conf = Factory::getConfig();
        $options = array(
            'defaultgroup' => 'com_content',
            'cachebase' => $conf->get('cache_path', JPATH_SITE . '/cache')
        );
        $cache = Factory::getCache('com_content');
        $cache->clean();
        $cache = Factory::getCache('com_contentbuilder');
        $cache->clean();

        $dispatcher = Factory::getApplication()->getDispatcher();
        $dispatcher->dispatch('onContentCleanCache', new Joomla\Event\Event('onContentCleanCache', $options));

        //// trigger onContentAfterSave event
        $isNew = true;
        $table = Table::getInstance('content');

        if ($article > 0) {
            $table->load($article);
            $isNew = false;
        }

        $dispatcher = Factory::getApplication()->getDispatcher();
        $eventResult = $dispatcher->dispatch('onContentAfterSave', new Joomla\Event\Event('onContentAfterSave', array('com_content.article', &$table, $isNew)));

        PluginHelper::importPlugin('contentbuilder_listaction');

        $dispatcher = Factory::getApplication()->getDispatcher();
        $eventResult = $dispatcher->dispatch('onAfterArticleCreation', new Joomla\Event\Event('onAfterArticleCreation', array($contentbuilder_form_id, $record_id, $article)));
        $results = $eventResult->getArgument('result') ?: [];

        $msg = implode('', $results);

        if ($msg) {
            Factory::getApplication()->enqueueMessage($msg);
        }

        return $article;
    }

    public static function setPermissions($form_id, $record_id = 0, $suffix = '')
    {

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where id = " . intval($form_id) . " And published = 1");
        $type = $db->loadAssoc();

        $num_records_query = '';
        if (is_array($type)) {
            $reference_id = $type['reference_id'];
            $type = $type['type'];
            $_type = $type;
            if (file_exists(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_contentbuilder/classes/types/' . $type . '.php');
                $type = 'contentbuilder_' . $type;
                if (class_exists($type)) {
                    $num_records_query = call_user_func(array($type, 'getNumRecordsQuery'), $reference_id, Factory::getApplication()->getIdentity()->get('id', 0));
                    //$num_records_query = $type::getNumRecordsQuery($reference_id, Factory::getApplication()->getIdentity()->get('id', 0));
                }
            } else if (file_exists(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php')) {
                require_once(JPATH_SITE . '/media/contentbuilder/types/' . $type . '.php');
                $type = 'contentbuilder_' . $type;
                if (class_exists($type)) {
                    $num_records_query = call_user_func(array($type, 'getNumRecordsQuery'), $reference_id, Factory::getApplication()->getIdentity()->get('id', 0));
                    //$num_records_query = $type::getNumRecordsQuery($reference_id, Factory::getApplication()->getIdentity()->get('id', 0));
                }
            }
        }

        /*
        $rec = 0;
        if(is_array($record_id)){
            $i = 0;
            $rec = '';
            $size = count($record_id);
            foreach($record_id As $rec_id){
                if($i+1 < $size){
                    $rec .= $db->Quote($rec_id).',';
                } else {
                    $rec .= $db->Quote($rec_id);
                }
                $i++;
            }
        } else {
           $rec = $db->Quote($record_id);
        }*/

        $db->setQuery("
            Select 
                forms.config,
                forms.verification_required_view,
                forms.verification_required_new,
                forms.verification_required_edit,
                forms.verification_days_view,
                forms.verification_days_new,
                forms.verification_days_edit,
                forms.verification_url_view,
                forms.verification_url_new,
                forms.verification_url_edit,
                contentbuilder_users.userid,
                forms.limit_add,
                forms.limit_edit,
                " . ($num_records_query ? '(' . $num_records_query . ') ' : "'0'") . " As amount_records,
                contentbuilder_users.verified_view,
                contentbuilder_users.verified_new,
                contentbuilder_users.verified_edit,
                contentbuilder_users.verification_date_view,
                contentbuilder_users.verification_date_new,
                contentbuilder_users.verification_date_edit,
                contentbuilder_users.limit_add As user_limit_add,
                contentbuilder_users.limit_edit As user_limit_edit,
                contentbuilder_users.published
                " . ($record_id && !is_array($record_id) ? ',contentbuilder_records.edited' : ",'0' As edited") . "
            From 
                #__contentbuilder_forms As forms
                Left Join 
                    #__contentbuilder_users As contentbuilder_users
                On ( contentbuilder_users.form_id = forms.id And contentbuilder_users.userid = " . Factory::getApplication()->getIdentity()->get('id', 0) . " )
                " . ($record_id && !is_array($record_id) ? "Left Join 
                    #__contentbuilder_records As contentbuilder_records
                On ( contentbuilder_records.`type` = " . $db->Quote(isset($_type) ? $_type : '') . " And contentbuilder_records.reference_id = forms.reference_id And contentbuilder_records.record_id = " . $db->Quote($record_id) . " )
                " : '') . "
            Where 
                forms.id = " . intval($form_id) . "
            And
                forms.published = 1
        ");
        $result = $db->loadAssoc();

        $config = unserialize(base64_decode($result['config']));

        Factory::getApplication()->getSession()->clear('permissions' . $suffix, 'com_contentbuilder');
        $permissions = array();

        //if(!$exclude_own){

        $permissions['published'] = true;

        if ($result['published'] !== null && !$result['published']) {

            $permissions['published'] = false;

        }

        $permissions['limit_edit'] = true;

        if (intval($result['limit_edit']) > 0 && intval($result['user_limit_edit']) > 0 && $result['edited'] >= intval($result['user_limit_edit'])) {

            $permissions['limit_edit'] = false;

        } else if (intval($result['limit_edit']) > 0 && intval($result['user_limit_edit']) <= 0 && $result['edited'] >= intval($result['limit_edit'])) {

            $permissions['limit_edit'] = false;

        }

        $permissions['limit_add'] = true;

        if (intval($result['limit_add']) > 0 && intval($result['user_limit_add']) > 0 && $result['amount_records'] >= intval($result['user_limit_add'])) {

            $permissions['limit_add'] = false;

        } else if (intval($result['limit_add']) > 0 && intval($result['user_limit_add']) <= 0 && $result['amount_records'] >= intval($result['limit_add'])) {

            $permissions['limit_add'] = false;

        }

        $jdate = Factory::getDate();

        $permissions['verify_view'] = true;
        if ($result['verification_required_view']) {
            $days = floatval($result['verification_days_view']) * 86400;

            $date = !empty($result['verification_date_view']) ? strtotime($result['verification_date_view']) : 0;
            $valid_until = $date + $days;
            $now = strtotime($jdate->toSql());

            if ($result['verified_view']) {
                if ($now < $valid_until || floatval($result['verification_days_view']) <= 0) {
                    $permissions['verify_view'] = true;
                } else {
                    $permissions['verify_view'] = trim($result['verification_url_view']) != '' ? trim($result['verification_url_view']) : false;
                }
            } else {
                $permissions['verify_view'] = trim($result['verification_url_view']) != '' ? trim($result['verification_url_view']) : false;
            }
        }

        $permissions['verify_new'] = true;
        if ($result['verification_required_new']) {
            $days = floatval($result['verification_days_new']) * 86400;

            $date = !empty($result['verification_date_new']) ? strtotime($result['verification_date_new']) : 0;

            $valid_until = $date + $days;
            $now = strtotime($jdate->toSql());

            if ($result['verified_new']) {
                if ($now < $valid_until || floatval($result['verification_days_new']) <= 0) {
                    $permissions['verify_new'] = true;
                } else {
                    $permissions['verify_new'] = trim($result['verification_url_new']) != '' ? trim($result['verification_url_new']) : false;
                }
            } else {
                $permissions['verify_new'] = trim($result['verification_url_new']) != '' ? trim($result['verification_url_new']) : false;
            }
        }

        $permissions['verify_edit'] = true;
        if ($result['verification_required_edit']) {
            $days = floatval($result['verification_days_edit']) * 86400;

            $date = !empty($result['verification_date_edit']) ? strtotime($result['verification_date_edit']) : 0;
            $valid_until = $date + $days;
            $now = strtotime($jdate->toSql());

            if ($result['verified_edit']) {
                if ($now < $valid_until || floatval($result['verification_days_edit']) <= 0) {
                    $permissions['verify_edit'] = true;
                } else {
                    $permissions['verify_edit'] = trim($result['verification_url_edit']) != '' ? trim($result['verification_url_edit']) : false;
                }
            } else {
                $permissions['verify_edit'] = trim($result['verification_url_edit']) != '' ? trim($result['verification_url_edit']) : false;
            }
        }

        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['view']) && $config['own' . $suffix]['view']) {
            if (!isset($permissions['own'])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['view'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['edit']) && $config['own' . $suffix]['edit']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['edit'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['delete']) && $config['own' . $suffix]['delete']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['delete'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['state']) && $config['own' . $suffix]['state']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['state'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['publish']) && $config['own' . $suffix]['publish']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['publish'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['fullarticle']) && $config['own' . $suffix]['fullarticle']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['fullarticle'] = array('own' => true, 'form_id' => $form_id, 'record_id' => $record_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['listaccess']) && $config['own' . $suffix]['listaccess']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['listaccess'] = array('own' => true, 'form_id' => $form_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['new']) && $config['own' . $suffix]['new']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['new'] = array('own' => true, 'form_id' => $form_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['language']) && $config['own' . $suffix]['language']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['language'] = array('own' => true, 'form_id' => $form_id);
        }
        if (isset($config['own' . $suffix]) && isset($config['own' . $suffix]['rating']) && $config['own' . $suffix]['rating']) {
            if (!isset($permissions['own' . $suffix])) {
                $permissions['own' . $suffix] = array();
            }
            $permissions['own' . $suffix]['rating'] = array('own' => true, 'form_id' => $form_id);
        }
        //}

        $db->setQuery("Select id From #__usergroups");
        $groups = $db->loadAssocList();

        foreach ($groups as $group) {
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['listaccess']) && $config['permissions' . $suffix][$group['id']]['listaccess']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['listaccess'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['view']) && $config['permissions' . $suffix][$group['id']]['view']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['view'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['new']) && $config['permissions' . $suffix][$group['id']]['new']) {
                if (!isset($permissions[$group['id']])) {

                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['new'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['edit']) && $config['permissions' . $suffix][$group['id']]['edit']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['edit'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['delete']) && $config['permissions' . $suffix][$group['id']]['delete']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['delete'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['state']) && $config['permissions' . $suffix][$group['id']]['state']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['state'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['publish']) && $config['permissions' . $suffix][$group['id']]['publish']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['publish'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['fullarticle']) && $config['permissions' . $suffix][$group['id']]['fullarticle']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['fullarticle'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['language']) && $config['permissions' . $suffix][$group['id']]['language']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['language'] = true;
            }
            if (isset($config['permissions' . $suffix][$group['id']]) && isset($config['permissions' . $suffix][$group['id']]['rating']) && $config['permissions' . $suffix][$group['id']]['rating']) {
                if (!isset($permissions[$group['id']])) {
                    $permissions[$group['id']] = array();
                }
                $permissions[$group['id']]['rating'] = true;
            }
        }

        Factory::getApplication()->getSession()->set('permissions' . $suffix, $permissions, 'com_contentbuilder');
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

        $allowed = false;
        $permissions = Factory::getApplication()->getSession()->get('permissions' . $suffix, array(), 'com_contentbuilder');

        $published_return = $permissions['published'];
        if (!$published_return) {
            if (!$auth) {
                Factory::getApplication()->enqueueMessage($error_msg, 'error');
                Factory::getApplication()->redirect('index.php');
            } else {
                return false;
            }
        }

        switch ($action) {
            case 'edit':
                $edit_return = $permissions['limit_edit'];
                if (!$edit_return) {
                    if (!$auth) {
                        Factory::getApplication()->enqueueMessage($error_msg, 'error');
                        Factory::getApplication()->redirect('index.php');
                    } else {
                        return false;
                    }
                }
                break;
        }

        switch ($action) {
            case 'new':
                $add_return = $permissions['limit_add'];
                if (!$add_return) {
                    if (!$auth) {
                        Factory::getApplication()->enqueueMessage($error_msg, 'error');
                        Factory::getApplication()->redirect('index.php');
                    } else {
                        return false;
                    }
                }
                break;
        }

        switch ($action) {
            case 'edit':
            case 'new':
            case 'view':
            case 'delete':
                $myaction = $action == 'delete' ? 'edit' : $action;
                $verify_return = $permissions['verify_' . $myaction];

                if ($verify_return !== true) {

                    if ($verify_return === false) {

                        if (!$auth) {
                            Factory::getApplication()->enqueueMessage($error_msg, 'error');
                            Factory::getApplication()->redirect('index.php');
                        } else {
                            return false;
                        }
                    } else if (is_string($verify_return)) {
                        if (!$auth) {
                            Factory::getApplication()->redirect($verify_return);
                        } else {
                            return false;
                        }
                    }
                }
                break;
        }

        if (!isset($permissions['own' . $suffix])) {
            $gids = array();

            $groups = Access::getGroupsByUser(Factory::getApplication()->getIdentity()->get('id', 0));

            foreach ($groups as $gid) {
                $gids[] = $gid;
            }

            foreach ($permissions as $group_id => $group_action) {
                if (isset($group_action[$action]) && $group_action[$action] && in_array($group_id, $gids)) {
                    $allowed = true;
                    break;
                }
            }

        } else {

            if (isset($permissions['own' . $suffix][$action])) {
                $user_return = $permissions['own' . $suffix][$action];
                if (is_array($user_return) && isset($user_return['own']) && $user_return['own']) {
                    $db = Factory::getContainer()->get(DatabaseInterface::class);

                    // XDA+GIL 08/JAN/2024 - PHP 8.3 Fix (double declaration of static bar)
                    static $typeref;

                    if (isset($typeref) && is_array($typeref) && isset($typeref[intval($user_return['form_id'])])) {
                        $typerefid = $typeref[intval($user_return['form_id'])];
                    } else {
                        $db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where id = " . intval($user_return['form_id']));
                        $typerefid = $db->loadAssoc();
                        $typeref[intval($user_return['form_id'])] = $typerefid;
                    }
                    if (is_array($typerefid)) {
                        $form = self::getForm($typerefid['type'], $typerefid['reference_id']);
                        if ($form && (!isset($user_return['record_id']))) {
                            $allowed = true;
                        } else {
                            if (is_array($user_return['record_id'])) {
                                foreach ($user_return['record_id'] as $recid) {
                                    $db->setQuery("Select session_id From #__contentbuilder_records Where `record_id` = " . $db->Quote($recid) . " And `type` = " . $db->Quote($typerefid['type']) . " And `reference_id` = " . $db->Quote($typerefid['reference_id']) . "");
                                    $session_id = $db->loadResult();
                                    if ($form && $session_id != Factory::getApplication()->getSession()->getId() && !$form->isOwner(Factory::getApplication()->getIdentity()->get('id', 0), $recid)) {
                                        $allowed = false;
                                        break;
                                    } else {
                                        $allowed = true;
                                    }
                                }
                            } else {

                                $db->setQuery("Select session_id From #__contentbuilder_records Where `record_id` = " . $db->Quote($user_return['record_id']) . " And `type` = " . $db->Quote($typerefid['type']) . " And `reference_id` = " . $db->Quote($typerefid['reference_id']) . "");
                                $session_id = $db->loadResult();

                                if ($form && ($user_return['record_id'] == false || $session_id == Factory::getApplication()->getSession()->getId() || ($form->isOwner(Factory::getApplication()->getIdentity()->get('id', 0), $user_return['record_id'])))) {
                                    $allowed = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$allowed) {
            if (!$auth) {
                Factory::getApplication()->redirect('index.php', 403);
            } else {
                return false;
            }
        }

        if ($auth) {
            return true;
        }
    }

    public static function authorize($action)
    {
        return self::checkPermissions($action, '', '', true);
    }

    public static function authorizeFe($action)
    {
        return self::checkPermissions($action, '', '_fe', true);
    }

    public static function getListStates($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select * From #__contentbuilder_list_states where form_id = " . intval($id) . " And published = 1 Order By id");
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
            $imp .= $db->Quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
            $i++;
        }
        if ($imp) {
            $db->setQuery("Select states.color, records.record_id From #__contentbuilder_list_states As states, #__contentbuilder_list_records As records Where states.published = 1 And states.id = records.state_id And records.record_id In (" . $imp . ") And records.form_id = " . intval($id) . " And states.form_id = " . intval($id));
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
            $imp .= $db->Quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
            $i++;
        }
        if ($imp) {
            $db->setQuery("Select states.title, records.record_id From #__contentbuilder_list_states As states, #__contentbuilder_list_records As records Where states.published = 1 And states.id = records.state_id And records.record_id In (" . $imp . ") And records.form_id = " . intval($id) . " And states.form_id = " . intval($id));
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
                $imp .= $db->Quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
                $i++;
            }

            if ($imp) {
                $db->setQuery("Select records.published, records.record_id From #__contentbuilder_records As records Where `type` = " . $db->Quote($type) . " And reference_id = " . $db->Quote($reference_id) . " And records.record_id In (" . $imp . ")");
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
                $imp .= $db->Quote($item->colRecord) . ($i + 1 < $itemcnt ? ',' : '');
                $i++;
            }

            if ($imp) {
                $db->setQuery("Select records.lang_code, records.record_id From #__contentbuilder_records As records Where reference_id = " . $db->Quote($reference_id) . " And records.record_id In (" . $imp . ")");
                $codes = $db->loadAssocList();
                foreach ($codes as $code) {
                    $out[$code['record_id']] = $code['lang_code'];
                }
            }
        }
        return $out;
    }
}
