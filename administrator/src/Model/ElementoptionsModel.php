<?php
/**
 * @package     ContentBuilder NG
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Administrator\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

/**
 * Joomla resolves the model name from the view name.
 * The view is "elementoptions" (plural), while the original model class is singular.
 * This alias keeps both entry points working.
 */
class ElementoptionsModel extends ElementoptionModel
{
}

