<div<% if $Lightbox %> x-data="swLightbox"<% end_if %>>
    $RenderedTitle
    <% if $Intro %><div class="prose mb-6 max-w-none">$Intro</div><% end_if %>
    <% if $VisibleImages %>
        <div class="$ColumnClasses gap-4">
            <% loop $VisibleImages %>
                <figure class="mb-4 break-inside-avoid">
                    <% with $Image.ScaleMaxWidth(700) %>
                        <% if $Up.Up.Lightbox %>
                            <a href="$Up.Image.ScaleMaxWidth(2400).URL" class="group block overflow-hidden rounded-box focus-visible:outline-2 focus-visible:outline-offset-2" data-lightbox data-alt="$Up.AltTextNice.ATT" data-caption="$Up.Caption.ATT" @click.prevent="open(\$el)">
                                <img src="$URL" width="$Width" height="$Height" alt="$Up.AltTextNice.ATT" loading="lazy" class="w-full transition duration-300 group-hover:scale-[1.03]">
                            </a>
                        <% else %>
                            <img src="$URL" width="$Width" height="$Height" alt="$Up.AltTextNice.ATT" loading="lazy" class="w-full rounded-box">
                        <% end_if %>
                    <% end_with %>
                    <% if $Caption %><figcaption class="mt-2 text-sm opacity-75">$Caption.XML</figcaption><% end_if %>
                </figure>
            <% end_loop %>
        </div>
    <% end_if %>

    <% if $Lightbox %>
        <dialog x-ref="dialog" class="modal" aria-label="<%t Antlion\\SilverwindBlocks.ImageViewer 'Image viewer' %>" @close="closed()" @keydown.left.prevent="prev()" @keydown.right.prevent="next()">
            <div class="modal-box relative flex max-h-[95svh] w-auto max-w-[95vw] flex-col items-center bg-neutral p-3 text-neutral-content sm:p-5" @touchstart.passive="touchStart(\$event)" @touchend.passive="touchEnd(\$event)">
                <img :src="current.src" :alt="current.alt" class="max-h-[78svh] w-auto rounded object-contain">
                <div class="mt-3 flex w-full items-center justify-between gap-4 text-sm">
                    <p x-text="current.caption" class="min-w-0 flex-1"></p>
                    <p x-text="counter()" class="opacity-70" aria-live="polite"></p>
                </div>
                <template x-if="items.length > 1">
                    <div>
                        <button type="button" @click="prev()" class="btn btn-circle absolute top-1/2 left-2 -translate-y-1/2" aria-label="<%t Antlion\\SilverwindBlocks.PreviousImage 'Previous image' %>">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                        </button>
                        <button type="button" @click="next()" class="btn btn-circle absolute top-1/2 right-2 -translate-y-1/2" aria-label="<%t Antlion\\SilverwindBlocks.NextImage 'Next image' %>">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </template>
                <form method="dialog">
                    <button class="btn btn-circle btn-sm absolute top-2 right-2" aria-label="<%t Antlion\\SilverwindBlocks.Close 'Close' %>">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                    </button>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop bg-black/80"><button tabindex="-1"><%t Antlion\\SilverwindBlocks.Close 'Close' %></button></form>
        </dialog>
    <% end_if %>
</div>
