<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class plgContentbuilder_verifyPaypal extends CMSPlugin
{
    private $test = false;
    private $url = 'https://www.paypal.com';
    private $business = '';
    private $token = '';
    private $locale = 'EN';
    private $curreny = 'USD';
    private $tax = 0;
    private $ipn = false;
    private $item_number = 1;
    private $name = 'Item';
    private $amount = 0;
    private $cancel_url = '';

    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);

        $plugin = PluginHelper::getPlugin('contentbuilder_verify', 'paypal');
        $pluginParams = (new Registry)->loadString($plugin->params);

        if ($pluginParams->def('test', 0)) {
            $this->test = true;
            $this->business = $pluginParams->def('test_business', '');
            $this->token = $pluginParams->def('test_token', '');
            $this->url = 'https://www.sandbox.paypal.com';
        } else {
            $this->business = $pluginParams->def('business', '');
            $this->token = $pluginParams->def('token', '');
        }
    }

    /**
     * Will be called in the content element (article or record)
     * If the return is not empty, it will render the returned value.
     * 
     * By that things like coupon codes may be implemented.
     * 
     * @param string $link the link that points to the verifier
     * @param string $plugin_settings A query string with the plugin options
     * @return string empty for nothing (default) or a string to render instead of the default
     */
    function onViewport($link, $plugin_settings)
    {

        return '';
    }

    /**
     * Will always be called by the verifier
     * 
     * @param type $return_url
     * @param type $options
     * @return string empty if everything is ok, else a message describing the problem 
     */
    function onSetup($return_url, $options)
    {

        if (isset($options['plugin_options']) && isset($options['plugin_options']['amount']) && is_numeric($options['plugin_options']['amount'])) {

            $this->amount = $options['plugin_options']['amount'];
            $this->name = $options['verification_name'];
            $this->locale = isset($options['plugin_options']['locale']) && $options['plugin_options']['locale'] ? strtoupper($options['plugin_options']['locale']) : 'EN';
            $this->curreny = isset($options['plugin_options']['currency-code']) && $options['plugin_options']['currency-code'] ? strtoupper($options['plugin_options']['currency-code']) : 'USD';
            $this->tax = isset($options['plugin_options']['tax']) && $options['plugin_options']['tax'] ? $options['plugin_options']['tax'] : 0;
            $this->ipn = isset($options['plugin_options']['use-ipn']) && $options['plugin_options']['use-ipn'] == 'true' ? true : false;
            $this->item_number = isset($options['plugin_options']['item-number']) && $options['plugin_options']['item-number'] ? $options['plugin_options']['item-number'] : 1;
            $this->cancel_url = htmlentities(isset($options['plugin_options']['cancel-url']) && $options['plugin_options']['cancel-url'] ? $options['plugin_options']['cancel-url'] : str_replace('&verify=1', '', $return_url));

        } else {
            return 'Please specify an amount (price) for PayPal payment';
        }
        return '';
    }

    /**
     * Will be called on forward, right after setup IF there is no verification yet
     * 
     * @param string $return_url
     * @param array $options 
     */
    function onForward($return_url, $options)
    {
        ob_end_clean();

        echo '
        <html>
        <head></head>
        <body>' . "\n";

        $html = 'Please wait while we process your request...';
        $html .= "<form name=\"cb_paypal_form\" action=\"" . $this->url . "/cgi-bin/webscr\" method=\"post\">" . "\n";
        $html .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"business\" value=\"" . $this->business . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $this->name . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $this->item_number . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"amount\" value=\"" . $this->amount . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"tax\" value=\"" . $this->tax . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"no_note\" value=\"1\"/>" . "\n";
        if ($this->ipn) {
            $html .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $return_url . '&paypal_ipn=true' . "\"/>" . "\n";
            if ($this->test) {
                $html .= "<input type=\"hidden\" name=\"test_ipn\" value=\"1\"/>" . "\n";
            }
        }
        $html .= "<input type=\"hidden\" name=\"return\" value=\"" . $return_url . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"cancel_return\" value=\"" . $this->cancel_url . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"lc\" value=\"" . $this->locale . "\"/>" . "\n";
        $html .= "<input type=\"hidden\" name=\"currency_code\" value=\"" . $this->curreny . "\"/>" . "\n";
        $html .= '</form>' . "\n";
        $html .= '<script type="text/javascript">document.cb_paypal_form.submit();</script>' . "\n";

        echo $html;

        echo '</body>
        </html>
        ' . "\n";
        exit;
    }

    /**
     * Will be called on verification
     * 
     * @param string $return_url
     * @param array $options
     * @return mixed boolean false on errors or an array with the payment data 
     */
    function onVerify($return_url, $options)
    {

        // the paypal_ipn parameter has been attached in onForward
        // so if PayPal returns with this parameter we should use verification through IPN
        if ($this->ipn && CBRequest::getVar('paypal_ipn', '') == 'true') {
            return $this->verifyIpn($return_url, $options);
        }

        $auth_token = $this->token;

        $paypal = 'https://www.paypal.com';
        if ($this->test) {
            $paypal = 'https://www.sandbox.paypal.com';
        }

        $req = 'cmd=_notify-synch';

        $tx_token = CBRequest::getVar('tx', 0);
        $req .= "&tx=" . urlencode($tx_token) . "&at=" . urlencode($auth_token);

        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        if (function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

            ob_start();
            curl_exec($ch);
            $res = ob_get_contents();
            curl_close($ch);
            ob_end_clean();

        } else {
            // try fsockopen
            $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
            fputs($fp, $header . $req);
            $res = '';
            $headerdone = false;
            while (!feof($fp)) {
                $line = fgets($fp, 1024);
                if (strcmp($line, "\r\n") == 0) {
                    $headerdone = true;
                } else if ($headerdone) {
                    $res .= $line;
                }
            }
            fclose($fp);
        }

        $lines = explode("\n", $res);
        $keyarray = array();

        if (strcmp($lines[0], "SUCCESS") == 0) {

            for ($i = 1; $i < count($lines); $i++) {
                if ($lines[$i] != "") {
                    list($key, $val) = explode("=", $lines[$i]);
                    $keyarray[urldecode($key)] = urldecode($val);
                }
            }

            if ($keyarray['mc_gross'] != (doubleval($this->amount) + doubleval($this->tax)) || $keyarray['mc_currency'] != strtoupper($this->curreny)) {

                return false;

            } else {

                return array(
                    'tx' => $tx_token,
                    'msg' => '',
                    'is_test' => $this->test ? 1 : 0,
                    'data' => $keyarray
                );
            }
        }

        return false;
    }

    /**
     *
     * @param string $return_url
     * @param array $options
     * @return mixed boolean false on error, else an array with information about the payment 
     */
    function verifyIpn($return_url, $options)
    {

        $return = false;

        $auth_token = $this->token;

        $paypal = 'https://www.paypal.com';
        if ($this->test) {
            $paypal = 'https://www.sandbox.paypal.com';
        }

        $req = 'cmd=_notify-validate';

        $tx_token = CBRequest::getVar('txn_id', 0);

        $keyarray = array();
        $postback = $_REQUEST;

        foreach ($postback as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        $pointer = null;
        $res = '';

        if (function_exists('curl_init')) {
            $ch = curl_init();
            $pointer = $ch;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

            ob_start();
            curl_exec($ch);
            $res = ob_get_contents();

        } else {
            // try fsockopen
            $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
            $pointer = $fp;
            fputs($fp, $header . $req);
            $headerdone = false;
            while (!feof($fp)) {
                $line = fgets($fp, 1024);
                if (strcmp($line, "\r\n") == 0) {
                    $headerdone = true;
                } else if ($headerdone) {
                    $res .= $line;
                }
            }
        }

        $lines = explode("\n", $res);

        if (strcmp($lines[0], "VERIFIED") == 0) {

            foreach ($postback as $key => $value) {
                $keyarray[$key] = $value;
            }

            if ($keyarray['mc_gross'] != (doubleval($this->amount) + doubleval($this->tax)) || $keyarray['mc_currency'] != strtoupper($this->curreny)) {

                // not good

            } else {

                $return = array(
                    'tx' => $tx_token,
                    'exit' => true,
                    'header' => 'Status: 200 OK',
                    'msg' => '',
                    'is_test' => $this->test ? 1 : 0,
                    'data' => $keyarray
                );
            }
        }

        // should be kept open until sending the status headers
        if (function_exists('curl_init')) {
            curl_close($pointer);
            ob_end_clean();
        } else {
            fclose($pointer);
        }

        return $return;
    }
}
