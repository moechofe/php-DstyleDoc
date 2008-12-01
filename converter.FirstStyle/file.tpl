<html>
{include file="_head.tpl"}
<body id="page-file">
<div id="page-content">
<div class="page-annotation"><p>{if isset(#page_file#)}{#page_file#|string_format:$file.display:$file.link}{else}#page_file(file-name,file-link)#{/if}</p></div>
<div class="page-content">{$file}</div>
</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">
{files_index}
</div>
</body>
</html>
