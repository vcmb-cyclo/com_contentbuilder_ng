<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

use Joomla\CMS\Uri\Uri;

global $ff_version, $ff_resnames, $ff_request, $ff_target;

$ff_version = '5.0.0 Beta (build 1)';
$ff_target  = 0;

$ff_resnames = array(
	'ff_name', 'ff_form', 'ff_border', 'ff_align', 'ff_runmode',
	'ff_page', 'ff_task', 'ff_target', 'ff_frame', 'ff_suffix',
	'ff_top'
);

DEFINE('_FF_RUNMODE_FRONTEND', 0);
DEFINE('_FF_RUNMODE_BACKEND',  1);
DEFINE('_FF_RUNMODE_PREVIEW',  2);

function nl()
{
	return "\r\n";
} // nl

function nlc()
{
	global $ff_config;
	if (!$ff_config->compress) return "\r\n";
} // nlc

function adjustNewlines($text)
{
	$text = str_replace("\r\n", "\n", $text); // unix mode
	return str_replace("\n", nl(), $text); // ff mode
} // adjustNewlines

function indent($level)
{
	$ind = '';
	for ($i = 0; $i < $level; $i++) $ind .= "\t";
	return $ind;
} // indent

function indentc($level)
{
	global $ff_config;
	$ind = '';
	if (!$ff_config->compress)
		for ($i = 0; $i < $level; $i++) $ind .= "\t";
	return $ind;
} // indentc

function expstring($text)
{
	$o = '';
	$text = trim($text);
	$l = strlen($text);
	for($i = 0; $i < $l; $i++) {
		$c = $text[$i];
		switch($c) {
			case ';' : $o .= '\\x3B'; break;
			case ',' : $o .= '\\x2C'; break;
			case '&' : $o .= '\\x26'; break;
			case '<' : $o .= '\\x3C'; break;
			case '>' : $o .= '\\x3E'; break;
			case '\'': $o .= '\\x27'; break;
			case '\\': $o .= '\\x5C'; break;
			case '"' : $o .= '\\x22'; break;
			case "\n": $o .= '\\n'; break;
			case "\r": $o .= '\\r'; break;
			default: $o.=$c;
		} // switch
	} // for
	return $o;
} // expstring

function impstring($text)
{
	return stripcslashes($text);
} // impstring

function addRequestParams($params)
{
	global $ff_request;

        $is_quoted = false;

	$px = explode('&amp;',$params);
        if( count( $px ) == 0 ){
            $px = explode('&',$params);
        }
	if (count($px)) foreach ($px as $p) {
		$x = explode('=',$p);
		$c = count($x);
		$n = ''; if ($c > 0) $n = trim($x[0]);
		$v = ''; if ($c > 1) $v = trim($x[1]);
		if ($n != '') $ff_request[$n] = $v;
	} // foreach
} // addRequestParams

function ff_reserved($p, $ff_param = true)
{
	global $ff_resnames;

	$p = strtolower($p);
	if (substr($p,0,3)!='ff_') return false;
	if ($ff_param && substr($p,0,9)=='ff_param_') return true;
	if (count($ff_resnames)) foreach ($ff_resnames as $n) if ($p == $n) return true;
	return false;
} // ff_reserved

function saveOtherParam($name)
{
	global $ff_otherparams;
	if ( BFRequest::getVar($name, null) != null && !is_array(BFRequest::getVar($name, null)) ) {
		$value = BFRequest::getVar($name);
		$ff_otherparams[$name] = $value;
		return $value;
	} // if
	return NULL;
} // saveOtherParam

function initFacileForms()
{
	global $ff_mossite, $ff_comsite, $ff_config, $ff_otherparams, $mosConfig_live_site;
	$mainframe = JFactory::getApplication();



	if (!isset($ff_mossite)) {
		if ($ff_config->livesite == 0) {
			//$ff_mossite = str_replace('\\','/', Uri::root());
			$ff_mossite = JUri::root();
		} else {
			$s = empty($_SERVER["HTTPS"]) ? '' : ( ($_SERVER["HTTPS"] == "on") ? "s" : "" );
			$s = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? 's' : $s;

			$protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
			$protocol = substr($protocol, 0, strpos($protocol, '/')).$s;
			$port = ":".$_SERVER["SERVER_PORT"];
			if (($protocol=='http' && $port==':80') || ($protocol=='https' && $port==':443')) $port = '';
			$path = dirname($_SERVER['PHP_SELF']);
			if (basename($path)=='administrator') $path = dirname($path);
			$domain = $_SERVER['HTTP_HOST'];
			$p = strrpos($domain, ':');
			if ($p) $domain = substr($domain,0,$p);
			$ff_mossite = str_replace('\\','/',$protocol."://".$domain.$port.$path);
		} // if
		$len = strlen($ff_mossite);
		if ($len>0 && $ff_mossite[$len-1]=='/') $ff_mossite = substr($ff_mossite,0,$len-1);
	} // if

	if (!isset($ff_comsite))
		$ff_comsite = $ff_mossite.'/components/com_breezingforms';

	if (!isset($ff_otherparams)) {
		$ff_otherparams = array();

		switch (saveOtherParam('option')) {
            case 'com_content':
                saveOtherParam('Itemid');
                saveOtherParam('task');
                saveOtherParam('sectionid');
                saveOtherParam('id');
                break;
            case 'com_contact':
            case 'com_contacts':
                saveOtherParam('id');
                saveOtherParam('Itemid');
                saveOtherParam('task');
                saveOtherParam('catid');
                saveOtherParam('view');
                saveOtherParam('contact_id');
                break;
            case 'com_weblinks':
                saveOtherParam('Itemid');
                saveOtherParam('catid');
                break;
            default:
                saveOtherParam('Itemid');
        } // switch
	} // if
} // initFacileForms

class facileFormsConf {
	var $stylesheet     = 1;        // backend frame preview no/yes
	var $wysiwyg        = 0;        // use wysiwyg editor for static text
	var $areasmall      = 4;        // small textarea lines
	var $areamedium     = 12;       // medium textarea lines
	var $arealarge      = 20;       // large textarea lines
	var $limitdesc      = 100;      // listview description limit
	var $emailadr       = 'Enter your email address here';                  // default email notify address
	var $images         = '{mossite}/components/com_breezingforms/images';    // {ff_images} path
	var $uploads        = '{mospath}/media/breezingforms/uploads';   // {ff_uploads} path
	var $movepixels     = 10;       // pixelmover stepping
	var $compress       = 1;        // compress output
	var $livesite       = 0;        // use $mosConfig_live_site as site url
	var $getprovider    = 0;        // get provider with gethostbyaddr
	var $gridshow       = 1;        // show grid in preview
	var $gridsize       = 10;       // grid size
	var $gridcolor1     = '#e0e0ff';// grid color even lines
	var $gridcolor2     = '#ffe0e0';// grid color odd lines

	// record manager settings
	var $viewed         = 0;        // default viewed filter setting
	var $exported       = 0;        // default exported filter setting
	var $archived       = 0;        // default archived filter setting
	var $formname       = '';       // default formname filter setting

	var $menupkg        = '';       // last selected menu package
	var $formpkg        = '';       // last selected form package
	var $scriptpkg      = '';       // last selected script package
	var $piecepkg       = '';       // last selected piece package

        var $csvdelimiter = ";";
        var $csvquote = '"';
        var $cellnewline = 1;

        var $enable_classic = 0;
        
        var $disable_ip = 0;

	function __construct()
	{
		$this->load();
	} // constructor

	function load()
	{
		global $ff_compath, $database;

		$database = BFFactory::getDbo();

		$configfile = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php';
		if (file_exists($configfile)) {
			include($configfile);
		} else {
                        $arr = get_object_vars($this);
			$ids = array();

			foreach ( $arr as $prop => $val ) $ids[] = "'".$prop."'";
            \Joomla\Utilities\ArrayHelper::toInteger($ids);

			$database->setQuery(
				"select id, value from #__facileforms_config ".
				 "where id in (".implode(',', $ids).")"
			);


                        $rows = array();
                        try{
                            $rows = $database->loadObjectList();
                        }catch(Exception $e){
                        }

			if (count($rows))
				foreach ($rows as $row) {
					$prop = $row->id;
					$this->$prop = stripcslashes($row->value);
				} // foreach
		} // if
	} // load

	function store()
	{
		global $ff_compath, $database, $mosConfig_fileperms;

		$database = BFFactory::getDbo();
		$configfile = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'facileforms.config.php';

		// prepare output
		$config = "<?php\n";
		$arr = get_object_vars($this);

        foreach ( $arr as $prop => $val ) {
			$config .= "\$this->".$prop." = \"".addslashes($val)."\";\n";

			$database->setQuery(
				"update #__facileforms_config ".
				   "set value=".$database->Quote($val)." ".
				 "where id = ".$database->Quote($prop).""
			);

			if (!$database->query()) {
				echo "<br/>".$database->getErrorMsg();
				exit;
			} // if

            $database->setQuery(
				"select count(*) from #__facileforms_config ".
				 "where id = ".$database->Quote($prop)
			);
			$saved = $database->loadResult();
			if (!$saved) {
				$database->setQuery(
					"insert into #__facileforms_config (id, value) ".
					"values (".$database->Quote($prop).", ".$database->Quote($val).")"
				);
				if (!$database->query()) {
					echo "<br/>".$database->getErrorMsg();
					exit;
				} // if
			} // if
		} // while
		$config .= "?>\n";

		// save to file

                if(!JFile::write($configfile,$config)){
                    die('Could not write config file, please check the permissions! <a href="javascript:history.go(-1)">back</a>');
                }

                /**
		$existed = file_exists($configfile);
		if ($fp = fopen($configfile, "w")) {
			fputs($fp, $config, strlen($config));
			fclose($fp);
			if (!$existed) {
				$filemode = NULL;
				if (isset($mosConfig_fileperms)) {
					if ($mosConfig_fileperms!='')
						$filemode = octdec($mosConfig_fileperms);
				} else
					$filemode = 0644;
				if (isset($filemode)) @chmod($configfile, $filemode);
			} // if
		} // if
                */
	} // store

	function bindRequest($request)
	{
		$arr = get_object_vars($this);
        foreach($arr as $prop => $val)
			$this->$prop = @BFRequest::getVar($prop, $val);
	} // bindRequest
} // class facileFormsConf

class facileFormsMenus extends JTable {
	var $id             = null;     // identifier
	var $package        = null;     // package name
	var $parent         = 0;        // parent id
	var $ordering       = 0;        // ordering
	var $published      = 1;        // is published
	var $img            = '';       // menu icon image
	var $title          = '';       // displayed menu name
	var $name           = '';       // form name (identifier)
	var $page           = 1;        // starting page
	var $frame          = 0;        // run in iframe
	var $border         = 0;        // show a border
	var $params         = null;     // additional parameters

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_compmenus', 'id', JFactory::getDbo());
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;
		$database = BFFactory::getDbo();
		$database->setQuery("select * from #__facileforms_compmenus where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
            foreach($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsMenus

class facileFormsForms extends JTable {
	var $id             = null;     // identifier
	var $package        = null;     // package name
	var $ordering       = null;     // ordering
	var $published      = null;     // no/yes
	var $runmode        = null;     // 0-any/1-foreground/2-background
	var $name           = null;     // form name (identifier)
	var $title          = null;     // fancy name
	var $description    = null;     // form description
	var $class1         = null;     // css class for <div>
	var $class2         = null;     // css class for <form>
	var $width          = null;     // form width in px
	var $widthmode      = null;     // 0=px 1=%
	var $height         = null;     // form height in px
	var $heightmode     = null;     // 0=px 1=auto
	var $pages          = null;     // # of pages
	var $emailntf       = null;     // none/default/custom
        var $mb_emailntf       = null;     // none/default/custom
	var $emaillog       = null;     // header only/nonempty values/all
        var $mb_emaillog       = null;     // header only/nonempty values/all
	var $emailxml       = null;     // xml attachment no/nonempty values/all
        var $mb_emailxml       = null;     // xml attachment no/nonempty values/all
	var $emailadr       = null;     // custom email address
	var $dblog          = null;     // no/nonempty values/all
	var $script1cond    = null;     // init: none/library/custom
	var $script1id      = null;     // library function id
	var $script1code    = null;     // custom code ff_{form}_init()
	var $script2cond    = null;     // submitted: none/library/custom
	var $script2id      = null;     // library function id
	var $script2code    = null;     // custom code ff_{form}_submitted(status='success','failed')
	var $piece1cond     = null;     // Before form: none/library/custom
	var $piece1id       = null;     // library function id
	var $piece1code     = null;     // custom code
	var $piece2cond     = null;     // After form: none/library/custom
	var $piece2id       = null;     // library function id
	var $piece2code     = null;     // custom code
	var $piece3cond     = null;     // Begin submit: none/library/custom
	var $piece3id       = null;     // library function id
	var $piece3code     = null;     // custom code
	var $piece4cond     = null;     // End submit: none/library/custom
	var $piece4id       = null;     // library function id
	var $piece4code     = null;     // custom code
	var $prevmode       = null;     // preview mode 0-none 1-below 2-overlay
	var $prevwidth      = null;     // preview width px in case of widthmode=1
	var $template_code_processed = null; // the processed templated easymode form html code
	var $template_code = null;
	var $template_areas = null;
	var $custom_mail_subject = null;
        var $mb_custom_mail_subject = null;
        var $alt_mailfrom = null;
        var $mb_alt_mailfrom = null;
        var $alt_fromname = null;
        var $mb_alt_fromname = null;
        var $mailchimp_email_field = null;
        var $mailchimp_api_key = null;
        var $mailchimp_list_id = null;
        var $mailchimp_double_optin = null;
        var $mailchimp_mergevars = null;
        var $mailchimp_checkbox_field = null;
        var $mailchimp_text_html_mobile_field = null;
        var $mailchimp_send_errors = null;
        var $mailchimp_update_existing = null;
        var $mailchimp_replace_interests = null;
        var $mailchimp_send_welcome = null;
        var $mailchimp_default_type = null;
        var $mailchimp_unsubscribe_field = null;
        var $mailchimp_send_notify = null;
        var $mailchimp_send_goodbye = null;
        var $mailchimp_delete_member = null;
        var $salesforce_token = null;
        var $salesforce_username = null;
        var $salesforce_password = null;
        var $salesforce_type = null;
        var $salesforce_fields = null;
        var $salesforce_enabled = null;
        var $email_type = null;
        var $mb_email_type = null;
        var $email_custom_template = null;
        var $mb_email_custom_template = null;
        var $email_custom_html = null;
        var $mb_email_custom_html = null;
	var $dropbox_email = '';
        var $dropbox_password = '';
        var $dropbox_folder = '';
        var $dropbox_submission_enabled = 0;
		var $dropbox_submission_types = 'pdf';
	var $double_opt = '';
	var $opt_mail = '';

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_forms', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_forms where id = $id");
		$rows = $database->loadObjectList();

		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach($arr as $prop => $val) {
				if ($prop[0] != '_'){
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used
			
			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_'){
			// 		@$this->$prop = $row->$prop;
			// 	}
			return true;
		} // if
		return false;
	} // load

} // class facileFormsForms

class facileFormsElements extends JTable {
	var $id             = null;     // general parameters
	var $form           = null;     // form id
	var $page           = null;     // page number
	var $ordering       = null;     // ordering index
	var $published      = null;     // publish status
	var $name           = null;     // identifier
	var $title          = null;     // fancy name
	var $type           = null;     // element type
/*
-----------------------------------------Element Parameter Cross Reference-------------------------------------------
Element             logging posx posy width height flag1    flag2    data1   data2     data3  script1 script2 script3
---------------------------------------------------------------------------------------------------------------------
Static Text/HTML    -       px%  px%  px%   px%    -        -        value   -         -      -       -       -
Rectangle           -       px%  px%  px%   px%    -        -        border  bckg.col. -      -       -       -
Image               -       px%  px%  px%   px%    -        -        img.url alt.text  -      -       -       -
Tooltip             -       px%  px%  -     -      type     -        img.url text      -      -       -       -
Regular Button      -       px%  px%  -     -      -        disabled -       caption   -      -       action  -
Graphic Button      -       px%  px%  -     -      capt.pos disabled img.url caption   -      -       action  -
Icon                -       px%  px%  -     -      capt.pos border   img.url caption   img.f2 -       action  -
Checkbox            yes     px%  px%  -     -      checked  disabled value   label     -      init    action  valid.
Radio Button        yes     px%  px%  -     -      checked  disabled value   label     -      init    action  valid.
Select List         yes     px%  px%  px    px     multiple disabled size    options   -      init    action  valid.
Query List          yes     px%  px%  px%   m.rows dsp.hdr  dsp.ckbx setting query     cols   -       -       -
Text                yes     px%  px%  szpx  maxlen password dis/rdo  value   -         -      init    action  valid.
Textarea            yes     px%  px%  szpx  colpx  -        dis/rdo  value   -         -      init    action  valid.
File Upload         yes     px%  px%  size  limit  -        disabled dir     types     -      init    action  valid.
Hidden Input        yes     -    -     -     -     -        -        value   -         -      init    -       valid.
---------------------------------------------------------------------------------------------------------------------

Query List Settings: border / cellspacing / cellpadding / <tr(h)>class / <tr(1)>class / <tr(2)>class
*/
	var $class1         = null;     // css class for <div>
	var $class2         = null;     // css class for <element>

	var $logging        = null;     // element is logged in email/database no/yes

	var $posx           = null;     // horizontal position in px or %
	var $posxmode       = null;     // 0-px 1-%
	var $posy           = null;     // vertical position in px or %
	var $posymode       = null;     // 0-px 1-%
	var $width          = null;     // width in % or px
	var $widthmode      = null;     // 0-px 1-%
	var $height         = null;     // height in px
	var $heightmode     = null;     // 0-fixed px 1-auto 2-automax

	var $flag1          = null;     // element specific data, see xref
	var $flag2          = null;
	var $data1          = null;
	var $data2          = null;
	var $data3          = null;

	var $script1cond    = null;     // init script
	var $script1flag1   = null;     // condition 1 = on form entry no/yes
	var $script1flag2   = null;     // condition 2 = on page entry
	var $script1id      = null;     // script id
	var $script1code    = null;     // custom code

	var $script2cond    = null;     // action script
	var $script2flag1   = null;     // action 1 = Click
	var $script2flag2   = null;     // action 2 = Blur
	var $script2flag3   = null;     // action 3 = Change
	var $script2flag4   = null;     // action 4 = Focus
	var $script2flag5   = null;     // action 5 = Select
	var $script2id      = null;     // script id
	var $script2code    = null;     // custom code

	var $script3cond    = null;     // validation script
	var $script3id      = null;     // script id
	var $script3msg     = null;     // message
	var $script3code    = null;     // custom code

	var $mailback       = null;
	var $mailbackfile       = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_elements', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=null)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_elements where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
            foreach($arr as $prop => $val)
				if ($prop[0] != '_')
					@$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsElements

class facileFormsScripts extends JTable {
	var $id             = null;     // identifier
	var $published      = null;     // is published
	var $package        = null;     // package name
	var $name           = null;     // function name
	var $title          = null;     // fancy name
	var $description    = null;     // description
	var $type           = null;     // type name
	var $code           = null;     // the code

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_scripts', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_scripts where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach($arr as $prop => $val) {
				if ($prop[0] != '_'){
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used

			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_')
			// 		$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsScripts

class facileFormsPieces extends JTable {
	var $id             = null;     // identifier
	var $published      = null;     // is published
	var $package        = null;     // package name
	var $name           = null;     // function name
	var $title          = null;     // fancy name
	var $description    = null;     // description
	var $type           = null;     // type name
	var $code           = null;     // the code

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_pieces', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_pieces where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach($arr as $prop => $val) {
				if ($prop[0] != '_'){
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used
			
			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_')
			// 		$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsPieces

class facileFormsRecords extends JTable {
	var $id             = null;     // identifier
	var $submitted      = null;     // date and time
	var $form           = null;     // form id
	var $title          = null;     // form title
	var $name           = null;     // form name
	var $ip             = null;     // submitters ip
	var $browser        = null;     // browser
	var $opsys          = null;     // operating system
	var $provider       = null;     // provider
	var $viewed         = null;     // view status
	var $exported       = null;     // export status
	var $archived       = null;     // archive status
	var $paypal_tx_id   = null;
	var $paypal_payment_date = null;
	var $paypal_testaccount = null;
	var $paypal_download_tries = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_records', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_records where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
            foreach($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsRecords

class facileFormsSubrecords extends JTable {
	var $id             = null;     // identifier
	var $record         = null;     // record id
	var $element        = null;     // element id
	var $name           = null;     // element name
	var $type           = null;     // data type
	var $value          = null;     // data value

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_subrecords', 'id', JFactory::getDbo());
	} // constructor

	public function load($id=null,$reset=true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_subrecords where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
            foreach($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsSubrecords

class facileFormsQuerycols {
	var $title          = null;     // column title
	var $name           = null;     // column name
	var $class1         = null;     // class for th
	var $class2         = null;     // class for td(1)
	var $class3         = null;     // class for td(2)
	var $thspan         = null;     // th span
	var $align          = null;     // 0-left 1-center 2-right
	var $valign         = null;     // 0-top 1-middle 2-bottom 3-baseline
	var $wrap           = null;     // 0-nowrap 1-wrap
	var $value          = null;     // value field (php allowed)
	var $comp           = null;     // complied value: array of array(type, value/code)

	function __construct()
	{
		$this->title    = '';
		$this->name     = '';
		$this->class1   = '';
		$this->class2   = '';
		$this->class3   = '';
		$this->width    = '';
		$this->widthmd  = 0;
		$this->thspan   = 1;
		$this->thalign  = 0;
		$this->thvalign = 0;
		$this->thwrap   = 0;
		$this->align    = 0;
		$this->valign   = 0;
		$this->wrap     = 0;
		$this->value    = '';
	} // constructor

	function unpack($line)
	{
		$vals = explode('&',$line);
		$cnt = count($vals);
		if ($cnt > 0) $this->title    = impstring($vals[0]);
		if ($cnt > 1) $this->name     = impstring($vals[1]);
		if ($cnt > 2) $this->class1   = impstring($vals[2]);
		if ($cnt > 3) $this->class2   = impstring($vals[3]);
		if ($cnt > 4) $this->class3   = impstring($vals[4]);
		if ($cnt > 5) $this->width    = impstring($vals[5]);
		if ($cnt > 6) $this->widthmd  = impstring($vals[6]);
		if ($cnt > 7) $this->thspan   = impstring($vals[7]);
		if ($cnt > 8) $this->thalign  = impstring($vals[8]);
		if ($cnt > 9) $this->thvalign = impstring($vals[9]);
		if ($cnt >10) $this->thwrap   = impstring($vals[10]);
		if ($cnt >11) $this->align    = impstring($vals[11]);
		if ($cnt >12) $this->valign   = impstring($vals[12]);
		if ($cnt >13) $this->wrap     = impstring($vals[13]);
		if ($cnt >14) $this->value    = impstring($vals[14]);
	} // unpack

	function pack()
	{
		return
			expstring($this->title   ).'&'.
			expstring($this->name    ).'&'.
			expstring($this->class1  ).'&'.
			expstring($this->class2  ).'&'.
			expstring($this->class3  ).'&'.
			expstring($this->width   ).'&'.
			expstring($this->widthmd ).'&'.
			expstring($this->thspan  ).'&'.
			expstring($this->thalign ).'&'.
			expstring($this->thvalign).'&'.
			expstring($this->thwrap  ).'&'.
			expstring($this->align   ).'&'.
			expstring($this->valign  ).'&'.
			expstring($this->wrap    ).'&'.
			expstring($this->value   );
	} // pack

} // class facileFormsQuerycols

?>