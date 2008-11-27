<div class="syntax-code">
{if isset($_syntax.function.class)}{$_syntax.function.class.name}{if $_syntax.function.static}::{else}-&gt;{/if}{/if}{$_syntax.function.name}( {section name=param loop=$_syntax.params}{if ! $templatelite.section.param.first}, {/if}${$_syntax.params[param].var}{/section} )
</div>
<div class="syntax-description">
{$_syntax.description}
</div>
