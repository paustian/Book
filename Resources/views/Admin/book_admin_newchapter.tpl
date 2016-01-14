{*  book_admin_newchapter.tpl,v 1.2 2005/08/30 19:29:32 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Add Chapter"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="createchapter"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<table border>
<tr>
	<td>{gt text="Chapter Name"}</td>
	<td>
 	 <input type="text" name="name" value="" size="32" maxlength="256"/>
   </td>
</tr>
<tr>
	<td>{gt text="Chapter Number"}</td>
	<td>
  	<input type="text" name="number" value="" size="5" maxlength="5"/>
   </td>
</tr>
<tr>
	<td>{gt text="Chapter Owner"}</td>
	<td>
		<select name="bid">
		    {html_options options=$bookmenu}
		</select>
	</td>
</tr>
</table>
<input name="submit" type="submit" value="{gt text="Add Chapter"}" />
</form>
 