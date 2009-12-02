{if $_members}
  <h3 class="list-header">{if isset(#members_index_list_header#)}{#members_index_list_header#|string_format:$_class.display:$_class.link}{else}#members_index_list_header(file-name,file-link)#{/if}</h3>
  <ul class="list">
{foreach from=$_members item=_member}
    <li class="member{if $this === $_member} displayed{/if}">
      {$_member.link}
      {$_member.title}
    </li>
{/foreach}
  </ul>
{/if}
