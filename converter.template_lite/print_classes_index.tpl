{if $_classes}
  <h3 class="list-header">{#classes_index_list_header#|string_format:$_file.display}</h3>
  <ul class="list">
{foreach from=$_classes item=_class}
    <li class="class">
      {$_class.link}
      {$_class.title}
    </li>
{/foreach}
  </ul>
{/if}
