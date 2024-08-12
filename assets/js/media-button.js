
jQuery(document).ready(function ($) {
    const botao = $(`<button id="create_gallery" class="button media-button button-large">${pgStrings.buttonLabel}</button>`);
    $('.select-mode-toggle-button').before(botao);
    botao.click(() => {
        const selected = $('.attachments-wrapper li.selected');
        let ids = [];
        selected.each(function() {
            ids.push($(this).attr('data-id'));
        });
        prompt(pgStrings.promptText, `[post-gallery images="${ids.join(',')}"]`);
    });

    function mostraBotao() {
        const show = !$('.delete-selected-button').hasClass('hidden');
        if (show) {
            $('#create_gallery').removeClass('hidden');
        } else {
            $('#create_gallery').addClass('hidden');
        }
    }
    mostraBotao();
    
    const observer = new MutationObserver(mostraBotao);
    observer.observe(document.querySelector('.select-mode-toggle-button'), { attributes: true, childList: false, subtree: false });

    function enableButton() {
        const disabled = $('.attachments-wrapper li.selected').length == 0;
        $('#create_gallery').attr('disabled', disabled);
    }
    enableButton();
    document.body.addEventListener('click', event => {
        if (event.target.matches('.attachments-wrapper li,.attachments-wrapper li *')) {
            enableButton();
        }
    });
});