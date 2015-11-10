
{include file="book_admin_menu.tpl"}
<h2>{gt text="List Figures"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="modifyfigure2"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<table>
{foreach from=$figData item=i}
<tr>
	<td><input type="radio" name="fid2" value="{$i.fid}"></td>
	<td>{$i.chap_number}-{$i.fig_number} {$i.title}</td>
	<td>{$i.content}</td>
</tr>
{/foreach}
</table>
<p><input name="submit" type="submit" value="{gt text="Edit Figure"}" /></p>

</form>