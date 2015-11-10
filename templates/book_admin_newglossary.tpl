{*  book_admin_newglossary.tpl,v 1.2 2005/08/30 19:29:32 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Add Glossary"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="createglossary"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<table>
<tr>
	<td>{gt text="Glossary Term:"}</td>
	<td>
  		<input type="text" name="term" size="40" maxlength="256"/>
	</td>
</tr>
<tr>
	<td>{gt text="Glossary Definition:"}</td>
	<td>
  <textarea name="definition" rows="10" cols="80" wrap="virutal"></textarea>
  </td>
</tr>
</table>
<input name="submit" type="submit" value="{gt text="Add Glossary"}" />
</form>
