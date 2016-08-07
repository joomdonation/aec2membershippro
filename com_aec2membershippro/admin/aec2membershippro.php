<?php
/**
 * @version        1.0.0
 * @package        Joomla
 * @subpackage     AEC2MembershipPro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('AEC2MembershipPro');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
