<%-- A linkfield Link as a button. Modal triggers (anything with $ModalTarget) open a <dialog> instead of navigating. Optional: Size (e.g. btn-lg), Block (full width) --%>
<% if $ModalTarget %>
    <button type="button" class="<% if $ButtonClass %>$ButtonClass<% else %>btn btn-primary<% end_if %><% if $Size %> $Size<% end_if %><% if $Block %> btn-block<% end_if %>" data-modal-target="$ModalTarget" aria-haspopup="dialog">$Title.XML</button>
<% else %>
    <a href="$URL" class="<% if $ButtonClass %>$ButtonClass<% else %>btn btn-primary<% end_if %><% if $Size %> $Size<% end_if %><% if $Block %> btn-block<% end_if %>"<% if $OpenInNew %> target="_blank" rel="noopener noreferrer"<% end_if %>>$Title.XML</a>
<% end_if %>
