<?
	function getMenuSelected($sName)
	{
		return (getPageName() == $sName ? 'class="page_menu_selected"' : '');
	}
?>
<div class="page_menu">
	<a href="index.php" <?=getMenuSelected('index.php') ?> >Home</a><br/>
	<a href="whatisit.php" <?=getMenuSelected('whatisit.php') ?>>What is Spending Profile?</a><br/>
	<a href="whatlooklike.php" <?=getMenuSelected('whatlooklike.php') ?>>What Does it Look Like?</a><br/>
	<a href="howuse.php" <?=getMenuSelected('howuse.php') ?>>How Do You Use it?</a><br/>
	<a href="testimonials.php" <?=getMenuSelected('testimonials.php') ?>>Testimonials</a><br/>

	<div class="page_menu_separator">_______________________<br/><br/></div>

	<a href="signin.php" <?=getMenuSelected('signin.php') ?>>Sign In</a><br/>
	<a href="createaccount.php" <?=getMenuSelected('createaccount.php') ?>>Create Account</a><br/>

	<br />

	<a href="demo.php"><img src="<?=$sImage?>image=demo.png" alt="Try Spending Profile right now!" height="50" width="100"/></a>

	<br/><br/>

	<!-- if (file_exists('ads/adsense/'.getPageName().'.leftmenu')) include('ads/adsense/'.getPageName().'.leftmenu') -->
</div>