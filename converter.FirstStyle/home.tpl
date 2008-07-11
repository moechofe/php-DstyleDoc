<html>
{include file="_head.tpl"}
<body id="page-home">
<div id="page-content">
{if isset(#page_home#)}{#page_home#}{else}#page_home#{/if}
{files_index}
</div>
<div id="page-footer">
{include file="_footer.tpl"}
</div>
</div>
<div id="page-header">
{include file="_header.tpl"}
</div>
<div id="page-browser">

</div>
</body>
</html>{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
