<html>
{include file="_head.tpl"}
<body id="page-function">
<div id="page-content">
<div class="page-annotation"><p>{#page_function#|string_format:$function.display:$function.link:$function.file.display:$function.file.link}</p></div>
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
