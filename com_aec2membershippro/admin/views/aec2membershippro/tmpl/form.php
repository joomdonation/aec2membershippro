<?php
/**
* @version		1.0.0
 * @package		Joomla
 * @subpackage	Docman2EDocman
 * @author		Tuan Pham Ngoc
 * @copyright	Copyright (C) 2018-2011 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
$start = JFactory::getApplication()->input->getInt('start', 0);
?>
<form action="index.php?option=com_aec2membershippro&task=migrate_subscriptions&start=<?php echo $start; ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12" style="text-align:center;">
			<h2>
				AEC To Membership Pro migrator
			</h2>
			<BR />
			<div class="img img-polaroid" style="padding:10px;background:#F3FBFB;text-align:left;">
				The migrator is migrating subscriptions from AEC to Membership Pro. So far <?php echo $start ?> records migrated. Please don't close the browser until the process completed
			</div>
		</div>
	</div>
	<?php echo JHtml::_( 'form.token' ); ?>
	<script type="text/javascript">
		function redirect() {
			document.adminForm.submit();
		}
		setTimeout('redirect()', 5000);
	</script>
</form>