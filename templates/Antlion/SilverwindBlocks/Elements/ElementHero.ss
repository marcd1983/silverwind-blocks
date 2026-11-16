<section class="relative isolate flex overflow-hidden $HeightClass $VerticalAlignClass $ToneClass"<% if $BackgroundStyle %> style="$BackgroundStyle"<% end_if %>>
    <% if $BackgroundImage %>
        <% with $BackgroundImage.ScaleMaxWidth(2400) %>
            <picture>
                <% if $Up.MobileBackgroundImage %><source media="(max-width: 767px)" srcset="$Up.MobileBackgroundImage.ScaleMaxWidth(1200).URL"><% end_if %>
                <% with $Convert('webp') %><source srcset="$URL" type="image/webp"><% end_with %>
                <img src="$URL" width="$Width" height="$Height" alt="" class="absolute inset-0 -z-20 size-full object-cover" style="object-position: $Up.BackgroundPosition" fetchpriority="high">
            </picture>
        <% end_with %>
    <% end_if %>
    <% if $OverlayStyle %><div class="absolute inset-0 -z-10" style="$OverlayStyle" aria-hidden="true"></div><% end_if %>

    <div class="relative w-full py-16 lg:py-24 $InnerClass">
        <div class="max-w-2xl $BoxPositionClass $TextAlignClass">
            $RenderedTitle('text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl')
            <% if $Content %><div class="prose prose-lg mt-6 max-w-none">$Content</div><% end_if %>
            <% if $Links %>
                <div class="mt-8 flex flex-wrap gap-3 $ContentJustifyClass">
                    <% loop $Links %><% include Includes/SilverwindBlocks/Button Size=$Top.ButtonSizeClass %><% end_loop %>
                </div>
            <% end_if %>
        </div>
    </div>
</section>
