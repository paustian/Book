{*  book_user_displaychapter.tpl,v 1.2 2006/12/23 22:59:01 paustian Exp  *}
<h2>{gt text="Chapter "}{$chapter_number} - {$chapter_title}</h2>
{section loop=$article_number name=i}
<h3>{$chapter_number} - {$article_number[i]}  {$article_name[i]}</h3> 
{$article_content[i]}
{/section}