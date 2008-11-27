{if $_functions}
  <h3 class="list-header">{#functions_index_list_header#|string_format:$_file.display}</h3>
  <ul class="list">
{foreach from=$_functions item=_function}
    <li class="function">
      {$_function.link}
      {$_function.title}
    </li>
{/foreach}
  </ul>
{/if}
