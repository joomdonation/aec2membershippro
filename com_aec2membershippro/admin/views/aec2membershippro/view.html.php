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

class AEC2MembershipProViewAEC2MembershipPro extends JViewLegacy
{
	public function display($tpl = null)
	{
		JToolbarHelper::title(JText::_('AEC To Membership Pro migrator'), 'dashboard.png');

		parent::display($tpl);
	}
}
