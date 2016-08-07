<?php
/**
 * @version        1.0.0
 * @package        Joomla
 * @subpackage     AEC2MembershipPro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

class AEC2MembershipProController extends JControllerLegacy
{

	public function reset_data()
	{
		$db = JFactory::getDbo();
		$db->truncateTable('#__osmembership_categories');
		$db->truncateTable('#__osmembership_plans');
		$db->truncateTable('#__osmembership_subscribers');

		$this->setRedirect('index.php?option=com_aec2membershippro', JText::_('Data is clean up. Now, you can start the migration'));
	}

	/**
	 * Migrate categories
	 */
	public function migrate_categories()
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmembership/table');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$fields = array_keys($db->getTableColumns('#__osmembership_categories'));

		if (!in_array('category_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_categories` ADD  `category_id` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		$query->select('id, name, `desc`, `active`, `ordering`')
			->from('#__acctexp_itemgroups')
			->where('id != 1')
			->order('id ASC');
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$row = JTable::getInstance('Category', 'OSMembershipTable');

		foreach ($categories as $category)
		{
			$row->id          = 0;
			$row->title       = $category->name;
			$row->description = $category->desc;
			$row->published   = $category->active;
			$row->ordering    = $category->ordering;
			$row->category_id = $category->id;

			$row->store();
		}

		$numberCategories = count($categories);

		$this->setRedirect('index.php?option=com_aec2membershippro&task=migrate_plans', JText::sprintf('%s categories migrated. Now, the system will migrating plans', $numberCategories));
	}

	/**
	 * Migrate subscription plans
	 */
	public function migrate_plans()
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmembership/table');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$fields = array_keys($db->getTableColumns('#__osmembership_plans'));

		if (!in_array('plan_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `plan_id` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		// First store the categories relationship
		$query->select('id, category_id')
			->from('#__osmembership_categories')
			->order('id');
		$db->setQuery($query);
		$categories = $db->loadObjectList('category_id');


		$query->clear();
		$query->select('id, name, `desc`, `active`, `ordering`, `params`')
			->from('#__acctexp_plans')
			->order('id');
		$db->setQuery($query);
		$plans = $db->loadObjectList();

		$row = JTable::getInstance('Plan', 'OSMembershipTable');

		foreach ($plans as $plan)
		{
			$row->id          = 0;
			$row->title       = $plan->name;
			$row->description = $row->short_description = $plan->desc;
			$row->published   = $plan->active;
			$row->ordering    = $plan->ordering;
			$row->plan_id     = $plan->id;

			// Find category
			$query->clear();
			$query->select('group_id')
				->from('#__acctexp_itemxgroup')
				->where('group_id != 1')
				->where('item_id = ' . $plan->id);

			$categoryId = (int) $db->loadResult();
			if ($categoryId && isset($categories[$categoryId]))
			{
				$row->category_id = $categories[$categoryId]->id;
			}

			// Params
			$params = unserialize(base64_decode($plan->params));

			if (is_array($params))
			{
				$row->price                    = $params['full_amount'];
				$row->subscription_length      = $params['full_period'];
				$row->subscription_length_unit = $params['full_periodunit'];

				$row->trial_amount     = $params['trial_amount'];
				$row->trial_duration   = $params['trial_period'];
				$row->trial_periodunit = $params['trial_duration_unit'];
			}
			$row->store();
		}

		$numberPlans = count($plans);

		$this->setRedirect('index.php?option=com_aec2membershippro&task=migrate_subscriptions', JText::sprintf('%s plans migrated. Now, the system will migrating coupons', $numberPlans));
	}

	/**
	 * Migrate subscriptions
	 */
	public function migrate_subscriptions()
	{
		require_once JPATH_ROOT . '/components/com_osmembership/helper/helper.php';

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmembership/table');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$fields = array_keys($db->getTableColumns('#__osmembership_subscribers'));

		if (!in_array('subscriber_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `subscriber_id` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		// First store the plans relationship
		$query->select('id, plan_id')
			->from('#__osmembership_plans')
			->order('id');
		$db->setQuery($query);
		$plans = $db->loadObjectList('plan_id');

		$start = $this->input->getInt('start', 0);
		$query->clear();
		$query->select('a.id, a.userid, a.`type`, a.`primary`, a.`status`, a.`signup_date`, a.lastpay_date, a.plan, a.recurring, a.expiration')
			->select('b.name, b.email')
			->from('#__acctexp_subscr AS a')
			->leftJoin('#__users AS b ON a.userid = b.id')
			->order('id');
		$db->setQuery($query, $start, 200);
		$subscriptions       = $db->loadObjectList();
		$numberSubscriptions = count($subscriptions);
		if (count($subscriptions) == 0)
		{
			// No records left, redirect to complete page
			$this->setRedirect('index.php?option=com_aec2membershippro&layout=complete');
		}
		else
		{
			$row = JTable::getInstance('Subscriber', 'OSMembershipTable');

			$membershipProVersion = OSMembershipHelper::getInstalledVersion();
			$calculateMainRecord  = version_compare($membershipProVersion, '2.6.0', 'ge');

			foreach ($subscriptions as $subscription)
			{
				$row->id = 0;
				$name    = $subscription->name;
				if ($name)
				{
					$pos = strpos($name, ' ');
					if ($pos !== false)
					{
						$row->first_name = substr($name, 0, $pos);
						$row->last_name  = substr($name, $pos + 1);
					}
					else
					{
						$row->first_name = $name;
					}
				}
				if (!isset($plans[$subscription->plan]))
				{
					continue;
				}

				$row->plan_id      = $plans[$subscription->plan]->id;
				$row->email        = $subscription->email;
				$row->user_id      = $subscription->userid;
				$row->created_date = $subscription->signup_date;
				$row->payment_date = $subscription->signup_date;
				$row->from_date    = $subscription->signup_date;
				$row->to_date      = $subscription->expiration;
				$status            = strtolower($subscription->status);
				switch ($status)
				{
					case 'pending':
						$row->published = 0;
						break;
					case 'active':
						$row->published = 1;
						break;
					case 'expired':
					case 'closed':
						$row->published = 2;
						break;
					default:
						$row->published = 3;
						break;
				}
				$row->to_date = $subscription->expiration;
				$row->act     = 'subscribe';

				// Query the invoice table, get payment amount data
				$query->clear();
				$query->select('a.invoice_number_format, a.method, a.amount, a.coupons, b.response')
					->from('#__acctexp_invoices AS a')
					->leftJoin('#__acctexp_log_history AS b ON a.invoice_number = b.invoice_number')
					->where('a.subscr_id = ' . $subscription->id)
					->order('a.id DESC, b.id DESC');
				$db->setQuery($query);
				$invoice = $db->loadObject();

				if ($invoice)
				{
					$row->payment_method = 'os_' . str_replace('_subscription', '', $invoice->method);
					$row->amount         = $row->gross_amount = $invoice->amount;

					if ($invoice->response)
					{
						$response = unserialize(base64_decode($invoice->response));

						if (is_array($response))
						{
							if ($response['txn_id'])
							{
								$row->transaction_id = $response['txn_id'];
							}
						}
					}

					if (empty($row->transaction_id))
					{
						$row->transaction_id = $invoice->invoice_number;
					}

				}

				// Find and set profile ID
				$row->is_profile = 1;
				if ($calculateMainRecord)
				{
					$row->plan_main_record = 1;
				}

				if ($row->user_id > 0)
				{
					$query->clear();
					$query->select('id')
						->from('#__osmembership_subscribers')
						->where('is_profile = 1')
						->where('user_id = ' . $row->user_id);
					$db->setQuery($query);
					$profileId = $db->loadResult();

					if ($profileId)
					{
						$row->is_profile = 0;
						$row->profile_id = $profileId;
					}

					if ($calculateMainRecord)
					{
						$query->clear()
							->select('plan_subscription_from_date')
							->from('#__osmembership_subscribers')
							->where('plan_main_record = 1')
							->where('user_id = ' . $row->user_id)
							->where('plan_id = ' . $row->plan_id);
						$db->setQuery($query);
						$db->setQuery($query);
						$planMainRecord = $db->loadObject();

						if ($planMainRecord)
						{
							$row->plan_main_record            = 0;
							$row->plan_subscription_from_date = $planMainRecord->plan_subscription_from_date;
						}
					}
				}

				if ($row->amount > 0)
				{
					$row->invoice_number = OSMembershipHelper::getInvoiceNumber($row);
				}

				if ($calculateMainRecord && $row->plan_main_record == 1)
				{
					$row->plan_subscription_status    = $row->published;
					$row->plan_subscription_from_date = $row->from_date;
					$row->plan_subscription_to_date   = $row->to_date;
				}

				$row->store();

				if (!$row->profile_id)
				{
					$row->profile_id = $row->id;
					$row->store();
				}
			}

			$start += $numberSubscriptions;
			$this->setRedirect('index.php?option=com_aec2membershippro&layout=form&start=' . $start);
		}
	}
}