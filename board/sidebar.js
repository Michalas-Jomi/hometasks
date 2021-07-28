$('#quests ol > li').click(function() {
    const questBoard = Quest.getQuest(this.id).addToBoard();
    if (questBoard === undefined) return;
    const board = document.getElementById('board');
    const id = '#' + questBoard.get(0).id;

    questBoard.css('left', Utils.randRange(board.offsetWidth - questBoard.get(0).offsetWidth))

    $(id + ' > header').mousedown(function(ev) {
        activeQuest = $(this.parentNode);
        offX = ev.offsetX;
        offY = ev.offsetY;
    });
    $(id + ' .close').click(ev => {
        Quest.getQuest(ev.target.parentNode.id).removeFromBoard();
    });
    $(id + ' input[type=checkbox]').click(ev => {
        let questNode = ev.target.parentNode.parentNode.parentNode;
        let quest = Quest.getQuest(questNode.id);
        
        if (!quest.checkable) {
            ev.preventDefault();
            return;
        }

        let task = quest.findTaskFromList(ev.target.parentNode);
        
        task.done = ev.target.checked;

        quest.refreshProgress();

        const XHR = new XMLHttpRequest(), FD  = new FormData();
        FD.append('action', 'checkTask');
        FD.append('task', task.id);
        FD.append('status', ev.target.checked);
        XHR.open('POST', '../php/server.php');
        XHR.send(FD);
    });
});

$('#questsResize').mousedown(ev => {
    activeResizeSidebar = true;
});

$('body').mousemove(ev => {
    if (activeResizeSidebar) {
        ev.preventDefault();
        let newWidth = Math.min(600, Math.max(100, ev.pageX - container.get(0).offsetLeft));
        board.css('width', parseInt(container.css('width')) - newWidth);
        quests.css('width', newWidth);
    }
});