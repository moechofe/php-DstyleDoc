{if $_classes}
<div class="classes-index browse-content"><div class="frame"><div class="content">
  <h3 class="list-header">{#classes_index_list_header#|string_format:$_file.display}</h3>
  <ul class="classes-list">
{foreach from=$_classes item=_class}
    <li class="file">
      {$_class.link}
      {$_class.title}
    </li>
{/foreach}
  </ul>
</div></div></div>
{/if}{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
