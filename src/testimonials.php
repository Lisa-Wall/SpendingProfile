<? include_once("inc_header.php"); ?>
<? include_once('inc_menu.php'); ?>

<? function debug(){} function error(){} ?>
<? include_once("server/core/database.php"); ?>

<div class="page_area">
	<div class="page_title">Testimonials</div>
	<br />

	<div>
	<?
		$oDatabase = new Database(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE, 'server/');
		$aTestimonials = $oDatabase->selectRows('server/sql/testimonials/get_all.sql');

		if ($aTestimonials !== false && $aTestimonials !== null)
		{
			foreach ($aTestimonials as $aTestimonial)
			{
				echo "<p>{$aTestimonial['Testimonial']}<br/>";
				echo "<b>- {$aTestimonial['FirstName']}";

				if ($aTestimonial['Location'] != "") echo " from {$aTestimonial['Location']}";

				echo "</b></p><hr/><br/>";
			}
		}
		else echo "<p style='color:red'>Error retrieving testimonials. Please try again later.</p>";
	?>
	</div>
</div>

<? include_once("inc_footer.php"); ?>