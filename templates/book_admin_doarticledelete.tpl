{*  book_admin_doarticledelete.tpl,v 1.3 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Delete an article."}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="deletearticle"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    {$books}
	<p><input name="submit" type="submit" value="{gt text="Delete Article"}" /></p>
</form>

<script type="text/javascript">

//ddtreemenu.createTree(treeid, enablepersist, opt_persist_in_days (default is 1))
ddtreemenu.createTree("treemenu2", true, 5)

</script>