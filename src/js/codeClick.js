document.addEventListener('DOMContentLoaded', codeClick);

function codeClick() {
    const codeBlocks = document.querySelectorAll('code');
    let isDragging = false;
    let isMouseDown = false;

    for (let codeBlock of codeBlocks) {
        codeBlock.addEventListener("mousedown", function(){
            isDragging = false;
            isMouseDown = true;
        }, false);

        codeBlock.addEventListener("mousemove", function(){
            if (isMouseDown) {
                codeBlock.classList.add('dragging');
                isDragging = true;
            }
        }, false);

        codeBlock.addEventListener('mouseup', function() {
            if (!isDragging) {
                const range = document.createRange();
                range.selectNode(this);
                window.getSelection().addRange(range);
            }

            codeBlock.classList.remove('dragging');
            isDragging = false;
            isMouseDown = false;
        }, false);
    }
}
