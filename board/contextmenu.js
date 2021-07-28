const contextmenu = $('#contextmenu');

contextmenu.get(0).setAttribute('tabindex', '0');

contextmenu.focus(ev => {
    contextmenu.css('display', 'block');
    let limit = 20;
    while (limit-- > 0 && (contextmenu.target.id === undefined || !contextmenu.target.id.startsWith('quest_')))
        contextmenu.target = contextmenu.target.parentNode;
    if (limit < 0) {
        contextmenu.focusout();
        console.error('contextmenu error');
        return;
    }
});
contextmenu.focusout(ev => {
    contextmenu.css('display', 'none');
    contextmenu.target = undefined;
})


$('#contextmenu > ol > li.refresh').click(ev => {
    let target = contextmenu.target;
    Utils.send('questRefresh', {'id': Quest.getQuestId(target.id)}, ev => {
        let quest = Quest.getQuest(target.id);
        quest.rewriteProgress(ev.target.response);
    });
});
$('#contextmenu > ol > li.edit').click(ev => {
    console.log('edit');
})
$('#contextmenu > ol > li.details').click(ev => {
    let quest = Quest.getQuest(contextmenu.target.id);
    Utils.send('questDetails', {'id' : quest.id},  ev => {
        let obj;
        eval('obj = ' + ev.target.response);
        console.log(obj);
        let quest = obj.quest;
        let tasks = obj.tasks;
        delete obj;

        $('#details .author').html('Autor: ' + quest.author);
        $('#details .owner').html('Dla: ' + (quest.owner ? quest.owner : 'wszystkich'));
        $('#details .created').html('Utworzono: ' + quest.created);
        $('#details .expiry').html('Termin: ' + (quest.expiry ? quest.expiry : 'bez terminowo'));

        let tasksHtml = '';

        tasks.forEach(task => {
            tasksHtml += `<li>`;

            tasksHtml += `<header><span class="title">${task.title}</span><span class="done">${task.done ? 'Wykonane' : 'Niewykonane'}</span></header>`;
            tasksHtml += '<ul>';
            task.states.forEach(state => {
                tasksHtml += `<li>[${state.date}] ${state.user} oznaczył jako ${state.state ? 'Wykonane' : 'Niewykonane'}</li>`;
            });
            tasksHtml += `</ul></li>`;
        });

        $('#details .tasks').html(tasksHtml);

        $('#detailsContainer').css('display', 'block');
    });
});
$('#contextmenu > ol > li.delete').click(ev => {
    let quest = Quest.getQuest(contextmenu.target.id);
    if (quest.author.id == user.id) {
        Utils.send('deleteQuest', {'id' : quest.id});
        quest.delete();
    }
})
$('#contextmenu > ol > li').click(ev => {
    contextmenu.focusout();
});

$('#quests li').contextmenu(ev => {
    contextmenu.target = ev.target;
    contextmenu.css('left', ev.pageX);
    contextmenu.css('top', ev.pageY);
    contextmenu.css('display', 'block');
    contextmenu.focus();
    ev.preventDefault();
});