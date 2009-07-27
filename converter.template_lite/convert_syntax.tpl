<div class="syntax-code">
{if $_syntax.returns}{section loop=$_syntax.returns name=return}{if ! $templatelite.section.return.first} {/if}<em>{$_syntax.returns[return].type}</em>{/section} = {/if}<u>{if $_syntax.function.isMethod}{$_syntax.function.class.name}{if $_syntax.function.static}::{else}-&gt;{/if}{/if}{$_syntax.function.name}</u>( {section name=param loop=$_syntax.params}{if ! $templatelite.section.param.first}, {/if}{if $_syntax.params[param].types}<em>{foreach from=$_syntax.params[param].types item=type}{$type.type} {/foreach}</em>{/if}<strong>${$_syntax.params[param].var}</strong>{/section} )
</div>
<div class="syntax-description">
{$_syntax.description}
</div>
