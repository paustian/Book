{*  book_user_shorttoc.tpl,v 1.2 2007/03/16 01:58:56 paustian Exp  *}
{pageaddvar name='javascript' value='jquery-ui'}
{pageaddvar name='javascript' value='modules/Book/javascript/book.js'}
{pageaddvar name='stylesheet' value='javascript/jquery-ui/themes/base/jquery-ui.css'}

{if $loggedIn}
    <button id="opener">{gt text="Open Book Tools"}</button>
    <div id="dialog" title="Book Tools"> 
        <form class="form" name="highlight" action="{modurl modname="Book" type="user" func="dohighlight"}" method="post" enctype="application/x-www-form-urlencoded">
            <input type="button" value="{gt text="Toggle Highlight"}" name="highlight" onmousedown="document.highlight.text.value = getSelectionHtml();
                    document.highlight.submit()" onmouseover="return escape('{gt text="Select the text you want to highlight."}')">
            <input type="hidden" name="text">
            <input type='hidden' name="aid" value="{$aid}">
        </form>
        <form class="form" name="collect_highlights" action="{modurl modname="Book" type="user" func="collecthighlights"}" method="post" enctype="application/x-www-form-urlencoded">
            <table>
                <tr style="text-align:center">
                    <td><input type="button" value="{gt text="Collect Highlights"}" name="collect_highlights" onmousedown="document.collect_highlights.submit()"
                               onmouseover="return escape('{gt text="Choose the chapters in the list box on the right then click this button. All the highlights that you have made for the choosen chapter(s) will be displayed."}')"></td>
                    <td>
                        <select name="cids[]" size="5" multiple>
                            {foreach item=chapter from=$chapters}
                                <option label="Chapter {$chapter.number}" value="{$chapter.number}">Chapter {$chapter.number}</option>
                            {/foreach}
                        </select>
                    </td>
                <tr></tr>
            </table>
            <input type="hidden" name="text">
        </form>
        <form class="form" name="dodef" action="{modurl modname="Book" type="user" func="dodef"}" method="post" enctype="application/x-www-form-urlencoded">
            <input type="button" value="{gt text="Create Definition"}" onmousedown="document.dodef.text.value = getSelectionHtml();
                    document.dodef.submit()"
                   onmouseover="return escape('{gt text="Select one to three words that you do not know the meaning of and click. The authors of the textbook will then define them for you."}')">
            <input type="hidden" name="text">
        </form>
        <hr />
    {/if}
    {if $dopaypal}
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" onmouseover="return escape('{gt text="_BOOKPAYPAL"}')">
            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="business" value="{$seller_email}">
            <input type="hidden" name="item_name" value="{$item_name}">
            <input type="hidden" name="item_number" value="111000">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="lc" value="US">
            <input type="hidden" name="bn" value="PP-SubscriptionsBF">
            <input type="hidden" name="a3" value="{$payment_amount}">
            <input type="hidden" name="p3" value="1">
            <input type="hidden" name="t3" value="Y">
            <input type="hidden" name="sra" value="1">
            <input type="hidden" name="custom" value="{$custom}">
            <!--The below value has to be changed from your web site. Point it at the correct url-->
            <input type="hidden" name="notify_url" value="http://www.microbiologytext.com/index.php?module=WebsiteFee&amp;unc=paypalsubscribe" />
        </form>
        <hr />
    {/if}
    <h4>{gt text="Table of Contents"}</h4>
    <form name="jump" method="post" action="/cgi-bin/redirect.pl">
        <p>
            <select name="quick_link" onChange="location.href = document.jump.quick_link.options[document.jump.quick_link.selectedIndex].value;">
                <option selected="selected">
                    {gt text="Select a Chapter..."}
                </option>
                <option>
                    ---------
                </option>
                {foreach item=chapter from=$chapters}
                    <option value="{$baseuri}/{modurl modname="Book" type="user" func="displayarticlesinchapter" cid=$chapter.cid}">{$chapter.number} - {$chapter.name}</option>
                {/foreach} 
            </select>
            <input type="submit" value="Go" />
        </p>
    </form>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(function() {
        $j("#dialog").dialog({
            position: ["right", "bottom"],
            autoOpen: true,
            resizeable: false,
            show: {
                effect: "blind",
                duration: 500
            },
            hide: {
                effect: "blind",
                duration: 500
            }
       });
        $j("#opener").click(function() {

            $j("#dialog").dialog("open");
        });
    });
    var lockright = function () {
         $j("#dialog").dialog('option', 'position', ['right','bottom']);
    };
    $j(window).scroll(lockright);
    $j(window).resize(lockright);
    
</script>