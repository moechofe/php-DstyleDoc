<div class="syntax-code">
{if $_syntax.returns}{section loop=$_syntax.returns name=return}{if ! $templatelite.section.return.first} {/if}<em>{if is_object($_syntax.returns[return].type)}{$_syntax.returns[return].type.link}{else}{$_syntax.returns[return].type}{/if}</em>{/section} = {/if}<u>{if $_syntax.function.isMethod}{$_syntax.function.class.name}{if $_syntax.function.static}::{else}-&gt;{/if}{/if}{$_syntax.function.name}</u>( {section name=param loop=$_syntax.params}{if ! $templatelite.section.param.first}, {/if}{if $_syntax.params[param].types}<em>{foreach from=$_syntax.params[param].types item=type}{if is_object($type.type)}{$type.type.link}{else}{$type.type}{/if} {/foreach}</em>{/if}<strong>${$_syntax.params[param].var}</strong>{/section} )
</div>
<div class="syntax-description">
{$_syntax.description}
</div>
