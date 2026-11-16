<section class="relative isolate<% if $HasBackground %> overflow-hidden<% end_if %> $PaddingClass $ToneClass<% if $HasBackgroundImage && $BackgroundFixed %> bg-cover bg-fixed<% end_if %>"<% if $BackgroundStyle || $BackgroundFixed %> style="$BackgroundStyle<% if $HasBackgroundImage && $BackgroundFixed %>;background-image:url('$BackgroundImage.ScaleMaxWidth(2400).URL');background-position:$BackgroundPosition<% end_if %>"<% end_if %>>
    <% if $HasBackgroundImage && not $BackgroundFixed %>
        <% with $BackgroundImage.ScaleMaxWidth(2400) %>
            <picture>
                <% if $Up.MobileBackgroundImage %><source media="(max-width: 767px)" srcset="$Up.MobileBackgroundImage.ScaleMaxWidth(1200).URL"><% end_if %>
                <% with $Convert('webp') %><source srcset="$URL" type="image/webp"><% end_with %>
                <img src="$URL" width="$Width" height="$Height" alt="" class="absolute inset-0 -z-20 size-full object-cover" style="object-position: $Up.BackgroundPosition" loading="lazy">
            </picture>
        <% end_with %>
    <% end_if %>
    <% if $HasBackgroundImage && $OverlayStyle %><div class="absolute inset-0 -z-10" style="$OverlayStyle" aria-hidden="true"></div><% end_if %>

    <div class="relative $InnerWidthClass">
        $RenderedTitle
        <% if $Intro %><div class="prose mb-8 max-w-none">$Intro</div><% end_if %>
        <% if $Elements.ElementControllers %>
            <div class="$LayoutClasses">
                <% loop $Elements.ElementControllers %>
                    <div class="min-w-0<% if $Up.IsColumns %> col-span-12 $GridSpanClass<% end_if %>">$Me</div>
                <% end_loop %>
            </div>
        <% end_if %>
    </div>
</section>
