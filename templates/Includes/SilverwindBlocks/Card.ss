<%--
    Card in one of three styles (vertical, horizontal, overlay). Used by the
    Card block and Carousel cards. Expects the scope to provide $CardTitle,
    $CardHeading, $Content, $Links, $CardToneClass, $CardOverlayStyle and
    the CardDisplay values ($CardImage, $CardFrameClass, $CardImageClass,
    $CardTextAlignClass, $CardJustifyClass). Card blocks also provide
    $ToneClass and $BackgroundStyle. Pass Style=...
--%>
<% if $Style == 'overlay' %>
    <%-- The card takes the image's shape but can grow if the text needs more room, so the image sits in its own clipped layer --%>
    <article class="group relative isolate flex h-full flex-col justify-end<% if $CardFrameClass %> $CardFrameClass<% else %> min-h-80<% end_if %> $CardToneClass $CardTextAlignClass">
        <div class="absolute inset-0 -z-10 overflow-hidden rounded-box<% if not $BackgroundStyle %> bg-neutral<% end_if %>"<% if $BackgroundStyle %> style="$BackgroundStyle"<% end_if %> aria-hidden="true">
            <% if $CardImage %>
                <% with $CardImage %>
                    <img src="$URL" alt="" width="$Width" height="$Height" loading="lazy" class="size-full $Up.CardImageClass transition duration-500 group-hover:scale-105"<% if $Up.BackgroundPosition %> style="object-position: $Up.BackgroundPosition"<% end_if %>>
                <% end_with %>
                <% if $CardOverlayStyle %><div class="absolute inset-0" style="$CardOverlayStyle"></div><% end_if %>
            <% end_if %>
        </div>
        <div class="flex flex-col gap-3 p-6 sm:p-8">
            <% if $CardTitle %>
                <{$CardHeading} class="text-xl font-bold">
                    <% if $Links.First && not $Links.First.ModalTarget %>
                        <a href="$Links.First.URL" class="after:absolute after:inset-0 focus-visible:outline-2"<% if $Links.First.OpenInNew %> target="_blank" rel="noopener noreferrer"<% end_if %>>$CardTitle.XML</a>
                    <% else %>
                        $CardTitle.XML
                    <% end_if %>
                </{$CardHeading}>
            <% end_if %>
            <% if $Content %><div class="prose prose-sm max-w-none">$Content</div><% end_if %>
            <% if $Links.First && not $Links.First.ModalTarget %>
                <span class="mt-1 inline-flex items-center gap-2 font-semibold" aria-hidden="true">
                    $Links.First.Title.XML
                    <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </span>
            <% else_if $Links %>
                <div class="relative z-10 mt-2 flex flex-wrap gap-2 $CardJustifyClass">
                    <% loop $Links %><% include Includes/SilverwindBlocks/Button Size='btn-sm' %><% end_loop %>
                </div>
            <% end_if %>
        </div>
    </article>
<% else %>
    <article class="card h-full overflow-hidden border border-base-300 bg-base-100 shadow-sm<% if $Style == 'horizontal' %> sm:card-side<% end_if %> $ToneClass"<% if $BackgroundStyle %> style="$BackgroundStyle"<% end_if %>>
        <% if $CardImage %>
            <figure class="<% if $Style == 'horizontal' %>sm:w-2/5 sm:shrink-0<% end_if %>">
                <% if $Links.First && not $Links.First.ModalTarget %><a href="$Links.First.URL" class="block size-full" tabindex="-1" aria-hidden="true"<% if $Links.First.OpenInNew %> target="_blank" rel="noopener noreferrer"<% end_if %>><% end_if %>
                    <% with $CardImage %>
                        <img src="$URL" alt="" width="$Width" height="$Height" loading="lazy" class="w-full<% if $Up.CardFrameClass %> $Up.CardFrameClass h-auto<% end_if %> $Up.CardImageClass transition duration-500 hover:scale-105"<% if $Up.BackgroundPosition %> style="object-position: $Up.BackgroundPosition"<% end_if %>>
                    <% end_with %>
                <% if $Links.First && not $Links.First.ModalTarget %></a><% end_if %>
            </figure>
        <% end_if %>
        <div class="card-body $CardTextAlignClass">
            <% if $CardTitle %><{$CardHeading} class="card-title text-xl">$CardTitle.XML</{$CardHeading}><% end_if %>
            <% if $Content %><div class="prose max-w-none">$Content</div><% end_if %>
            <% if $Links %>
                <div class="card-actions mt-2 $CardJustifyClass">
                    <% loop $Links %><% include Includes/SilverwindBlocks/Button %><% end_loop %>
                </div>
            <% end_if %>
        </div>
    </article>
<% end_if %>
