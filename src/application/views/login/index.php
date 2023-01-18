<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */
?>


<div class="aboutText">


	<?if($this->session->userdata('logged_in')) {

	} else { ?>
		<div class="aboutHeader">User Status</div> <?
		echo 'User not logged in';?>
		<br/><br/>
		<hr /><br/><?
	}?>

</div>


<div class="aboutText">
	<?
	//BOF Login user
	if(!$this->session->userdata('logged_in')) {?>

		<div class="aboutHeader">Login</div>

		<?echo '<form action="' . site_url('/login/logmein/') . '" method="post">';?>

		<label for="login_username">Username:</label>
		<input type="text" id="login_username" name="login_username" value="" /><br />

		<label for="login_password">Password:</label>
		<input type="password" id="login_password" name="login_password" value="" /><br />

		<input type="submit" id="login" name="login" value="Login" />

		</form><br />
		<hr />
	<?}
	//EOF Login user?>
</div>
<br/>


<?//BOF Create user?>
<?if($this->session->userdata('logged_in')) {?>
	<div class="aboutText">
		<div class="aboutHeader">Create A User</div>

		<form action="<?echo site_url('/login/create/')?>" method="post">

			<label for="create_username">Username:</label>
			<input type="text" id="create_username" name="create_username" value="" /><br />

			<label for="create_password">Password:</label>
			<input type="password" id="create_password" name="create_password" value="" /><br />

			<input type="checkbox" id="create_superuser" name="create_superuser" value="" style="margin-left:90px;margin-top: 5px;clear: left;float: left;"/>
			<label for="create_superuser" style="display: block;float: left;margin-left: 10px;width: 50px;margin-top: 4px;">Superuser</label><br /><br />

			<input type="submit" id="create" name="create" value="Create" style="margin-left: 105px;"/><br />

		</form>
		<br />
	</div>
	<hr />
<?}//EOF Create user?>

<?if($this->session->userdata('logged_in')) {?>
	<div class="aboutText">
		<br />
		<?
		//Grab user data from database
		$query = $this->db->select('id, username, superuser');
		$query = $this->db->get('users');
		$user_array = $query->result_array();

		if(count($user_array) > 0) {?>
			<div class="aboutHeader">User Table</div>
			<table>
				<tr>
					<th>
						ID
					</th>
					<th>
						Username
					</th>
					<th>
						Superuser
					</th>
					<?if($this->session->userdata('superuser')) {?>
						<th>
							Delete
						</th>
					<?}?>
				</tr>
				<?foreach($user_array as $ua) {?>
					<tr>
						<td>
							<?echo $ua['id'];?>
						</td>
						<td>
							<?echo $ua['username'];?>
						</td>
						<td>
							<? echo $ua['superuser']==1?'yes':'no';?>
						</td>
						<?if($this->session->userdata('superuser')) {?>
							<td>
								<?if($ua['superuser']!=1){?>
									<a href="<?echo site_url('/login/delete/' . $ua['id'])?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
								<?}?>
							</td>
						<?}?>
					</tr>
				<?}?>
			</table>
			<br />
		<?}
		?>
	</div>

	<hr />

<?}?>
</div>




