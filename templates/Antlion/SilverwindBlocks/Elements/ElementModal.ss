$RenderedTitle
<% if $ModalContent.ElementControllers %>
    <div class="flex flex-wrap gap-3 $ButtonJustifyClass">
        <% loop $ModalContent.ElementControllers %>
            <button type="button" class="$ModalTriggerClass $Up.ButtonSizeClass" data-modal-target="$ModalIdentifier" aria-haspopup="dialog">$ModalTriggerLabel.XML</button>
        <% end_loop %>
    </div>

    <% loop $ModalContent.ElementControllers %>
        <dialog id="$ModalIdentifier" class="modal modal-bottom sm:modal-middle" aria-label="$ModalTriggerLabel.ATT">
            <div class="modal-box w-full $Up.DialogSizeClass">
                <form method="dialog">
                    <button class="btn btn-circle btn-ghost btn-sm absolute top-3 right-3" aria-label="<%t Antlion\\SilverwindBlocks.Close 'Close' %>">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                    </button>
                </form>
                $Me
            </div>
            <form method="dialog" class="modal-backdrop"><button tabindex="-1"><%t Antlion\\SilverwindBlocks.Close 'Close' %></button></form>
        </dialog>
    <% end_loop %>
<% end_if %>
