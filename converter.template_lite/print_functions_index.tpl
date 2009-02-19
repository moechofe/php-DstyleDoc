{if $_functions}
  <h3 class="list-header">{if isset(#functions_index_list_header#)}{#functions_index_list_header#|string_format:$_file.display:$_file.link}{else}#functions_index_list_header(file-name,file-link)#{/if}</h3>
  <ul class="list">
{foreach from=$_functions item=_function}
    <li class="function{if $this == $_function} displayed{/if}">
      {$_function.link}
      {$_function.title}
    </li>
{/foreach}
  </ul>
{/if}
