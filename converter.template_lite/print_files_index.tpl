{if $_files}
  <h3 class="list-header">{if isset(#files_index_list_header#)}{#files_index_list_header#}{else}#files_index_list_header#{/if}</h3>
  <ul class="list">
{foreach from=$_files item=_file}
    <li class="file{if $this === $_file} displayed{/if}">
      {$_file.link}
      {$_file.title}
    </li>
{/foreach}
  </ul>
{/if}
