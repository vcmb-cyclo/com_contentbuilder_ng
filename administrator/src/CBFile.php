<?php
/**
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CB\Component\Contentbuilder\Administrator;

// no direct access
\defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\Filesystem\File ;

if (!class_exists('CBFile'))
{

	class CBFile extends File {

		public static function read($file){
			return file_get_contents($file);
		}
	}

}