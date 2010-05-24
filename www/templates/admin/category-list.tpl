
<h1>{$page->title}</h1>

<p>
	Make a category inactive to remove it from the menu. This does not prevent binaries being matched into an appropriate category.
</p>

<table style="margin-top:10px;" class="data Sortable highlight">

	<tr>
		<th>id</th>
		<th>title</th>
		<th>parent</th>
		<th>active</th>
	</tr>
	
	{foreach from=$categorylist item=category}
	<tr>
		<td>{$category.ID}</td>
		<td><a href="{$smarty.const.WWW_TOP}/category-edit.php?id={$category.ID}">{$category.title}</a></td>
		<td>{$category.parentID}</td>
		<td>{if $category.status == "1"}Yes{else}No{/if}</td>
	</tr>
	{/foreach}


</table>