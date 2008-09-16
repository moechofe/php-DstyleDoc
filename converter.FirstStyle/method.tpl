<html>
{include file="_head.tpl"}
<body id="page-method">
<div id="page-content">
<div class="page-annotation"><p>{#page_method#|string_format:$method.display:$method.link:$method.class.display:$method.class.link:$method.file.display:$method.file.link}</p></div>
<div class="page-content">{$method}</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">
{methods_index class=$method.class}
</div>
</body>
</html>
