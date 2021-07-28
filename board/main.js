const container = $('#container');
const quests = $('#quests');
const board = $('#board');

var activeQuest = null;
var activeTask = null;
var activeResizeSidebar = false;

$('body').mouseup(function(ev) {
    activeQuest = null;
    activeTask = null;
    activeResizeSidebar = false;
});

$('#detailsContainer .close').click(ev => $('#detailsContainer').css('display', 'none'));