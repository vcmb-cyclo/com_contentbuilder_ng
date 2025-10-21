<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;

class TableForm extends Table
{
    public $id = 0;
    public $type = '';
    public $reference_id = 0;
    public $name = '';
    public $title = '';
    public $tag = '';
    public $created = '0000-00-00 00:00:00';
    public $modified = '0000-00-00 00:00:00';
    public $created_by = '';
    public $details_template = '';
    public $details_prepare = '';
    public $editable_template = '';
    public $editable_prepare = '';

    public $email_template = '';
    public $email_subject = '';
    public $email_alternative_from = '';
    public $email_alternative_fromname = '';
    public $email_recipients = '';
    public $email_recipients_attach_uploads = '';
    public $email_html = '';

    public $email_admin_template = '';
    public $email_admin_subject = '';
    public $email_admin_alternative_from = '';
    public $email_admin_alternative_fromname = '';
    public $email_admin_recipients = '';
    public $email_admin_recipients_attach_uploads = '';
    public $email_admin_html = '';

    public $modified_by = '';
    public $print_button = 1;
    public $metadata = 1;
    public $export_xls = 0;
    public $edit_button = 0;
    public $list_state = 0;
    public $list_publish = 0;
    public $list_rating = 0;
    public $select_column = 0;
    public $show_id_column = 0;
    public $use_view_name_as_title = 0;
    public $intro_text = '';
    public $published_only = 0;
    public $display_in = 0;
    public $ordering = 0;
    public $own_only = 0;
    public $own_only_fe = 0;
    public $published = 0;
    public $config = '';
    public $initial_sort_order = -1;
    public $initial_sort_order2 = -1;
    public $initial_sort_order3 = -1;
    public $initial_order_dir = 'desc';
    public $create_articles = 1;
    public $default_section = 0;
    public $default_category = 0;
    public $title_field = 0;
    public $delete_articles = 1;
    public $edit_by_type = 0;
    public $email_notifications = 1;
    public $email_update_notifications = 0;
    public $limited_article_options = 1;
    public $limited_article_options_fe = 1;
    public $upload_directory = 'media/contentbuilder/upload';
    public $protect_upload_directory = 1;
    public $last_update = '0000-00-00 00:00:00';
    public $limit_add = 0;
    public $limit_edit = 0;
    public $verification_required_view = 0;
    public $verification_days_view = 0;
    public $verification_required_new = 0;
    public $verification_days_new = 0;
    public $verification_required_edit = 0;
    public $verification_days_edit = 0;
    public $verification_url_view = '';
    public $verification_url_new = '';
    public $verification_url_edit = '';
    public $default_lang_code = '*';
    public $default_lang_code_ignore = 0;
    public $show_all_languages_fe = 1;
    public $list_language = 0;
    public $default_publish_up_days = 0;
    public $default_publish_down_days = 0;
    public $default_access = 0;
    public $default_featured = 0;
    public $list_article = 0;
    public $list_author = 0;

    public $act_as_registration = 0;
    public $registration_username_field = '';
    public $registration_password_field = '';
    public $registration_password_repeat_field = '';
    public $registration_email_field = '';
    public $registration_email_repeat_field = '';
    public $registration_name_field = '';

    public $auto_publish = 0;

    public $force_login = 0;
    public $force_url = '';

    public $registration_bypass_plugin = '';
    public $registration_bypass_plugin_params = '';
    public $registration_bypass_verification_name = '';
    public $registration_bypass_verify_view = '';

    public $theme_plugin = '';

    public $rating_slots = 5;

    public $rand_date_update = '0000-00-00 00:00:00';
    public $rand_update = '86400';

    public $article_record_impact_publish = 0;
    public $article_record_impact_language = 0;

    public $allow_external_filter = 0;

    public $show_filter = 1;

    public $show_records_per_page = 1;

    public $initial_list_limit = 20;

    public $save_button_title = '';

    public $apply_button_title = '';

    public $filter_exact_match = 0;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct($db)
    {
        parent::__construct('#__contentbuilder_forms', 'id', $db);
    }
}


// as of J! 2.5
class formTableForm extends TableForm
{
}
