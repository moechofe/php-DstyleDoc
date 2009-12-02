<html>
{include file="_head.tpl"}
<body id="page-">
<div id="page-content">
<div class="page-annotation"><p>{if isset(#page_#)}{#page_#|string_format:$.display:$.link:$.class.display:$.class.link:$.file.display:$.file.link}{else}#page_(member-name,member-link,class-name,class-link,file-name,file-link)#{/if}</p></div>
<div class="page-content">{$member}</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">
{ascent_index class=$member.class}
{members_index class=$member.class}
</div>
</body>
</html>
