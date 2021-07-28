var usersDownloaded = false;

$('#newQuest > header').click(function() {
    if (!usersDownloaded) {
        usersDownloaded = true;
        const XHR = new XMLHttpRequest(), FD  = new FormData();
        FD.append('action', 'users');
        XHR.addEventListener('load', function(ev) {
            eval(ev.target.response);

            let html = '';

            for (let id in User.users) {
                html += `<option value="${User.users[id].nick}">`;
            }

            $('#newQuestUsersList').html(html);
        } );
        XHR.open('POST', '../php/server.php');
        XHR.send(FD);
    }

    let body = $('#newQuestBody');
    let h = body.height();
    if (body.css('display') == 'none') {
        body.css('height', 0);
        body.css('display', 'block');
        body.animate({height: h}, function() {
            body.css('height', 'auto');
        });
    } else {
        body.animate({height: 0}, function() {
            body.css('display', 'none');
            body.css('height', h);
        });
    }
});
$('#newQuest input[type=submit]').click(ev => {
    ev.preventDefault();

    let form = document.getElementById('newQuestBody');

    const XHR = new XMLHttpRequest(), FD  = new FormData(form);
    FD.append('action', 'newQuest');
    XHR.addEventListener('load', function(ev) {
        console.log(ev.target.response);
        if (ev.target.status == 400)
            return $('#newQuest .error').html(ev.target.response);
        window.location.reload();
    });

    XHR.open('POST', '../php/server.php');
    XHR.send(FD);
});

$('body').mousemove(function(ev) {
    if (activeTask == null) return;
});

var taskId = 0;

function addTask() {
    let tasks = document.getElementById('tasks');
    let div = document.createElement('div');
    taskId++;
    div.className = 'task';
    div.id = 'task_' + taskId;
    div.innerHTML = `
        <label><input placeholder="Nazwa"  type="text" name="title_task${taskId}"></label>
        <div class="close">&#x274C;</div>
        <label><textarea placeholder="Opis" name="description_task${taskId}" rows="3"></textarea></label>
        <div class="move"></div>
        <div style="clear: both;"></div>`

    tasks.appendChild(div);

    $(`#${div.id} > label > *`).keydown(function(ev) {
        let task = this.parentNode.parentNode;
        if ($(task).is(':last-child')) {
            let children = task.parentNode.children;
    
            if (children.length > 2) {
                let last = children[children.length - 2];
    
                if (last.children[0].children[0].value === '' && last.children[2].children[0].value === '')
                    return;
            }
    
            addTask();
        }
    });

    $(`#${div.id} .close`).click(function(ev) {
        if (this.parentNode.parentNode.childElementCount > 2)
            this.parentNode.parentNode.removeChild(this.parentNode);
    });

    $(`#${div.id} .move`).mousedown(function(ev) {
        activeTask = this;
        while (activeTask.className != 'task') {
            activeTask = activeTask.parentNode;
        }

        let tasks = document.getElementById('tasks');
        tasks.appendChild(activeTask);

        let j = $(activeTask);
    });
}

addTask();