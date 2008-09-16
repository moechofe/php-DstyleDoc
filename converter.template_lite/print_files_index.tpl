{if $_files}
  <h3 class="list-header">{#files_index_list_header#}</h3>
  <ul class="list">
{foreach from=$_files item=_file}
    <li class="file">
      {$_file.link}
      {$_file.title}
    </li>
{/foreach}
  </ul>
{/if}
