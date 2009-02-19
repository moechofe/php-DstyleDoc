<dt class="return-type">
<i>{if is_object($_return.type)}{$_return.type.link}{else}{$_return.type}{/if}</i>
{if $_return.from}
<span class="return-from">{if isset(#return_from#)}{#return_from#|string_format:$_return.from.display:$_return.from.link}{else}#return_from(from.name,from.link)#{/if}</span>
{/if}
</dt>
<dd class="return-description">
{$_return.description}
</dd>
