{*  book_admin_dolistfigure.tpl,v 1.2 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="List of All Figures"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="listbookfigures"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    {section name=i loop=$books}
    <input type="radio" name="bid" value="{$books[i].bid}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].bid} - {$books[i].name}
	<br/>
	{/section}
        <p>{button src=folder_documents.gif set=icons/small alt="List Figures" title="List Figures" value="listfigs"}{gt text="List Figures"}</p>
        <p>{button src=folder_green.gif set=icons/small alt="List Figure Paths" title="List Figure Paths" value="listpaths"}{gt text="List Figure Paths"}</p>

        <p><input name="submit" type="submit" value="{gt text="List Figures"}" /></p>
</form>
