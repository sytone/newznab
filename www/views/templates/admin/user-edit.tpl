
<h1>{$page->title}</h1>

{if $error != ''}
	<div class="error">{$error}</div>
{/if}

<form action="{$SCRIPT_NAME}?action=submit" method="POST">

<table class="input">

<tr>
	<td>Name:</td>
	<td>
		<input type="hidden" name="id" value="{$user.ID}" />
		<input class="long" name="username" type="text" value="{$user.username}" />
	</td>
</tr>

<tr>
	<td>Email:</td>
	<td>
		<input class="long" name="email" type="text" value="{$user.email}" />
	</td>
</tr>

{if !$user.ID }
<tr>
	<td>Password:</td>
	<td>
		<input class="long" name="password" type="text" value="" />
	</td>
</tr>
{else}
<tr>
	<td>Grabs:</td>
	<td>
		<input class="short" name="grabs" type="text" value="{$user.grabs}" />
	</td>
</tr>
{/if}

<tr>
	<td>Invites:</td>
	<td>
		<input class="short" name="invites" type="text" value="{$user.invites}" />
	</td>
</tr>

<tr>
	<td><label for="role">Role</label>:</td>
	<td>
		{html_radios id="role" name='role' values=$role_ids output=$role_names selected=$user.role separator='<br />'}
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<input type="submit" value="Save" />
	</td>
</tr>

</table>

</form>