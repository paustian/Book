{*  book_block_first.tpl,v 1.3 2006/12/24 03:38:04 paustian Exp  *}
<div class="book_block">
{section name=i loop=$books}
        {$books[i].toc}
{/section}
</div>