{*  book_admin_import.tpl,v 1.1 2005/09/02 00:27:29 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<p>{gt text="Paste your modified chapter into this box and it will be imported."}</p>
<form class="form" action="{modurl modname="Book" type="admin" func="import"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<textarea name="chap_to_import" cols="120" rows="30">
</textarea>
<p><input name="submit" type="submit" value="{gt text="Import Chapter"}" /></p>
</form>