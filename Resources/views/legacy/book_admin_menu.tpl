{*  book_admin_menu.tpl,v 1.9 2007/02/03 13:02:44 paustian Exp  *}
{insert name='getstatusmsg'}
{adminheader}
<h2>{gt text="Book"}</h2>
<div class="pn-menu">
  <table border="0" width="100%"">
    <tr>
      <td><a href="{modurl modname="Book" type="admin" func="newbook"}">{gt text="Add Book"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="newchapter"}">{gt text="New Chapter"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="newarticle"}">{gt text="New Article"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="newfigure"}">{gt text="New Figure"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="newglossary"}">{gt text="New Glossary"}</a></td>
    </tr>
    <tr>
      <td><a href="{modurl modname="Book" type="admin" func="modify"}">{gt text="Edit Book Name"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="modifychapter"}">{gt text="Edit Chapter Name"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="modifyarticle1"}">{gt text="Edit Article"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="modifyfigure1"}">{gt text="Edit Figure"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="modifyglossary1"}">{gt text="Edit Glossary Term"}</a></td>
    </tr>
    <tr>
      <td><a href="{modurl modname="Book" type="admin" func="dodelete"}">{gt text="Delete Book"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="chapterdisplay"}">{gt text="Delete Chapter"}</a></td>
      <td><a href="{modurl modname="Book" type="admin" func="dodeletearticle"}">{gt text="Delete Article"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="dodeletefigure"}">{gt text="Delete Figure"}</a></td>
    	  <td><a href="{modurl modname="Book" type="admin" func="dodeleteglossary"}">{gt text="Delete Glossary Term"}</a></td>
    </tr>    
  	<tr>
  		<td><a href="{modurl modname="Book" type="admin" func="do_export"}">{gt text="Export Chapter/Book"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="doimport"}">{gt text="Import Chapter/Book"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="dolistbookfigures"}">{gt text="List All Figures"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="choose_verify_url"}">{gt text="Check urls in Chapter"}</a></td>
                <td></td>
  	</tr>
  	<tr>
  		<td><a href="{modurl modname="Book" type="admin" func="checkstudentdefs"}">{gt text="Check for Requested Definitions"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="modifyconfig"}">{gt text="Modify Configuration"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="importglossaryitems"}">{gt text="Import Glossary"}</a></td>
  		<td><a href="{modurl modname="Book" type="admin" func="dosearchreplace1"}">{gt text="Do Global Search and Replace"}</a></td>
  		<td></td>
  	</tr>
  </table>
	
</div>