<html>
{include file="_head.tpl"}
<body id="page-members">
<div id="page-content">
<div class="page-annotation"><p>{if isset(#page_members#)}{#page_members#|string_format:$members.display:$members.link:$members.class.display:$members.class.link:$members.file.display:$members.file.link}{else}#page_members(member-name,member-link,class-name,class-link,file-name,file-link)#{/if}</p></div>
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
