<dt class="return-type">
<i>{if is_object($_return.type)}{$_return.type.link}{else}{$_return.type}{/if}</i>
{if $_return.from && is_string($_return.from)}
<span class="return-from">{if isset(#return_from_free#)}{#return_from_free#|string_format:$_return.from}{else}#return_from_free(from.name_or_link)#{/if}</span>
{elseif $_return.from && $_return.from !== $this}
<span class="return-from">{if isset(#return_from#)}{#return_from#|string_format:$_return.from.display:$_return.from.link}{else}#return_from(from.name,from.link)#{/if}</span>
{/if}
</dt>
{if $_return.description}
<dd class="return-description">
{$_return.description}
</dd>
{/if}
