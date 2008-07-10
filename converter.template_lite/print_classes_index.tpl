{if $_classes}
<div class="classes-index">
  <h3 class="list-header">{#classes_index_list_header#|string_format:$_file.display}</h3>
  <ul class="classes-list">
{foreach from=$_classes item=_class}
    <li class="file">
      {$_class.link}
      {$_class.title}
    </li>
{/foreach}
  </ul>
</div>
{/if}
