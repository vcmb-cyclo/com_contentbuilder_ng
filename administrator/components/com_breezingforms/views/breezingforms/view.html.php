<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

defined('_JEXEC') or die;

class BreezingformsViewBreezingforms extends JViewLegacy
{
	protected $modules = null;

	public function display($tpl = null)
	{
                
            JToolbarHelper::title('BreezingForms');
            Factory::getDocument()->setTitle("BreezingForms");

            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFRequest.php');
            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFText.php');

            JHtmlSidebar::addEntry('<i class="fa fa-folder-open" aria-hidden="true"></i> ' . '<m>'.
                BFText::_('COM_BREEZINGFORMS_MANAGERECS') .'</m>',
                'index.php?option=com_breezingforms&act=managerecs', BFRequest::getVar('act','') == 'managerecs' || BFRequest::getVar('act','') == 'recordmanagement' || BFRequest::getVar('act','') == '');

            JHtmlSidebar::addEntry('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> ' .'<m>'.
                BFText::_('COM_BREEZINGFORMS_MANAGEFORMS') .'</m>',
                'index.php?option=com_breezingforms&act=manageforms', BFRequest::getVar('act','') == 'manageforms' || BFRequest::getVar('act','') == 'easymode' || BFRequest::getVar('act','') == 'quickmode');

            JHtmlSidebar::addEntry('<i class="fa fa-code" aria-hidden="true"></i> ' .'<m>'.
                BFText::_('COM_BREEZINGFORMS_MANAGESCRIPTS') .'</m>',
                'index.php?option=com_breezingforms&act=managescripts', BFRequest::getVar('act','') == 'managescripts');

            JHtmlSidebar::addEntry('<i class="fa fa-puzzle-piece" aria-hidden="true"></i> '  .'<m>'.
                BFText::_('COM_BREEZINGFORMS_MANAGEPIECES') .'</m>',
                'index.php?option=com_breezingforms&act=managepieces', BFRequest::getVar('act','') == 'managepieces');

            JHtmlSidebar::addEntry('<i class="fa fa-link" aria-hidden="true"></i> ' .'<m>'.
                BFText::_('COM_BREEZINGFORMS_INTEGRATOR') .'</m>',
                'index.php?option=com_breezingforms&act=integrate', BFRequest::getVar('act','') == 'integrate');

            /*
            JHtmlSidebar::addEntry('<i class="fa fa-bars" aria-hidden="true"></i> '  .'<m>'.
                BFText::_('COM_BREEZINGFORMS_MANAGEMENUS') .'</m>',
                'index.php?option=com_breezingforms&act=managemenus', BFRequest::getVar('act','') == 'managemenus');*/

            JHtmlSidebar::addEntry('<i class="fa fa-cog" aria-hidden="true"></i> '  .'<m>'.
                BFText::_('COM_BREEZINGFORMS_CONFIG') .'</m>',
                'index.php?option=com_breezingforms&act=configuration', BFRequest::getVar('act','') == 'configuration');

            JHtmlSidebar::addEntry('<i class="fa fa-file-text-o" aria-hidden="true"></i> '  .'<m>'.
                BFText::_('Docs & Support') .'</m>',
                'http://crosstec.org/en/support/breezingforms-documentation.html' );

            $this->sidebar = '<div id="bf-sidebar">' . JHtmlSidebar::render() . '</div>';


            Factory::getDocument()->addScriptDeclaration('
            
            jQuery(document).ready(function(){
            
                jQuery("#bf-sidebar").appendTo("#wrapper");
            });
            
            ');

            parent::display($tpl);
	}
}
