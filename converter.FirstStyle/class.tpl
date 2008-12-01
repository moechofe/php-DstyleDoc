<html>
{include file="_head.tpl"}
<body id="page-class">
<div id="page-content">
<div class="page-annotation"><p>{if isset(#page_class#)}{#page_class#|string_format:$class.display:$class.link:$class.file.display:$class.file.link}{else}#page_class(class-name,class-link,file-name,file-link)#{/if}</p></div>
<div class="page-content">{$class}</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">
{classes_index file=$class.file}
</div>
</body>
</html>
