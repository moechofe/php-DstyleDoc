<html>
{include file="_head.tpl"}
<body id="page-method">
<div id="page-content">
<p class="summary">
{if isset(#page_method#)}{#page_method#|string_format:$method.display:$method.link:$method.class.display:$method.class.link:$method.file.display:$method.file.link}{else}#page_method#{/if}
</p>
{$method}
</div>
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
</html>{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
