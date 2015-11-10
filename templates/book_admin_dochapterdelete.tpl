{*  book_admin_dochapterdelete.tpl,v 1.6 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Delete Chapter"}</h2>
<h2>{gt text="Warning deleting a chapter will remove all its articles. Make sure that this is what you want to do before hitting submit"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="deletechapter"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <table>
		{section name=i loop=$books}
		<tr><td>
		<input type="radio" name="bid" value="{$books[i].bid|}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].name|}
		</td>
		<td>
		<select name="chapter_{$books[i].bid|}">
			{html_options options=$chapters[i]}
		</select>
		</td></tr>
		{/section}
	</table>
	<p><input name="submit" type="submit" value="{gt text="Delete Chapter"}" /></p>
</form>