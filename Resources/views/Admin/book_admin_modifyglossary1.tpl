{*  book_admin_modifyglossary1.tpl,v 1.2 2005/08/30 19:29:32 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Glossary"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="modifyglossary2"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <p>{gt text="Glossary Term ID:"}<input type="text" name="gid" size="5" maxlength="5"/></p>
	<p><input name="submit" type="submit" value="{gt text="Edit Glossary"}" /></p>
</form>
<p>{gt text="Current glossary terms"}</p>
<table border=1>
<tr class="pn-blocktitle"><td>Term</td><td>id</td><td>Term</td><td>id</td><td>Term</td><td>id</td><td>Term</td><td>id</td></tr>
{section name=i loop=$term4}
<tr>
	<td>{$term1[i.index].term}</td><td>{$term1[i.index].gid}</td>
	<td>{$term2[i.index].term}</td><td>{$term2[i.index].gid}</td>
	<td>{$term3[i.index].term}</td><td>{$term3[i.index].gid}</td>
	<td>{$term4[i.index].term}</td><td>{$term4[i.index].gid}</td>
</tr>
{/section}
<tr>
	<td>{$last1.term}</td><td>{$last1.gid}</td>
	<td>{$last2.term}</td><td>{$last2.gid}</td>
	<td>{$last3.term}</td><td>{$last3.gid}</td>
</tr>
</table>