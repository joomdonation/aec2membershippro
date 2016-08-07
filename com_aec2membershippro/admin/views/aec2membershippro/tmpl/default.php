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
defined( '_JEXEC' ) or die ;

JToolbarHelper::deleteList('Are you sure want to clear existing data?', 'reset_data', JText::_('CLEAR EXISTING DATA'));
JToolbarHelper::save('migrate_categories', JText::_('Start The Migration'));
?>
<form action="index.php?option=com_aec2membershippro" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12" style="text-align:center;">
			<h2>
				AEC To Membership Pro migrator
			</h2>
			<BR />
			<div class="img img-polaroid" style="padding:10px;background:#F3FBFB;text-align:left;">
				<p>
					This component is used to migrate data (categories/plans/subscriptions) from <strong>AEC</strong> into <strong>Membership Pro</strong>.
					Please click on <strong>Start The Migration</strong> button at toolbar to start the migration.
				</p>
				<p style="font-size: 16px; color: red">
					Please only press <strong>CLEAR EXISTING DATA</strong> button in the toolbar if you want to <strong>DELETE ALL EXISTING CATEGORIES, PLANS, SUBSCRIPTIONS RECORDS in Membership Pro</strong>.
					Only press that button if you want to reset the data and start the migration again
				</p>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>