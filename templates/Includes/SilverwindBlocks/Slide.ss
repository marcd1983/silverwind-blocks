<div class="swiper-slide relative isolate aspect-(--sw-ar-m) max-h-[85svh] min-h-80 overflow-hidden md:aspect-(--sw-ar) $ToneClass"<% if $BackgroundStyle %> style="$BackgroundStyle"<% end_if %> role="group" aria-roledescription="slide" aria-label="$Pos / $TotalItems">
    <% if $IsVideo %>
        <video class="absolute inset-0 -z-20 size-full object-cover" muted playsinline loop preload="metadata" data-sw-video<% if $DesktopImage %> poster="$DesktopImage.URL"<% end_if %><% if $VideoStart %> data-start="$VideoStart"<% end_if %><% if $VideoEnd %> data-end="$VideoEnd"<% end_if %> aria-hidden="true">
            <% if $VideoWebM %><source src="$VideoWebM.URL" type="video/webm"><% end_if %>
            <% if $VideoMP4 %><source src="$VideoMP4.URL" type="video/mp4"><% end_if %>
        </video>
    <% else_if $DesktopImage %>
        <picture>
            <% if $MobileImage %><source media="(max-width: 767px)" srcset="$MobileImage.URL" width="$MobileImage.Width" height="$MobileImage.Height"><% end_if %>
            <img src="$DesktopImage.URL" width="$DesktopImage.Width" height="$DesktopImage.Height" alt="$AltText.ATT" class="absolute inset-0 -z-20 size-full object-cover" style="object-position: $BackgroundPosition"<% if $IsFirst %> fetchpriority="high"<% else_if $LazyLoad %> loading="lazy"<% end_if %>>
        </picture>
        <% if $LazyLoad && not $IsFirst %><div class="swiper-lazy-preloader" aria-hidden="true"></div><% end_if %>
    <% end_if %>

    <% if $OverlayStyle %><div class="absolute inset-0 -z-10" style="$OverlayStyle" aria-hidden="true"></div><% end_if %>

    <% if $CoverLink %>
        <a href="$CoverLink.URL" class="absolute inset-0 z-10 focus-visible:outline-4 focus-visible:-outline-offset-4"<% if $CoverLink.OpenInNew %> target="_blank" rel="noopener noreferrer"<% end_if %>><span class="sr-only">$CoverLink.Title.XML</span></a>
    <% end_if %>

    <% if $Heading || $Content || $Links %>
        <div class="absolute inset-0 flex py-12 sm:py-16 $VerticalAlignClass">
            <div class="w-full $InnerClass">
                <div class="max-w-xl $BoxPositionClass $TextAlignClass<% if $ContentPanel %> rounded-box bg-black/45 p-6 backdrop-blur-sm sm:p-10<% end_if %>">
                    <% if $Heading %><h2 class="text-3xl font-bold tracking-tight text-balance sm:text-5xl">$Heading.XML</h2><% end_if %>
                    <% if $Content %><div class="prose prose-lg mt-4 max-w-none">$Content</div><% end_if %>
                    <% if $Links %>
                        <div class="relative z-20 mt-8 flex flex-wrap gap-3 $ContentJustifyClass">
                            <% loop $Links %><% include Includes/SilverwindBlocks/Button Size='btn-lg' %><% end_loop %>
                        </div>
                    <% end_if %>
                </div>
            </div>
        </div>
    <% end_if %>
</div>
