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
?>
<form action="index.php?option=com_aec2membershippro" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12" style="text-align:center;">
			<h2>
				AEC To Membership Pro migrator
			</h2>
			<BR />
			<div class="img img-polaroid" style="padding:10px;background:#F3FBFB;text-align:left;">
				Congratulations, your AEC data has been successfully migrated to Membership Pro. Please <a href="index.php?option=com_osmembership"><strong>CLICK HERE</strong></strong></a> to continue setup and use Membership Pro
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>