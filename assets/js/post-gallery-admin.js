window.addEventListener('DOMContentLoaded', () => {
});

function copyInput(input) {
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
}