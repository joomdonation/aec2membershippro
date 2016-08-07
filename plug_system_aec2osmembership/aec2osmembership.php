<?php
/**
 * @version        2.1.0
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2015 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

error_reporting(0);
if (!file_exists(JPATH_ROOT . '/components/com_osmembership/osmembership.php'))
{
	return;
}

class plgSystemAec2OsMembership extends JPlugin
{
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return true;
		}

		$option        = JRequest::getCmd('option');
		$task          = JRequest::getCmd('task');		
		
		if ($option == 'com_acctexp' && $task == 'paypal_subscriptionnotification')
		{
			// Let Membership Pro handle it
			JRequest::setVar('option', 'com_osmembership');
			JRequest::setVar('task', 'recurring_payment_confirm');
			JRequest::setVar('payment_method', 'os_paypal');

			JRequest::setVar('aec', 1, 'get');
		}
	}
}

