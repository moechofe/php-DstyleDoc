<html>
{include file="_head.tpl"}
<body id="page-function">
<div id="page-content">
<div class="page-annotation"><p>{if isset(#page_function#)}{#page_function#|string_format:$function.display:$function.link:$function.file.display:$function.file.link}{else}#page_function(function-name,function-link,file-name,file-link)#{/if}</p></div>
<div class="page-content">{$function}</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">
{functions_index file=$function.file}
</div>
</body>
</html>
