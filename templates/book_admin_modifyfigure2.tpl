{*  book_admin_modifyfigure2.tpl,v 1.5 2007/01/13 20:48:36 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Figure"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="updatefigure"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
	<input type="hidden" name="fid" value="{$fid}" />
	<table>
<tr>
    <td>
    Figure ID:
    </td>
    <td>
    {$fid}
    </td>
</tr>
<tr>
	<td>{gt text="Figure Number"}</td>
	<td><input type="text" name="fig_number" size="3" maxlength="10" value="{$fig_number}"/></td>
</tr>
<tr>
	<td>{gt text="Chapter Number"}</td>
	<td><input type="text" name="chap_number" size="3" maxlength="10" value="{$chap_number}"/></td>
</tr>
<tr>
	<td><input type="checkbox" name="perm" {if $perm == 1}checked{/if}></td>
	<td>{gt text="Permission for figure has been granted."}</td>
<tr>
	<td>{gt text="Book ID"}</td>
	<td><select name="bid">
		{html_options options=$books selected=$bid}
		</select>
	</td>
</tr>
<tr>
	<td>{gt text="Figure Title"}</td>
	<td>
  		<input type="text" name="title" size="40" maxlength="256" value="{$title}"/>
	</td>
</tr>
<tr>
	<td>{gt text="Figure Legend"}</td>
	<td>
  <textarea name="content" id="contents" rows="10" cols="80">{$content}</textarea>
  </td>
</tr>
<tr>
	<td>{gt text="Image Path"}</td>
	<td><input type="text" name="img_link" size="40" maxlength="256" value="{$img_link}"></td>
</tr>
</table>
<input name="submit" type="submit" value="{gt text="Edit Figure"}" />
	
</form>
{notifydisplayhooks eventname='book.ui_hooks.articles.form_edit' id=$fid}