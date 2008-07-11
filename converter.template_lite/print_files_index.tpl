{if $_files}
<div class="files-index">
  <h3 class="list-header">{#files_index_list_header#}</h3>
  <ul class="files-list">
{foreach from=$_files item=_file}
    <li class="file">
      {$_file.link}
      {$_file.title}
    </li>
{/foreach}
  </ul>
</div>
{/if}{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
