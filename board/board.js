// Board
var offX, offY;
$('#board').mousemove(function(ev) {
    if (ev.buttons != 1) return;
    if (activeQuest == null) return;
    if (ev.offsetY < 0 || ev.offsetX < 0) return;

    ev.preventDefault();
    
    var x = 0, y = 0;

    var target = ev.target;

    while (target.id != 'board') {
        x += target.offsetLeft;
        y += target.offsetTop;
        target = target.parentNode;
    }

    x = ev.offsetX - offX + x;
    y = ev.offsetY - offY + y;

    x = Math.min(target.offsetWidth  - activeQuest.get(0).offsetWidth,  Math.max(0, x));
    y = Math.min(target.offsetHeight - activeQuest.get(0).offsetHeight, Math.max(0, y))

    activeQuest.css('left', x);
    activeQuest.css('top',  y);
});

