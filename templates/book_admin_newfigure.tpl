{*  book_admin_newfigure.tpl,v 1.3 2007/01/13 20:45:36 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="New Figure"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="createfigure"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<table>
<tr>
	<td>{gt text="Figure Number"}</td>
	<td><input type="text" name="fig_number" size="3" maxlength="10"/></td>
</tr>
<tr>
	<td>{gt text="Chapter Number"}</td>
	<td><input type="text" name="chap_number" size="3" maxlength="10"/></td>
</tr>
<tr>
	<td><input type="checkbox" name="perm" checked /></td>
	<td>{gt text="Permission Granted"}</td>
</tr>
<tr>
	<td>{gt text="Book"}</td>
	<td><select name="bid">
		{html_options options=$books}
		</select>	
	</td>
</tr>
<tr>
	<td>{gt text="Figure Title"}</td>
	<td>
  		<input type="text" name="title" size="40" maxlength="256"/>
	</td>
</tr>
<tr>
	<td>{gt text="Figure Legend"}</td>
	<td>
  <textarea name="content" rows="10" cols="80" wrap="virutal"></textarea>
  </td>
</tr>
<tr>
	<td>{gt text="Path to Image/Medium"}</td>
	<td><input type="text" name="img_link" size="40" value="images/book_/chapter_/" maxlength="256"></td>
</tr>
</table>
<input name="submit" type="submit" value="{gt text="Create Figure"}" />
</form>
{notifydisplayhooks eventname='book.ui_hooks.articles.form_edit' id=$fid}