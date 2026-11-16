<% if $ModalTarget %>
    <button type="button" class="<% if $Plain %>link link-hover<% end_if %>" data-modal-target="$ModalTarget" aria-haspopup="dialog">$Title.XML</button>
<% else %>
    <a href="$URL" class="<% if $Plain %>link link-hover<% end_if %><% if $LinkingMode == 'current' %> menu-active<% end_if %>"<% if $OpenInNew %> target="_blank" rel="noopener noreferrer"<% end_if %>>$Title.XML</a>
<% end_if %>
