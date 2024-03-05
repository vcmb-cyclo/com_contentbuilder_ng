<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 5.0
 * @package BreezingForms
 * @copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

function bf_sanitizeFilename($fileName, $defaultIfEmpty = 'upload', $separator = '_', $lowerCase = true)
{
	// Gather file informations and store its extension
	$fileInfos = pathinfo($fileName);
	$fileExt = array_key_exists('extension', $fileInfos) ? '.' . strtolower($fileInfos['extension']) : '';

	// Removes accents

	if (function_exists('transliterator_transliterate')) {

		$fileName = @transliterator_transliterate('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();', $fileInfos['filename']);
		$fileName = JFilterOutput::stringURLSafe($fileName);
		if (trim($fileName) == '') {

			$allowed = "/[^a-z0-9\\.\\-\\_]/i";
			$fileName = preg_replace($allowed, "_", $fileInfos['filename']);
		} else {
			// Removes all characters that are not separators, letters, numbers, dots or whitespaces
			$fileName = preg_replace("/[^ a-zA-Z" . preg_quote($separator) . "\d\.\s]/", '', $lowerCase ? strtolower($fileName) : $fileName);

			// Replaces all successive separators into a single one
			$fileName = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $fileName);

			// Trim beginning and ending seperators
			$fileName = trim($fileName, $separator);
		}
	} else {
		$allowed = "/[^a-z0-9\\.\\-\\_]/i";
		$fileName = preg_replace($allowed, "_", $fileInfos['filename']);
	}

	// If empty use the default string
	if (empty($fileName)) {
		$allowed = "/[^a-z0-9\\.\\-\\_]/i";
		$fileName = preg_replace($allowed, "_", $fileInfos['filename']);
	}

	if (empty($fileName)) {
		$fileName = $defaultIfEmpty;
	}

	return $fileName . $fileExt;
}

function bf_tooltipText($title = '', $content = '', $translate = 1, $escape = 1)
{
	HTMLHelper::_('bootstrap.tooltip');

	// Return empty in no title or content is given.
	if ($title == '' && $content == '') {
		return '';
	}

	// Split title into title and content if the title contains '::' (old Mootools format).
	if ($content == '' && !(strpos($title, '::') === false)) {
		list($title, $content) = explode('::', $title, 2);
	}

	// Pass texts through the Text.
	if ($translate) {
		$title = Text::_($title);
		$content = Text::_($content);
	}

	// Escape the texts.
	if ($escape) {
		$title = str_replace('"', '&quot;', $title);
		$content = str_replace('"', '&quot;', $content);
	}

	// Return only the content if no title is given.
	if ($title == '') {
		return $content;
	}

	// Return only the title if title and text are the same.
	if ($title == $content) {
		return '<strong>' . $title . '</strong>';
	}

	// Return the formated sting combining the title and  content.
	if ($content != '') {
		return '<strong>' . $title . '</strong><br />' . $content;
	}

	// Return only the title.
	return $title;
}

function bf_stringURLUnicodeSlug($string)
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
	$str = trim(JString::strtolower($str));

	// Remove any duplicate whitespace and replace whitespaces by hyphens
	$str = preg_replace('#\x20+#', '-', $str);

	return $str;
}

function bf_cleanString($string)
{
	return str_replace(array('[', ']', '{', '}', '(', ')', '|'), array(
		'&#91;',
		'&#93;',
		'&#123;',
		'&#125;',
		'&#40;',
		'&#41;',
		'&#124;'
	), $string);
}

function bf_startsWith($haystack, $needle)
{
	$length = strlen($needle);

	return(substr($haystack, 0, $length) === $needle);
}

function bf_endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return(substr($haystack, -$length) === $needle);
}


function bf_is_mobile()
{
	$is_mobile = false;

	// Check user agent string
	$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

	if (empty($agent)) {
		return false;
	}

	$mobile_devices = array(
		'is_iphone' => 'iphone',
		'is_ipad' => 'ipad',
		'is_ipod' => 'ipod',
		'is_kindle' => 'kindle',
		'is_surface' => 'Windows NT [0-9.]+; ARM;',
		'is_windows_phone' => 'Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7'
	);

	$mobile_oss = array(
		'is_ios' => 'ip(hone|ad|od)',
		'is_android' => 'android',
		'is_webos' => '(web|hpw)os',
		'is_palmos' => 'palm(\s?os|source)',
		'is_windows' => 'windows (phone|ce)',
		'is_symbian' => 'symbian(\s?os|)|symbos',
		'is_bbos' => 'blackberry(.*?version\/\d+|\d+\/\d+)',
		'is_bada' => 'bada'
	);

	$mobile_browsers = array(
		'is_opera_mobile' => 'opera (mobi|mini)',
		// Opera Mobile or Mini
		'is_webkit_mobile' => '(android|nokia|webos|hpwos|blackberry).*?webkit|webkit.*?(mobile|kindle|bolt|skyfire|dolfin|iris)',
		// Webkit mobile
		'is_firefox_mobile' => 'fennec',
		// Firefox mobile
		'is_ie_mobile' => 'iemobile|windows ce',
		// IE mobile
		'is_netfront' => 'netfront|kindle|psp|blazer|jasmine',
		// Netfront
		'is_uc_browser' => 'ucweb'
		// UC browser
	);

	$groups = array($mobile_devices, $mobile_oss, $mobile_browsers);

	foreach ($groups as $group) {
		foreach ($group as $name => $regex) {
			if (preg_match('/' . $regex . '/i', $agent)) {
				$is_mobile = true;
				break;
			}
		}
	}

	// Fallbacks
	if ($is_mobile === false) {

		$useragent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
			return true;
		}

		$regex = 'nokia|motorola|sony|ericsson|lge?(-|;|\/|\s)|htc|samsung|asus|mobile|phone|tablet|pocket|wap|wireless|up\.browser|up\.link|j2me|midp|cldc|kddi|mmp|obigo|novarra|teleca|openwave|uzardweb|pre\/|hiptop|avantgo|plucker|xiino|elaine|vodafone|sprint|o2';
		$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

		if (
			false !== strpos($accept, 'text/vnd.wap.wml')
			|| false !== strpos($accept, 'application/vnd.wap.xhtml+xml')
			|| isset($_SERVER['HTTP_X_WAP_PROFILE'])
			|| isset($_SERVER['HTTP_PROFILE'])
			|| preg_match('/' . $regex . '/i', $agent)
		) {
			$is_mobile = true;
		}
	}

	return $is_mobile;
}

/**
 * Mail creator as expected by former FacileForms code
 * This is a not really Legacy, so it stays like that
 *
 * @param string $from
 * @param string $fromname
 * @param string $subject
 * @param string $body
 *
 * @return JMail
 */



function bf_getFieldSelectorList($form_id, $element_target_id)
{
	$db = Factory::getContainer()->get(DatabaseInterface::class);
	$db->setQuery("Select `name` From #__facileforms_elements Where form = " . intval($form_id) . " And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5','bfFakeName6') Order by `ordering`");

	$rows = $db->loadColumn();
	$out = '<script type="text/javascript">
    function insertAtCursor_' . $element_target_id . '(myValue) {
var myField = document.getElementById("' . $element_target_id . '");
//IE support
if (document.selection) {
myField.focus();
sel = document.selection.createRange();
sel.text = myValue;
}
//MOZILLA/NETSCAPE support
else if (myField.selectionStart || myField.selectionStart == \'0\') {
var startPos = myField.selectionStart;
var endPos = myField.selectionEnd;
myField.value = myField.value.substring(0, startPos)
+ myValue
+ myField.value.substring(endPos, myField.value.length);
} else {
myField.value += myValue;
}
}

    </script>';
	if ($rows) {
		foreach ($rows as $row) {
			$out .= '<a href="javascript: insertAtCursor_' . $element_target_id . '(\'{' . $row . ':label}\');void(0);">{' . $row . ':label}</a><br/>';
			$out .= '<a href="javascript: insertAtCursor_' . $element_target_id . '(\'{' . $row . ':value}\');void(0);">{' . $row . ':value}</a><br/><br/>';
		}
	}

	return $out;
}

/*
   additional function for inserting fields to editor
*/
function bf_getFieldSelectorListEditor($form_id, $element_target_id)
{
	$db = Factory::getContainer()->get(DatabaseInterface::class);
	$db->setQuery("Select `name` From #__facileforms_elements Where form = " . intval($form_id) . " And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5','bfFakeName6') Order by `ordering`");
	$rows = $db->loadColumn();
	$out = '<script type="text/javascript">
    function insertAtCursor_' . $element_target_id . '_Editor(myValue) {
        var content = Joomla.editors.instances[' . json_encode($element_target_id) . '].getValue();
        var splitPos = content.lastIndexOf("<");
        var combined = content.substring(0, splitPos) + myValue + content.substring(splitPos);
        jQuery("#' . $element_target_id . '_div iframe").contents().find("body").html(combined);
    }
    </script>';

	if ($rows) {
		foreach ($rows as $row) {
			$out .= '<a href="javascript: insertAtCursor_' . $element_target_id . '_Editor(\'{' . $row . ':label}\');void(0);">{' . $row . ':label}</a><br/>';
			$out .= '<a href="javascript: insertAtCursor_' . $element_target_id . '_Editor(\'{' . $row . ':value}\');void(0);">{' . $row . ':value}</a><br/><br/>';
		}
	}

	return $out;

}

function bf_getFieldSelectorListHTML($form_id, $editor, $element_target_id)
{
	$db = Factory::getContainer()->get(DatabaseInterface::class);
	$db->setQuery("Select `name` From #__facileforms_elements Where form = " . intval($form_id) . " And `name` Not In ('bfFakeName','bfFakeName2','bfFakeName3','bfFakeName4','bfFakeName5','bfFakeName6') Order by `ordering`");
	$rows = $db->loadColumn();
	$out = '<script type="text/javascript">
    function insert_' . $element_target_id . 'HTML(myValue) {
        var content = ' . $editor->getContent($element_target_id) . ';
        ' . $editor->setContent($element_target_id, 'content+myValue') . ';
    }
    </script>';
	if ($rows) {
		foreach ($rows as $row) {
			$out .= '<a href="javascript: insert_' . $element_target_id . 'HTML(\'{' . $row . ':label}\');void(0);">{' . $row . ':label}</a><br/>';
			$out .= '<a href="javascript: insert_' . $element_target_id . 'HTML(\'{' . $row . ':value}\');void(0);">{' . $row . ':value}</a><br/><br/>';
		}
	}

	return $out;
}

function bf_ToolTip($tooltip, $title = '', $width = '', $image = 'tooltip.png', $text = '', $href = '', $link = 1)
{
	// Initialize the toolips if required
	static $init;
	if (!$init) {
		//JHTML::_( 'bootstrap.tooltip' );
		$init = true;
	}

	return JHTML::_('tooltip', $tooltip, $title, $image, $text, $href, $link);
}

// used if copy is disabled
function bf_copy($file1, $file2)
{
	$contentx = @file_get_contents($file1);
	$openedfile = @fopen($file2, "w");
	@fwrite($openedfile, $contentx);
	@fclose($openedfile);
	if ($contentx === false) {
		$status = false;
	} else {
		$status = true;
	}

	return $status;
}

function bf_createMail($from, $fromname, $subject, $body, $alt_sender = '')
{

	$_mailfrom = '';
	$_fromname = '';

	$_mailfrom = Factoryonfig()->get('mailfrom', '');
	$_fromname = Factoryonfig()->get('fromname', '');

	$mail = Factoryailer();

	/*
				try {

					$mail->setSender( array( $alt_sender ? $alt_sender : $_mailfrom, $fromname ? $fromname : $_fromname ) );

				} catch ( Exception $e ) {

				}*/

	$mail->setSubject($subject);
	$mail->setBody($body);

	$prev_from = $alt_sender ? $alt_sender : $_mailfrom;


	try {

		//$mail->SetFrom( $prev_from, $fromname ? $fromname : $_fromname );
		//$mail->SetFrom( $from ? $from : '', $fromname ? $fromname : '' );
		//$mail->setSender( array( $prev_from, $fromname ? $fromname : $_fromname ) );
		//$mail->setSender( array( $from ? $from : $prev_from, $fromname ? $fromname : $_fromname ) );
		$mail->setSender(array($prev_from, $fromname ? $fromname : $_fromname));

	} catch (Exception $e) {

	}

	try {

		if ($from && $from != $prev_from) {


			$newfrom = $from ? $from : $_mailfrom;
			$newfromname = $fromname ? $fromname : $_fromname;

			if (!empty($newfrom)) {

				$mail->addReplyTo($from, $fromname ? $fromname : $_fromname);
			}

		}

	} catch (Exception $e) {

	}

	return $mail;
}

function bf_sendNotificationBySession($session)
{

	$contents = Factoryession()->get($session, array());

	if (count($contents) != 0) {

		$from = $contents['from'];
		$fromname = $contents['fromname'];
		$recipient = $contents['recipients'];
		$subject = $contents['subject'];
		$body = $contents['body'];
		$attachment = $contents['attachment'];
		$html = $contents['isHtml'];
		$alt_sender = $contents['alt_sender'];

		if ((is_array($recipient) && count($recipient) != 0) || (!is_array($recipient) && $recipient != '')) {

			$mail = bf_createMail($from, $fromname, $subject, $body, $alt_sender);
			if (is_array($recipient)) {
				foreach ($recipient as $to) {
					$mail->AddAddress($to);
				}
			} else {
				$mail->AddAddress($recipient);
			}

			if ($attachment) {
				if (is_array($attachment)) {
					foreach ($attachment as $fname) {
						$mail->AddAttachment($fname);
					}
				} else {
					$mail->AddAttachment($attachment);
				}
			} // if

			if (isset($html)) {
				$mail->IsHTML($html);
			}

			$mail->Send();
		}
	}

	Factoryession()->set($session, array());
}

function bf_sendNotificationByPaymentCache($formId, $recordId, $type = 'admin')
{

	$contents = array();
	$sourcePath = JPATH_SITE . '/media/breezingforms/payment_cache/';
	if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
		while (false !== ($file = @readdir($handle))) {
			if ($file != "." && $file != "..") {
				$parts = explode('_', $file);
				if (count($parts) == 4) {
					if ($parts[0] == intval($formId) && $parts[1] == intval($recordId) && $parts[2] == $type) {
						$contents = unserialize(BFFile::read($sourcePath . $file));
						File::delete($sourcePath . $file);
						break;
					}
				}
			}
		}
		@closedir($handle);
	}

	if (count($contents) != 0) {

		$from = $contents['from'];
		$fromname = $contents['fromname'];
		$recipient = $contents['recipients'];
		$subject = $contents['subject'];
		$body = $contents['body'];
		$attachment = $contents['attachment'];
		$html = $contents['isHtml'];
		$alt_sender = $contents['alt_sender'];

		if ((is_array($recipient) && count($recipient) != 0) || (!is_array($recipient) && $recipient != '')) {

			$mail = bf_createMail($from, $fromname, $subject, $body, $alt_sender);
			if (is_array($recipient)) {
				foreach ($recipient as $to) {
					$mail->AddAddress($to);
				}
			} else {
				$mail->AddAddress($recipient);
			}

			if ($attachment) {
				if (is_array($attachment)) {
					foreach ($attachment as $fname) {
						$mail->AddAttachment($fname);
					}
				} else {
					$mail->AddAttachment($attachment);
				}
			} // if

			if (isset($html)) {
				$mail->IsHTML($html);
			}

			$mail->Send();
		}
	}
}

/**
 * The name says it all
 *
 * @param string $string
 *
 * @return boolean
 */
function bf_isUTF8($string)
{
	if (is_array($string)) {
		$enc = implode('', $string);

		return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
	} else {
		return(utf8_encode(utf8_decode($string)) == $string);
	}
}

/**
 * The classic recursive slash remover
 *
 * @param string $value raw
 *
 * @return string cleaned
 */
function bf_stripslashes_deep($value)
{
	if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc()) {
		$value = is_array($value) ?
			array_map('bf_stripslashes_deep', $value) :
			stripslashes($value);
	}

	return $value;
}

function bf_is_email($email, $checkDNS = false)
{
	//      Check that $email is a valid address
	//              (http://tools.ietf.org/html/rfc3696)
	//              (http://tools.ietf.org/html/rfc2822)
	//              (http://tools.ietf.org/html/rfc5322#section-3.4.1)
	//              (http://tools.ietf.org/html/rfc5321#section-4.1.3)
	//              (http://tools.ietf.org/html/rfc4291#section-2.2)
	//              (http://tools.ietf.org/html/rfc1123#section-2.1)

	//      the upper limit on address lengths should normally be considered to be 256
	//              (http://www.rfc-editor.org/errata_search.php?rfc=3696)
	if (strlen($email) > 256) {
		return false;
	}   //      Too long

	//      Contemporary email addresses consist of a "local part" separated from
	//      a "domain part" (a fully-qualified domain name) by an at-sign ("@").
	//              (http://tools.ietf.org/html/rfc3696#section-3)
	$index = strrpos($email, '@');

	if ($index === false) {
		return false;
	}   //      No at-sign
	if ($index === 0) {
		return false;
	}   //      No local part
	if ($index > 64) {
		return false;
	}   //      Local part too long

	$localPart = substr($email, 0, $index);
	$domain = substr($email, $index + 1);
	$domainLength = strlen($domain);

	if ($domainLength === 0) {
		return false;
	}   //      No domain part
	if ($domainLength > 255) {
		return false;
	}   //      Domain part too long

	//      Let's check the local part for RFC compliance...
	//
	//      local-part      =       dot-atom / quoted-string / obs-local-part
	//      obs-local-part  =       word *("." word)
	//              (http://tools.ietf.org/html/rfc2822#section-3.4.1)
	if (preg_match('/^"(?:.)*"$/', $localPart) > 0) {
		$dotArray[] = $localPart;
	} else {
		$dotArray = explode('.', $localPart);
	}

	foreach ($dotArray as $localElement) {
		//      Period (".") may...appear, but may not be used to start or end the
		//      local part, nor may two or more consecutive periods appear.
		//              (http://tools.ietf.org/html/rfc3696#section-3)
		//
		//      A zero-length element implies a period at the beginning or end of the
		//      local part, or two periods together. Either way it's not allowed.
		if ($localElement === '') {
			return false;
		}   //      Dots in wrong place

		//      Each dot-delimited component can be an atom or a quoted string
		//      (because of the obs-local-part provision)
		if (preg_match('/^"(?:.)*"$/', $localElement) > 0) {
			//      Quoted-string tests:
			//
			//      Note that since quoted-pair
			//      is allowed in a quoted-string, the quote and backslash characters may
			//      appear in a quoted-string so long as they appear as a quoted-pair.
			//              (http://tools.ietf.org/html/rfc2822#section-3.2.5)
			$groupCount = preg_match_all('/(?:^"|"$|\\\\\\\\|\\\\")|(\\\\|")/', $localElement, $matches);
			array_multisort($matches[1], SORT_DESC);
			if ($matches[1][0] !== '') {
				return false;
			}   //      Unescaped quote or backslash character inside quoted string
			if (preg_match('/^"\\\\*"$/', $localElement) > 0) {
				return false;
			}   //      "" and "\" are slipping through - note: must tidy this up
		} else {
			//      Unquoted string tests:
			//
			//      Any ASCII graphic (printing) character other than the
			//      at-sign ("@"), backslash, double quote, comma, or square brackets may
			//      appear without quoting.  If any of that list of excluded characters
			//      are to appear, they must be quoted
			//              (http://tools.ietf.org/html/rfc3696#section-3)
			//
			$stripped = '';
			//      Any excluded characters? i.e. <space>, @, [, ], \, ", <comma>
			if (preg_match('/[ @\\[\\]\\\\",]/', $localElement) > 0) //      Check all excluded characters are escaped
			{
				$stripped = preg_replace('/\\\\[ @\\[\\]\\\\",]/', '', $localElement);
			}
			if (preg_match('/[ @\\[\\]\\\\",]/', $stripped) > 0) {
				return false;
			}   //      Unquoted excluded characters
		}
	}

	//      Now let's check the domain part...

	//      The domain name can also be replaced by an IP address in square brackets
	//              (http://tools.ietf.org/html/rfc3696#section-3)
	//              (http://tools.ietf.org/html/rfc5321#section-4.1.3)
	//              (http://tools.ietf.org/html/rfc4291#section-2.2)
	if (preg_match('/^\\[(.)+]$/', $domain) === 1) {
		//      It's an address-literal
		$addressLiteral = substr($domain, 1, $domainLength - 2);
		$matchesIP = array();

		//      Extract IPv4 part from the end of the address-literal (if there is one)
		if (preg_match('/\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $addressLiteral, $matchesIP) > 0) {
			$index = strrpos($addressLiteral, $matchesIP[0]);

			if ($index === 0) {
				//      Nothing there except a valid IPv4 address, so...
				return true;
			} else {
				//      Assume it's an attempt at a mixed address (IPv6 + IPv4)
				if ($addressLiteral[$index - 1] !== ':') {
					return false;
				}   //      Character preceding IPv4 address must be ':'
				if (substr($addressLiteral, 0, 5) !== 'IPv6:') {
					return false;
				}   //      RFC5321 section 4.1.3

				$IPv6 = substr($addressLiteral, 5, ($index === 7) ? 2 : $index - 6);
				$groupMax = 6;
			}
		} else {
			//      It must be an attempt at pure IPv6
			if (substr($addressLiteral, 0, 5) !== 'IPv6:') {
				return false;
			}   //      RFC5321 section 4.1.3
			$IPv6 = substr($addressLiteral, 5);
			$groupMax = 8;
		}

		$groupCount = preg_match_all('/^[0-9a-fA-F]{0,4}|\\:[0-9a-fA-F]{0,4}|(.)/', $IPv6, $matchesIP);
		$index = strpos($IPv6, '::');

		if ($index === false) {
			//      We need exactly the right number of groups
			if ($groupCount !== $groupMax) {
				return false;
			}   //      RFC5321 section 4.1.3
		} else {
			if ($index !== strrpos($IPv6, '::')) {
				return false;
			}   //      More than one '::'
			$groupMax = ($index === 0 || $index === (strlen($IPv6) - 2)) ? $groupMax : $groupMax - 1;
			if ($groupCount > $groupMax) {
				return false;
			}   //      Too many IPv6 groups in address
		}

		//      Check for unmatched characters
		array_multisort($matchesIP
		[1], SORT_DESC);
		if ($matchesIP[1][0] !== '') {
			return false;
		}   //      Illegal characters in address

		//      It's a valid IPv6 address, so...
		return true;
	} else {
		//      It's a domain name...

		//      The syntax of a legal Internet host name was specified in RFC-952
		//      One aspect of host name syntax is hereby changed: the
		//      restriction on the first character is relaxed to allow either a
		//      letter or a digit.
		//              (http://tools.ietf.org/html/rfc1123#section-2.1)
		//
		//      NB RFC 1123 updates RFC 1035, but this is not currently apparent from reading RFC 1035.
		//
		//      Most common applications, including email and the Web, will generally not permit...escaped strings
		//              (http://tools.ietf.org/html/rfc3696#section-2)
		//
		//      Characters outside the set of alphabetic characters, digits, and hyphen MUST NOT appear in domain name
		//      labels for SMTP clients or servers
		//              (http://tools.ietf.org/html/rfc5321#section-4.1.2)
		//
		//      RFC5321 precludes the use of a trailing dot in a domain name for SMTP purposes
		//              (http://tools.ietf.org/html/rfc5321#section-4.1.2)
		$matches = array();
		$groupCount = preg_match_all('/(?:[0-9a-zA-Z][0-9a-zA-Z-]{0,61}[0-9a-zA-Z]|[a-zA-Z])(?:\\.|$)|(.)/', $domain, $matches);
		$level = count($matches[0]);

		if ($level == 1) {
			return false;
		}   //      Mail host can't be a TLD

		$TLD = $matches[0][$level - 1];
		if (substr($TLD, strlen($TLD) - 1, 1) === '.') {
			return false;
		}   //      TLD can't end in a dot
		if (preg_match('/^[0-9]+$/', $TLD) > 0) {
			return false;
		}   //      TLD can't be all-numeric

		//      Check for unmatched characters
		array_multisort($matches[1], SORT_DESC);
		if ($matches[1][0] !== '') {
			return false;
		}   //      Illegal characters in domain, or label longer than 63 characters

		//      Check DNS?
		if ($checkDNS && function_exists('checkdnsrr')) {
			if (!(checkdnsrr($domain, 'A') || checkdnsrr($domain, 'MX'))) {
				return false;   //      Domain doesn't actually exist
			}
		}

		//      Eliminate all other factors, and the one which remains must be the truth.
		//              (Sherlock Holmes, The Sign of Four)
		return true;
	}
}

function BFRedirect($link, $msg = null)
{
	$mainframe = Factorypplication();
	$mainframe->enqueueMessage($msg);
	$mainframe->redirect($link);
}