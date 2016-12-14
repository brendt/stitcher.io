document.addEventListener('DOMContentLoaded', codeClick);

function codeClick() {
    var codeBlocks = document.querySelectorAll('code');

    for (var codeBlock of codeBlocks) {
        codeBlock.addEventListener('click', function() {
            var range = document.createRange();
            range.selectNode(this);
            window.getSelection().addRange(range);
        });
    }
}
