<div class="member-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{if isset(#member_title#)}{#member_title#|string_format:$_member.title:$_member.link}{else}#member_title(member-title,member-link)#{/if}</p></div>
  <div class="content-display"><h1>{if isset(#member_display#)}{#member_display#|string_format:$_member.display:$_member.link}{else}#member_display(member-name,member-link){/if}</h1></div>
  <div class="content-package">
{if $_member.packages}
		{if isset(#member_package#)}{#member_package#|string_format:$_member.display:$_member.link}{else}#member_package(member-name,member-link)#{/if}
		<ul>
{foreach from=$_member.packages item=package}
			<li>{if is_object($package)}$package.link{else}{$package}{/if}</li>
{/foreach}
		</ul>
{/if}
	</div>
  <div class="content-description">{$_member.description}</div>
{if $_member.types}
  <div class="content-types">
    <h2>{if isset(#member_types#)}{#member_types#|string_format:$_member.display:$_member.link}{else}#member_types(member.name,member.link)#{/if}</h2>
    <dl>
{foreach item=type from=$_member.types}
      {$type}
{/foreach}
    </dl>
  </div>
{/if}
</div></div></div>
